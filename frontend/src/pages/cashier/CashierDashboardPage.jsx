import { useEffect, useState } from 'react'
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
  const [rejectReason, setRejectReason] = useState('')
  const [proofUrl, setProofUrl] = useState(null)
  const [isPosOpen, setIsPosOpen] = useState(false)
  const stats = useResource(() => cashierApi.dashboard(), [], { poll: true })
  const orders = useResource(() => cashierApi.orders({ 'filter[status]': filter }), [filter], { poll: true })
  const { busy, run } = useAction()

  const selected = (orders.data || []).find((o) => o.id === selectedId) || null
  const pick = (o) => { setSelectedId(o.id); setMethod(null); setReceived(''); setRejectReason('') }
  const effectiveMethod = method || selected?.payment?.method?.code
  const after = () => { setSelectedId(null); orders.reload(); stats.refresh() }
  useEffect(() => {
    let active = true; let objectUrl
    if (selected?.payment?.proof?.preview_available && selected.payment.method.code === 'qris') {
      cashierApi.paymentProof(selected.payment.id).then((blob) => { if (active) { objectUrl = URL.createObjectURL(blob); setProofUrl(objectUrl) } }).catch(() => { if (active) setProofUrl(null) })
    } else setProofUrl(null)
    return () => { active = false; if (objectUrl) URL.revokeObjectURL(objectUrl) }
  }, [selected?.payment?.id, selected?.payment?.proof?.preview_available])

  const verify = async () => {
    const payload = {}
    if (method && method !== selected.payment.method.code) payload.method = method
    if (effectiveMethod === 'tunai' && received) payload.amount_received = Number(received)
    const out = await run(() => cashierApi.verify(selected.payment.id, payload), 'Pembayaran terverifikasi')
    if (out) after()
  }
  const reject = async () => { const out = await run(() => cashierApi.reject(selected.payment.id, { reason: rejectReason.trim() }), 'Pembayaran ditolak'); if (out) after() }

  const s = stats.data
  return (
    <>
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <PageHeader title="Dashboard Kasir" />
        <Button
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
                    <li key={o.id} className={cn('flex items-center justify-between gap-3 py-3', selectedId === o.id && 'rounded-lg bg-white/10 px-2')}>
                      <div>
                        <p className="text-lg font-medium">{o.display_number}</p>
                        <p className="text-sm text-white/70">
                          {o.table?.number ? `Meja ${o.table.number}` : `Takeaway (${o.customer_name || 'Pelanggan'})`} · {o.items.reduce((a, i) => a + i.qty, 0)} item
                        </p>
                      </div>
                      <div className="text-right">
                        <p>{rupiah(o.total)}</p>
                        <p className={cn('text-sm font-medium', o.payment?.status?.code === 'pending' ? 'text-[#f5b04c]' : 'text-white/70')}>
                          {o.payment?.status?.label || ORDER_STATUS[o.status.code]?.label}
                        </p>
                      </div>
                      {o.payment?.status?.code === 'pending' ? (
                        <Button variant="sand" size="sm" onClick={() => pick(o)}>Verif</Button>
                      ) : (
                        <span className="w-14 text-xs text-white/60">{o.payment?.status?.code === 'rejected' ? 'Tunggu bukti baru' : ''}</span>
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
              <p className="text-sm font-medium text-[#f5b04c]">Status pembayaran: {selected.payment?.status?.label || 'Tidak diketahui'}</p>
              <ul className="space-y-1.5">{selected.items.map((i) => <li key={i.id} className="flex justify-between"><span>{i.name} <span className="text-white/70">×{i.qty}</span></span><span>{rupiah(i.subtotal)}</span></li>)}</ul>
              {selected.payment?.proof?.preview_available && <div><p className="mb-2">Bukti QRIS pelanggan · status {selected.payment.proof.status}</p>{proofUrl ? <img src={proofUrl} alt="Bukti pembayaran QRIS" className="max-h-72 max-w-full rounded-lg bg-white object-contain" /> : <p>Memuat bukti…</p>}{selected.payment.proof.reject_reason && <p className="mt-2 text-amber-200">Penolakan sebelumnya: {selected.payment.proof.reject_reason}</p>}</div>}
              {selected.order_source === 'qr_customer' && selected.payment?.method?.code === 'qris' && selected.payment.proof?.status !== 'pending' && <p className="text-amber-200">Menunggu bukti QRIS pelanggan sebelum pembayaran dapat diverifikasi.</p>}
              {selected.payment_history?.length > 0 && <ul aria-label="Riwayat bukti QRIS" className="space-y-1 text-sm">{selected.payment_history.map((proof, index) => <li key={`${proof.uploaded_at}-${index}`}>Bukti {index + 1}: {proof.status}{proof.reject_reason ? ` · alasan: ${proof.reject_reason}` : ''}</li>)}</ul>}
              <div className="flex justify-between border-t border-white/20 pt-3 text-lg font-medium"><span>Total</span><span>{rupiah(selected.total)}</span></div>
              <div>
                <p className="mb-2 text-sm text-white/80">Metode Pembayaran</p>
                <FilterTabs options={[{ value: 'qris', label: 'QRIS' }, { value: 'tunai', label: 'CASH' }]} value={effectiveMethod} onChange={setMethod} />
              </div>
              {effectiveMethod === 'tunai' && (
                <label className="block text-sm"><span className="mb-1 block text-white/80">Uang diterima (opsional)</span>
                  <Input inputMode="numeric" value={received} onChange={(e) => setReceived(e.target.value.replace(/\D/g, ''))} placeholder={String(selected.total)} className="text-brand-ink" />
                  {received && Number(received) >= selected.total && <span className="mt-1 block">Kembalian {rupiah(Number(received) - selected.total)}</span>}
                </label>
              )}
              <label className="block text-sm"><span className="mb-1 block text-white/80">Alasan penolakan (wajib jika menolak)</span><textarea value={rejectReason} onChange={(e) => setRejectReason(e.target.value)} maxLength={255} className="w-full rounded-lg p-2 text-brand-ink" /></label>
              <div className="flex justify-end gap-3 pt-2">
                {selected.payment?.method?.code === 'qris' && <Button variant="sand" loading={busy} disabled={!rejectReason.trim()} onClick={reject}>Tolak</Button>}
                <Button variant="sand" loading={busy} disabled={selected.order_source === 'qr_customer' && selected.payment?.method?.code === 'qris' && selected.payment.proof?.status !== 'pending'} onClick={verify}>Verifikasi</Button>
              </div>
            </div>
          )}
        </Card>
      </div>

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
