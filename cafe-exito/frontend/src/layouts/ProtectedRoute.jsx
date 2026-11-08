import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { Spinner } from '../components/ui/States'
import { HOME_BY_ROLE } from '../lib/status'

/** Rute staf: wajib login; `roles` membatasi role yang boleh masuk. */
export default function ProtectedRoute({ roles }) {
  const { user, booting } = useAuth()
  const location = useLocation()
  if (booting) return <Spinner label="Memeriksa sesi…" />
  if (!user) return <Navigate to="/login" replace state={{ from: location.pathname }} />
  if (roles && !roles.includes(user.role.code)) return <Navigate to={HOME_BY_ROLE[user.role.code] || '/login'} replace />
  return <Outlet />
}
