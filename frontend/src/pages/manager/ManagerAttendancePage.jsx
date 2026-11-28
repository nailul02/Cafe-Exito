import { attendanceApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import PageHeader from '../../components/ui/PageHeader'
import AttendanceReportTable from '../attendance/AttendanceReportTable'

/** Rekap absensi seluruh pegawai; backend mensyaratkan attendance.view_all. */
export default function ManagerAttendancePage() {
  const attendanceRes = useResource(() => attendanceApi.list({ per_page: 100 }), [])

  return (
    <>
      <PageHeader title="Rekap Absensi" subtitle="Riwayat check-in dan check-out seluruh pegawai." />
      <AttendanceReportTable res={attendanceRes} showEvidence />
    </>
  )
}
