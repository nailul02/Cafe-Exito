import { Navigate } from 'react-router-dom'

/** Halaman ini digantikan oleh tab antrean di ProductionDashboardPage. Redirect agar tidak ada broken page. */
export default function StationQueuePage() {
  return <Navigate to="/production" replace />
}
