import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { Spinner } from '../components/ui/States'
import { getHomeByRole } from '../lib/status'

/** Rute staf: wajib login; `roles` membatasi role yang boleh masuk. */
export default function ProtectedRoute({ roles }) {
  const { user, booting } = useAuth()
  if (booting) return <Spinner label="Memeriksa sesi…" />
  if (!user) return <Navigate to="/" replace />

  const raw = user?.role?.code || user?.role || ''
  const roleCode = raw === 'admin' ? 'manager' : (['barista', 'kitchen'].includes(raw) ? 'barista_kitchen' : raw)

  if (roles && !roles.includes(roleCode) && !roles.includes(raw)) {
    return <Navigate to={getHomeByRole(roleCode, user.station)} replace />
  }
  return <Outlet />
}
