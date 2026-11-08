import { useState } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { NAV } from './navigation'
import UserChip from './UserChip'
import { cn } from '../lib/cn'

/** Layout Kasir / Barista / Kitchen: sidebar cokelat penuh + konten putih (mengikuti wireframe Android Expanded). */
export default function StationLayout() {
  const { user, logout } = useAuth()
  const [open, setOpen] = useState(false)
  const navigate = useNavigate()
  const items = NAV[user.role.code] || []
  const onLogout = async () => { await logout(); navigate('/login', { replace: true }) }

  return (
    <div className="flex min-h-full bg-white">
      <aside className={cn('fixed inset-y-0 left-0 z-40 flex w-72 flex-col rounded-r-3xl bg-brand p-6 text-white transition-transform lg:static lg:translate-x-0 lg:rounded-3xl lg:m-3', open ? 'translate-x-0' : '-translate-x-full')}>
        <div className="flex items-center gap-4">
          <img src="/logo.png" alt="" className="h-16 w-16 rounded-full bg-white object-cover" />
          <span className="text-2xl font-medium">cafe exito</span>
        </div>
        <nav className="mt-10 flex flex-1 flex-col gap-1" aria-label="Navigasi utama">
          {items.map((i) => (
            <NavLink key={i.to} to={i.to} end={i.end} onClick={() => setOpen(false)}
              className={({ isActive }) => cn('rounded-xl px-4 py-3 text-lg transition-colors', isActive ? 'bg-white/15 font-medium' : 'text-white/85 hover:bg-white/10')}>
              {i.label}
            </NavLink>
          ))}
        </nav>
        <button onClick={onLogout} className="rounded-xl px-4 py-3 text-left text-white/85 hover:bg-white/10">Keluar</button>
      </aside>
      {open && <div className="fixed inset-0 z-30 bg-brand-ink/40 lg:hidden" onClick={() => setOpen(false)} aria-hidden />}
      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex items-center justify-between gap-3 px-5 py-4">
          <button className="rounded-lg p-2 text-2xl lg:hidden" onClick={() => setOpen(true)} aria-label="Buka menu">☰</button>
          <span className="hidden lg:block" />
          <UserChip />
        </header>
        <main className="min-w-0 flex-1 px-5 pb-8"><Outlet /></main>
      </div>
    </div>
  )
}
