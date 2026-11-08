import { useState } from 'react'
import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { Card, StatCard } from '../ui/Card'
import FilterTabs from '../ui/FilterTabs'
import Button from '../ui/Button'
import Progress from '../ui/Progress'
import { AsyncView } from '../ui/States'
import DataTable from '../ui/DataTable'
import BarChart from '../charts/BarChart'
import { rupiah } from '../../lib/format'

const PERIODS = [{ value: 'daily', label: 'Hari ini' }, { value: 'weekly', label: 'Minggu ini' }, { value: 'monthly', label: 'Bulan ini' }]
const mmss = (s) => (s == null ? '–' : `${Math.floor(s / 60)} mnt ${String(s % 60).padStart(2, '0')} dtk`)

function toCsv(report) {
  const rows = [['Periode', 'Penjualan (Rp)', 'Pesanan'], ...report.series.map((s) => [s.label, s.amount, s.orders])]
  return rows.map((r) => r.join(',')).join('\n')
}

/** Laporan penjualan bersama Manager & Owner. Tanpa HPP/laba bersih/pengeluaran/EDC (K-B3, K-B8 PENDING: di luar MVP). */
export default function SalesReportView() {
  const [period, setPeriod] = useState('daily')
  const res = useResource(() => reportsApi.sales({ period }), [period])
  const download = (r) => {
    const url = URL.createObjectURL(new Blob([toCsv(r)], { type: 'text/csv' }))
    const a = Object.assign(document.createElement('a'), { href: url, download: `laporan-penjualan-${period}.csv` }); a.click(); URL.revokeObjectURL(url)
  }
  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <FilterTabs variant="solid" options={PERIODS} value={period} onChange={setPeriod} />
        {res.data && <div className="flex gap-2"><Button variant="outline" size="sm" onClick={() => download(res.data)}>Unduh CSV</Button><Button variant="dark" size="sm" onClick={() => window.print()}>Cetak laporan</Button></div>}
      </div>
      <AsyncView res={res} empty={() => false}>
        {(r) => (
          <div className="print-area space-y-5">
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <StatCard label="Total omzet" value={rupiah(r.totals.revenue)} hint={`${r.totals.orders} transaksi`} />
              <StatCard label="Rata-rata transaksi" value={rupiah(r.totals.average_order)} hint={`${r.totals.items} item terjual`} />
              <StatCard label="Kecepatan saji rata-rata" value={mmss(r.avg_service_seconds)} hint="Dari pesan hingga siap" />
              <StatCard label="Metode terbanyak" value={[...r.by_method].sort((a, b) => b.amount - a.amount)[0]?.method === 'qris' ? 'QRIS' : 'Tunai'} hint="Berdasarkan nilai" />
            </div>
            <div className="grid gap-5 xl:grid-cols-3">
              <Card className="p-5 xl:col-span-2"><h2 className="font-ui font-semibold text-brand-ink">Tren penjualan</h2><p className="mb-3 text-xs text-brand/60">Batang oranye menandai puncak penjualan</p><BarChart data={r.series} /></Card>
              <Card className="p-5">
                <h2 className="mb-3 font-ui font-semibold text-brand-ink">Metode pembayaran</h2>
                <ul className="space-y-4">{r.by_method.map((m) => <li key={m.method}><div className="mb-1 flex justify-between text-sm"><span>{m.method === 'qris' ? 'QRIS' : 'Tunai'} <span className="text-brand/60">({m.orders})</span></span><span className="font-medium">{m.percent}% · {rupiah(m.amount)}</span></div><Progress percent={m.percent} tone={m.method === 'qris' ? 'ok' : 'warn'} /></li>)}</ul>
              </Card>
            </div>
            <div className="grid gap-5 xl:grid-cols-3">
              <Card className="p-5"><h2 className="mb-3 font-ui font-semibold text-brand-ink">Penjualan per kategori</h2><ul className="space-y-3">{r.by_category.map((c) => <li key={c.category}><div className="mb-1 flex justify-between text-sm"><span>{c.category}</span><span className="font-medium">{c.percent}% · {rupiah(c.amount)}</span></div><Progress percent={c.percent} tone="warn" /></li>)}</ul></Card>
              <Card className="p-5"><h2 className="mb-3 font-ui font-semibold text-brand-ink">5 menu terlaris</h2><ol className="space-y-2 text-sm">{r.top_menus.map((m, i) => <li key={m.name} className="flex items-center justify-between gap-2"><span><span className="mr-2 inline-grid h-6 w-6 place-items-center rounded-full bg-cream text-xs font-medium">{i + 1}</span>{m.name}</span><span className="text-right"><span className="font-medium">{m.qty}×</span> <span className="text-brand/60">{rupiah(m.amount)}</span></span></li>)}</ol></Card>
              <Card className="p-5"><h2 className="mb-3 font-ui font-semibold text-brand-ink">Rekap per kasir</h2><DataTable rowKey="cashier" columns={[{ key: 'cashier', header: 'Kasir' }, { key: 'orders', header: 'Transaksi', align: 'right' }, { key: 'amount', header: 'Omzet', align: 'right', render: (c) => rupiah(c.amount) }]} rows={r.cashier_recap} /></Card>
            </div>
          </div>
        )}
      </AsyncView>
    </div>
  )
}
