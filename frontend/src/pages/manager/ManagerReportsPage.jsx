import PageHeader from '../../components/ui/PageHeader'
import SalesReportView from '../../components/reports/SalesReportView'

/** MG-06: Kelola Laporan. Tab Penjualan dan Stok & Mutasi. Tab Absensi, rekap gaji, dan persetujuan laporan: di luar MVP (K-B1, K-B13). */
export default function ManagerReportsPage() {
  return (
    <>
      <PageHeader title="Kelola Laporan & Analitik" subtitle="Rekap penjualan operasional." />
      <SalesReportView />
    </>
  )
}
