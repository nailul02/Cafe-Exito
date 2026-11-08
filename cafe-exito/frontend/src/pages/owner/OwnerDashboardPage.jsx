import { Link } from 'react-router-dom'
import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { Card, StatCard } from '../../components/ui/Card'
import Progress from '../../components/ui/Progress'
import { Badge } from '../../components/ui/Badge'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import BarChart from '../../components/charts/BarChart'
import DevNote from '../../components/ui/DevNote'
import { rupiah, qty } from '../../lib/format'
import { STOCK_LEVEL } from '../../lib/status'

/** OW-01: ringkasan Owner. Tanpa laba bersih/HPP/outlet/kehadiran (di luar MVP: K-B1, K-B3, K-B7). Owner hanya membaca (K-B11). */
export default function OwnerDashboardPage() {
  const res = useResource(() => reportsApi.ownerDashboard(), [], { poll: true, interval: 15000 })
  return (
    <>
      <PageHeader title="Dashboard" subtitle="Ringkasan operasional Cafe Exito hari ini." />
      <DevNote>Layar Figma Owner memuat laba bersih, kehadiran karyawan, dan pilihan outlet. Itu belum masuk MVP (menunggu keputusan K-B1/K-B3/K-B7).</DevNote>
      <AsyncView res={res} empty={() => false}>
        {(d) => (
          <div className="space-y-5">
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <StatCard label="Penjualan hari ini" value={rupiah(d.today.revenue)} hint={d.today.vs_yesterday_percent == null ? 'Belum ada pembanding kemarin' : `${d.today.vs_yesterday_percent >= 0 ? '▲' : '▼'} ${Math.abs(d.today.vs_yesterday_percent)}% dibanding kemarin`} tone={d.today.vs_yesterday_percent < 0 ? 'bad' : 'ok'} />
              <StatCard label="Transaksi" value={d.today.orders} hint="Pesanan terbayar" />
              <StatCard label="Rata-rata transaksi" value={rupiah(d.today.average_order)} />
              <StatCard label="Stok kritis" value={`${d.critical_count} bahan`} tone={d.critical_count ? 'bad' : 'ok'} hint={`${d.pending_purchases} pembelian belum diterima`} />
            </div>
            <div className="grid gap-5 xl:grid-cols-3">
              <Card className="p-5 xl:col-span-2"><h2 className="font-ui font-semibold text-brand-ink">Tren penjualan per jam</h2><div className="mt-3"><BarChart data={d.series} /></div></Card>
              <Card className="p-5">
                <div className="mb-3 flex items-center justify-between"><h2 className="font-ui font-semibold text-brand-ink">Peringatan stok</h2><Link to="/owner/reports" className="text-xs text-accent-deep underline">Laporan</Link></div>
                {d.low_stock.length === 0 ? <p className="text-sm text-brand/70">Semua bahan aman.</p> : (
                  <ul className="space-y-3">{d.low_stock.slice(0, 5).map((i) => (
                    <li key={i.id}><div className="flex items-center justify-between gap-2 text-sm"><span className="font-medium">{i.name}</span><Badge tone={STOCK_LEVEL[i.level.code].tone}>{STOCK_LEVEL[i.level.code].label}</Badge></div><p className="text-xs text-brand/60">Sisa {qty(i.stock)} {i.unit} · minimum {qty(i.min_stock)}</p><Progress className="mt-1" percent={i.stock_percent} tone={i.level.code === 'menipis' ? 'warn' : 'bad'} /></li>
                  ))}</ul>
                )}
              </Card>
            </div>
            <Card className="p-5"><h2 className="mb-3 font-ui font-semibold text-brand-ink">Menu terlaris hari ini</h2>
              {d.top_menus.length === 0 ? <p className="text-sm text-brand/70">Belum ada penjualan hari ini.</p> : <ol className="grid gap-2 sm:grid-cols-2">{d.top_menus.map((m, i) => <li key={m.name} className="flex items-center justify-between rounded-xl bg-cream/60 px-4 py-3"><span><span className="mr-3 font-semibold text-accent-deep">{i + 1}</span>{m.name}</span><span className="text-sm"><b>{m.qty}</b> terjual · {rupiah(m.amount)}</span></li>)}</ol>}
            </Card>
          </div>
        )}
      </AsyncView>
    </>
  )
}
