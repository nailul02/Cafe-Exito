import { useState } from 'react'
import { cashierApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card, StatCard } from '../../components/ui/Card'
import FilterTabs from '../../components/ui/FilterTabs'
import Button from '../../components/ui/Button'
import { Input } from '../../components/ui/Form'
import { AsyncView, EmptyState } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import CashierPosModal from './CashierPosModal'
import { ORDER_STATUS } from '../../lib/status'
import { rupiah } from '../../lib/format'
import { cn } from '../../lib/cn'

const FILTERS = [{ value: 'semua', label: 'Semua' }, { value: 'menunggu_pembayaran', label: 'Menunggu Bayar' }, { value: 'dibayar', label: 'Sudah Bayar' }]

/** KS-01: dashboard kasir = kartu ringkasan + daftar pesanan + panel verifikasi pembayaran + pembuat pesanan POS. */
export default function CashierDashboardPage() {
  const [filter, setFilter] = useState('menunggu_pembayaran')
  const [selectedId, setSelectedId] = useState(null)
  const [method, setMethod] = useState(null)
  const [received, setReceived] = useState('')
  const [amountApplied, setAmountApplied] = useState('')
  const [isPosOpen, setIsPosOpen] = useState(false)
  const stats = useResource(() => cashierApi.dashboard(), [], { poll: true, enabled: !isPosOpen })
  const orders = useResource(() => cashierApi.orders({ 'filter[status]': filter }), [filter], { poll: true, enabled: !isPosOpen })
  const sessions = useResource(() => cashierApi.sessions(), [], { poll: true, enabled: !isPosOpen })
  const { busy, run } = useAction()

  const selected = (orders.data || []).find((o) => o.id === selectedId) || null
  const selectedPaid = selected?.payments?.filter((payment) => ['verified', 'refunded'].includes(payment.status?.code)).reduce((sum, payment) => sum + Number(payment.amount || 0), 0) || 0
  const selectedBalance = selected ? Math.max(0, Number(selected.total) - selectedPaid) : 0
  const pick = (o) => { setSelectedId(o.id); setMethod(null); setReceived(''); setAmountApplied('') }
  const effectiveMethod = method || selected?.payment?.method?.code
  const after = () => { setSelectedId(null); orders.reload(); stats.refresh() }

  const verify = async () => {
    const payload = {}
    if (method && method !== selected.payment.method.code) payload.method = method
    if (amountApplied) payload.amount_applied = Number(amountApplied)
    if (effectiveMethod === 'tunai' && received) payload.amount_received = Number(received)
    const out = await run(() => cashierApi.verify(selected.payment.id, payload), 'Pembayaran terverifikasi')
    if (out) after()
  }
  const reject = async () => { const out = await run(() => cashierApi.reject(selected.payment.id, {}), 'Pembayaran ditolak'); if (out) after() }
  const closeSession = async (id) => {
    const out = await run(() => cashierApi.closeSession(id), 'Sesi meja ditutup')
    if (out) sessions.reload()
  }

  const s = stats.data
  return (
    <>
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <PageHeader title="Dashboard Kasir" />
        <Button
          data-testid="new-pos-order-btn"
          variant="sand"
          size="md"
          className="font-bold shadow-lg"
          onClick={() => setIsPosOpen(true)}
        >
          ➕ Pesanan Baru (POS)
        </Button>
      </div>

      <div className="grid grid-cols-2 gap-3 xl:grid-cols-4 mt-2">
        <StatCard surface="brown" label="Pesanan Baru" value={s?.new_orders ?? '–'} />
        <StatCard surface="brown" label="Menunggu Pembayaran" value={s?.awaiting_payment ?? '–'} />
        <StatCard surface="brown" label="Pesanan Hari Ini" value={s?.orders_today ?? '–'} />
        <StatCard surface="brown" label="Total Pendapatan" value={s ? rupiah(s.revenue_today) : '–'} />
      </div>

      <div className="mt-6 grid gap-4 xl:grid-cols-2">
        <Card surface="brown" className="p-5">
          <h2 className="text-xl font-medium">Daftar Pesanan</h2>
          <FilterTabs className="mt-3" options={FILTERS} value={filter} onChange={(v) => { setFilter(v); setSelectedId(null) }} />
          <div className="mt-4">
            <AsyncView res={orders} emptyTitle="Belum ada pesanan" emptyHint="Pesanan pelanggan akan muncul di sini secara otomatis.">
              {(rows) => (
                <ul className="divide-y divide-white/15">
                  {rows.map((o) => (
                    <li key={o.id} data-testid={`cashier-order-${o.id}`} className={cn('flex items-center justify-between gap-3 py-3', selectedId === o.id && 'rounded-lg bg-white/10 px-2')}>
                      <div>
                        <p className="text-lg font-medium">{o.display_number}</p>
                        <p className="text-sm text-white/70">
                          {o.table?.number ? `Meja ${o.table.number}` : `Takeaway (${o.customer_name || 'Pelanggan'})`} · {o.items.reduce((a, i) => a + i.qty, 0)} item
                        </p>
                      </div>
                      <div className="text-right">
                        <p>{rupiah(o.total)}</p>
                        <p className={cn('text-sm font-medium', o.payment?.status?.code === 'pending' ? 'text-[#f5b04c]' : 'text-white/70')}>
                          {ORDER_STATUS[o.status.code]?.label}
                        </p>
                      </div>
                      {o.payment?.status?.code === 'pending' ? (
                        <Button data-testid={`verif-order-btn-${o.id}`} variant="sand" size="sm" onClick={() => pick(o)}>Verif</Button>
                      ) : (
                        <span className="w-14" />
                      )}
                    </li>
                  ))}
                </ul>
              )}
            </AsyncView>
          </div>
        </Card>

        <Card surface="brown" className="p-5">
          <h2 className="text-xl font-medium">Verifikasi Pembayaran</h2>
          {!selected ? <div className="text-white/80"><EmptyState title="Pilih pesanan" hint="Tekan Verif pada pesanan yang menunggu pembayaran." /></div> : (
            <div className="mt-3 space-y-4">
              <p className="text-lg font-medium">
                {selected.display_number} · {selected.table?.number ? `Meja ${selected.table.number}` : `Takeaway (${selected.customer_name || 'Pelanggan'})`}
              </p>
              <ul className="space-y-1.5">{selected.items.map((i) => <li key={i.id} className="flex justify-between"><span>{i.name} <span className="text-white/70">×{i.qty}</span></span><span>{rupiah(i.subtotal)}</span></li>)}</ul>
              <div className="flex justify-between border-t border-white/20 pt-3 text-lg font-medium"><span>Total</span><span>{rupiah(selected.total)}</span></div>
              <div className="flex justify-between text-sm text-white/80"><span>Terverifikasi · Sisa</span><span>{rupiah(selectedPaid)} · {rupiah(selectedBalance)}</span></div>
              <label className="block text-sm"><span className="mb-1 block text-white/80">Jumlah yang diterima untuk cicilan</span>
                <Input inputMode="numeric" value={amountApplied} onChange={(e) => setAmountApplied(e.target.value.replace(/\D/g, ''))} placeholder={String(selected.payment?.amount || 0)} className="text-brand-ink" />
                <span className="mt-1 block text-xs text-white/60">Kosongkan untuk memverifikasi seluruh tagihan pembayaran ini.</span>
              </label>
              <div>
                <p className="mb-2 text-sm text-white/80">Metode Pembayaran</p>
                <FilterTabs options={[{ value: 'qris', label: 'QRIS' }, { value: 'tunai', label: 'CASH' }]} value={effectiveMethod} onChange={setMethod} />
              </div>
              {effectiveMethod === 'tunai' && (
                <label className="block text-sm"><span className="mb-1 block text-white/80">Uang diterima (opsional)</span>
                  <Input inputMode="numeric" value={received} onChange={(e) => setReceived(e.target.value.replace(/\D/g, ''))} placeholder={String(selected.total)} className="text-brand-ink" />
                  {received && Number(received) >= Number(amountApplied || selected.payment?.amount || 0) && <span className="mt-1 block">Kembalian {rupiah(Number(received) - Number(amountApplied || selected.payment?.amount || 0))}</span>}
                </label>
              )}
              <div className="flex justify-end gap-3 pt-2">
                <Button data-testid="cashier-reject-payment" variant="sand" loading={busy} onClick={reject}>Tolak</Button>
                <Button data-testid="cashier-verify-payment" variant="sand" loading={busy} onClick={verify}>Verifikasi</Button>
              </div>
            </div>
          )}
        </Card>
      </div>

      <Card surface="brown" className="mt-6 p-5">
        <h2 className="text-xl font-medium">Sesi Meja Aktif</h2>
        <p className="mt-1 text-sm text-white/70">Saldo dihitung dari seluruh pesanan dan pembayaran terverifikasi dalam sesi.</p>
        <div className="mt-4"><AsyncView res={sessions} emptyTitle="Tidak ada sesi meja aktif">
          {(rows) => <ul className="divide-y divide-white/15">
            {rows.map((session) => {
              const unfinished = session.orders.some((order) => !['selesai', 'dibatalkan'].includes(order.status))
              const pending = session.orders.some((order) => order.payments.some((payment) => payment.status === 'pending'))
              const canClose = !unfinished && !pending && session.balance === 0
              return <li key={session.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                <div><p className="font-semibold">Meja {session.table.number} · {session.customer_name}</p>
                  <p className="text-sm text-white/70">{session.orders.length} pesanan · Total {rupiah(session.total)} · Terbayar {rupiah(session.paid)} · Sisa {rupiah(session.balance)}</p>
                  {!canClose && <p className="mt-1 text-xs text-[#f5b04c]">Tutup setelah semua pesanan selesai, pembayaran diverifikasi, dan saldo lunas.</p>}
                </div>
                <Button size="sm" variant="sand" disabled={!canClose || busy} loading={busy} onClick={() => closeSession(session.id)}>Tutup Sesi</Button>
              </li>
            })}
          </ul>}
        </AsyncView></div>
      </Card>

      {/* Modal POS Kasir */}
      <CashierPosModal
        isOpen={isPosOpen}
        onClose={() => setIsPosOpen(false)}
        onSuccess={() => {
          orders.reload()
          stats.refresh()
        }}
      />
    </>
  )
}
