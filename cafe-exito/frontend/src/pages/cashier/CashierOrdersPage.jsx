import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { cashierApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { Card } from '../../components/ui/Card'
import FilterTabs from '../../components/ui/FilterTabs'
import DataTable from '../../components/ui/DataTable'
import Button from '../../components/ui/Button'
import { Input } from '../../components/ui/Form'
import PageHeader from '../../components/ui/PageHeader'
import { AsyncView } from '../../components/ui/States'
import { ORDER_STATUS } from '../../lib/status'
import { rupiah } from '../../lib/format'

const FILTERS = [{ value: 'semua', label: 'Semua' }, { value: 'menunggu_pembayaran', label: 'Menunggu Bayar' }, { value: 'dibayar', label: 'Sudah Bayar' }, { value: 'selesai', label: 'Selesai' }]

/** KS-02: Semua Pesanan (tabel) dengan filter status dan tombol Detail. */
export default function CashierOrdersPage() {
  const [filter, setFilter] = useState('semua')
  const [search, setSearch] = useState('')
  const navigate = useNavigate()
  const res = useResource(() => cashierApi.orders({ 'filter[status]': filter, search }), [filter, search], { poll: true })
  const columns = [
    { key: 'no', header: 'No', render: (o) => o.display_number },
    { key: 'meja', header: 'Meja', render: (o) => String(o.table.number).padStart(2, '0') },
    { key: 'total', header: 'Total', align: 'right', render: (o) => rupiah(o.total) },
    { key: 'bayar', header: 'Pembayaran', render: (o) => o.payment?.method?.label },
    { key: 'status', header: 'Status', render: (o) => ORDER_STATUS[o.status.code]?.label },
    { key: 'detail', header: '', render: (o) => <Button size="sm" variant="sand" onClick={() => navigate(`/cashier/orders/${o.id}`)}>Detail</Button> },
  ]
  return (
    <>
      <PageHeader title="Pesanan" />
      <Card surface="brown" className="p-5">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h2 className="text-xl font-medium">Semua Pesanan</h2>
          <Input className="max-w-52 text-brand-ink" placeholder="Cari no. pesanan" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Cari pesanan" />
        </div>
        <FilterTabs className="mt-3" options={FILTERS} value={filter} onChange={setFilter} />
        <div className="mt-4"><AsyncView res={res} emptyTitle="Tidak ada pesanan">{(rows) => <DataTable surface="brown" columns={columns} rows={rows} />}</AsyncView></div>
      </Card>
    </>
  )
}
