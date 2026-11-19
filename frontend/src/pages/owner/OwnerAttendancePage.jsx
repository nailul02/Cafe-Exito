import { attendanceApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import PageHeader from '../../components/ui/PageHeader'
import AttendanceReportTable from '../attendance/AttendanceReportTable'

/** Pemantauan absensi Owner bersifat read-only dan dibatasi permission backend. */
export default function OwnerAttendancePage() {
  const attendanceRes = useResource(() => attendanceApi.list({ per_page: 100 }), [])

  return (
    <>
      <PageHeader title="Laporan Absensi" subtitle="Riwayat kehadiran staf Cafe Éxito." />
      <AttendanceReportTable res={attendanceRes} showEvidence />
    </>
  )
}
