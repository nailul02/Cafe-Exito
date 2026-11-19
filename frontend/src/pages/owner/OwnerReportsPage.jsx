import { useState } from 'react'
import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import PageHeader from '../../components/ui/PageHeader'
import FilterTabs from '../../components/ui/FilterTabs'
import { Card } from '../../components/ui/Card'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import { Input, Select } from '../../components/ui/Form'
import SalesReportView from '../../components/reports/SalesReportView'
import { StatusPill } from '../../components/ui/Badge'
import { PAYMENT_STATUS } from '../../lib/status'
import { dateTime, rupiah } from '../../lib/format'

function Transactions() {
  const [search, setSearch] = useState('')
  const [method, setMethod] = useState('')
  const res = useResource(() => reportsApi.transactions({ search, method: method || undefined }), [search, method], { poll: true, interval: 10000 })
  const columns = [
    { key: 'paid', header: 'Tanggal & jam', render: (r) => dateTime(r.payment.paid_at) },
    { key: 'trx', header: 'No. transaksi', render: (r) => <span className="font-medium">{r.payment.transaction_number}</span> },
    { key: 'method', header: 'Metode', render: (r) => r.payment.method.label },
    { key: 'items', header: 'Ringkasan item', render: (r) => r.order.items.map((i) => `${i.qty}× ${i.name}`).join(', ') },
    { key: 'total', header: 'Total', align: 'right', render: (r) => rupiah(r.order.total) },
    { key: 'status', header: 'Status', render: (r) => <StatusPill map={PAYMENT_STATUS} code={r.payment.status.code} /> },
  ]
  return (
    <Card className="p-5">
      <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 className="font-ui font-semibold text-brand-ink">Riwayat transaksi</h2>
        <div className="flex gap-2"><Input className="w-52" placeholder="Cari no. transaksi / menu" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Cari transaksi" />
          <Select className="w-40" value={method} onChange={(e) => setMethod(e.target.value)} aria-label="Filter metode"><option value="">Semua metode</option><option value="qris">QRIS</option><option value="tunai">Tunai</option></Select></div>
      </div>
      <AsyncView res={res} emptyTitle="Belum ada transaksi">{(rows) => <DataTable rowKey="trx" columns={columns} rows={rows.map((r) => ({ ...r, trx: r.payment.transaction_number }))} />}</AsyncView>
    </Card>
  )
}

/** OW-02: Laporan penjualan (periode) dan riwayat transaksi. */
export default function OwnerReportsPage() {
  const [tab, setTab] = useState('sales')
  return (
    <>
      <PageHeader title="Laporan Penjualan" subtitle="Omzet, metode pembayaran, kategori, dan riwayat transaksi." />
      <FilterTabs variant="solid" className="mb-5" options={[{ value: 'sales', label: 'Ringkasan penjualan' }, { value: 'trx', label: 'Riwayat transaksi' }]} value={tab} onChange={setTab} />
      {tab === 'sales' ? <SalesReportView /> : <Transactions />}
    </>
  )
}
