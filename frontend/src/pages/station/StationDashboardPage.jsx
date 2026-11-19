import { Navigate } from 'react-router-dom'

/** Halaman ini digantikan oleh ProductionDashboardPage. Redirect agar tidak ada broken page. */
export default function StationDashboardPage() {
  return <Navigate to="/production" replace />
}
