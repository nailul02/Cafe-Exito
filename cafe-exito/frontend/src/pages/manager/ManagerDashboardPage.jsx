import { Link } from 'react-router-dom'
import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { Card, StatCard } from '../../components/ui/Card'
import { Badge, StatusPill } from '../../components/ui/Badge'
import Progress from '../../components/ui/Progress'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'
import { ORDER_STATUS, STOCK_LEVEL, TICKET_STATUS } from '../../lib/status'
import { qty, rupiah, timeHM } from '../../lib/format'

/** MG-01: dashboard Manager. Pantauan pesanan hanya-baca (K-B11 PENDING: apakah Manager boleh menjalankan tiket). */
export default function ManagerDashboardPage() {
  const res = useResource(() => reportsApi.managerDashboard(), [], { poll: true })
  return (
    <>
      <PageHeader title="Dashboard" subtitle="Pantau pesanan, stok, dan ketersediaan menu secara langsung." />
      <DevNote>Jadwal shift & kehadiran staf (ada di Figma) belum dibangun: keputusan K-B1. Tombol aksi pesanan oleh Manager menunggu K-B11.</DevNote>
      <AsyncView res={res} empty={() => false}>
        {(d) => (
          <div className="space-y-5">
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <StatCard label="Penjualan hari ini" value={rupiah(d.revenue_today)} />
              <StatCard label="Pesanan aktif" value={d.active_orders.total} hint={`${d.active_orders.queued} antre · ${d.active_orders.in_progress} diproses`} />
              <StatCard label="Bahan kritis" value={d.low_stock.filter((i) => ['kritis', 'habis'].includes(i.level.code)).length} tone="bad" hint="Perlu restok" />
              <StatCard label="Penerimaan tertunda" value={d.pending_receipts} hint="Pembelian belum diterima" />
            </div>
            <div className="grid gap-5 xl:grid-cols-3">
              <Card className="p-5 xl:col-span-2">
                <h2 className="mb-3 font-ui font-semibold text-brand-ink">Pantau pesanan masuk</h2>
                {d.orders.length === 0 ? <p className="py-8 text-center text-sm text-brand/70">Belum ada pesanan hari ini.</p> : (
                  <ul className="grid gap-3 md:grid-cols-2">{d.orders.map((o) => (
                    <li key={o.id} className="rounded-2xl bg-cream/60 p-4">
                      <div className="flex items-center justify-between"><p className="font-semibold">{o.display_number} <span className="font-normal text-brand/70">· Meja {o.table.number}</span></p><StatusPill map={ORDER_STATUS} code={o.status.code} /></div>
                      <p className="mt-2 text-sm">{o.items.map((i) => `${i.qty}× ${i.name}`).join(', ')}</p>
                      <div className="mt-2 flex flex-wrap items-center gap-1.5">{o.tickets.map((t) => <Badge key={t.id} tone={TICKET_STATUS[t.status.code].tone}>{t.station === 'barista' ? 'Bar' : 'Dapur'}: {TICKET_STATUS[t.status.code].label}</Badge>)}<span className="ml-auto text-sm font-medium">{rupiah(o.total)} · {timeHM(o.ordered_at)}</span></div>
                    </li>
                  ))}</ul>
                )}
              </Card>
              <div className="space-y-5">
                <Card className="p-5">
                  <div className="mb-3 flex items-center justify-between"><h2 className="font-ui font-semibold text-brand-ink">Stok kritis</h2><Link to="/manager/inventory" className="text-xs text-accent-deep underline">Kelola semua</Link></div>
                  {d.low_stock.length === 0 ? <p className="text-sm text-brand/70">Semua bahan aman.</p> : <ul className="space-y-3">{d.low_stock.slice(0, 5).map((i) => <li key={i.id}><div className="flex justify-between text-sm"><span className="font-medium">{i.name}</span><span className="text-brand/70">{qty(i.stock)}/{qty(i.min_stock)} {i.unit}</span></div><Progress className="mt-1" percent={i.stock_percent} tone={i.level.code === 'menipis' ? 'warn' : 'bad'} /><p className="mt-0.5 text-xs" style={{ color: STOCK_LEVEL[i.level.code].tone === 'bad' ? 'var(--color-bad)' : 'var(--color-warn)' }}>{STOCK_LEVEL[i.level.code].label}</p></li>)}</ul>}
                </Card>
                <Card className="p-5"><h2 className="mb-3 font-ui font-semibold text-brand-ink">Menu tidak tersedia</h2>
                  {d.unavailable_menus.length === 0 ? <p className="text-sm text-brand/70">Semua menu tersedia.</p> : <ul className="space-y-2 text-sm">{d.unavailable_menus.map((m) => <li key={m.id} className="flex justify-between"><span>{m.name}</span><Badge tone="bad">{m.reason}</Badge></li>)}</ul>}
                </Card>
              </div>
            </div>
          </div>
        )}
      </AsyncView>
    </>
  )
}
