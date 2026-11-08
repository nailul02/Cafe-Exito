import { useState } from 'react'
import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import PageHeader from '../../components/ui/PageHeader'
import FilterTabs from '../../components/ui/FilterTabs'
import { Card } from '../../components/ui/Card'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import DevNote from '../../components/ui/DevNote'
import SalesReportView from '../../components/reports/SalesReportView'
import { qty } from '../../lib/format'

function StockUsage() {
  const res = useResource(() => reportsApi.stockUsage({}), [])
  return (
    <Card className="p-5">
      <h2 className="mb-3 font-ui font-semibold text-brand-ink">Pemakaian bahan dari pesanan</h2>
      <AsyncView res={res} emptyTitle="Belum ada pemakaian" emptyHint="Muncul setelah tiket pesanan mulai diproses.">{(rows) => <DataTable rowKey="ingredient" columns={[{ key: 'ingredient', header: 'Bahan' }, { key: 'used', header: 'Terpakai', align: 'right', render: (r) => `${qty(r.used)} ${r.unit}` }]} rows={rows} />}</AsyncView>
    </Card>
  )
}

/** MG-06: Kelola Laporan. Tab Penjualan dan Stok & Mutasi. Tab Absensi, rekap gaji, dan persetujuan laporan: di luar MVP (K-B1, K-B13). */
export default function ManagerReportsPage() {
  const [tab, setTab] = useState('sales')
  return (
    <>
      <PageHeader title="Kelola Laporan & Analitik" subtitle="Rekap penjualan dan pemakaian bahan." />
      <DevNote>Tab Absensi & Shift, rekap gaji/lembur, dan "Disetujui oleh" di Figma tidak dibangun (K-B1, K-B13).</DevNote>
      <FilterTabs variant="solid" className="mb-5" options={[{ value: 'sales', label: 'Laporan Penjualan' }, { value: 'stock', label: 'Stok & Mutasi' }]} value={tab} onChange={setTab} />
      {tab === 'sales' ? <SalesReportView /> : <StockUsage />}
    </>
  )
}
