import { useState } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { NAV } from './navigation'
import { cn } from '../lib/cn'
import { dateLong } from '../lib/format'

/** Layout Manager (aksen oranye) dan Owner (aksen cokelat tua), mengikuti desain tablet Figma. */
export default function ManagementLayout() {
  const { user, logout } = useAuth()
  const [open, setOpen] = useState(false)
  const navigate = useNavigate()
  const isOwner = user.role.code === 'owner'
  const items = NAV[user.role.code] || []
  const onLogout = async () => { await logout(); navigate('/login', { replace: true }) }

  return (
    <div className="flex min-h-full bg-cream font-ui">
      <aside className={cn('fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-black/5 bg-paper p-4 transition-transform lg:static lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full')}>
        <div className="flex items-center gap-3 px-2 py-2">
          <img src="/logo.png" alt="" className="h-11 w-11 rounded-xl bg-white object-cover ring-1 ring-black/5" />
          <div className="leading-tight">
            <p className="font-semibold text-brand-ink">Cafe Exito</p>
            <p className="text-xs text-brand/70">{isOwner ? 'Owner Dashboard' : 'Manager Dashboard'}</p>
          </div>
        </div>
        <div className="mt-4 rounded-xl bg-white p-3 ring-1 ring-black/5">
          <p className="text-sm font-medium text-brand-ink">{user.name}</p>
          <p className="text-xs text-accent-deep">{user.role.label}</p>
        </div>
        <nav className="mt-4 flex flex-1 flex-col gap-1" aria-label="Navigasi utama">
          {items.map((i) => (
            <NavLink key={i.to} to={i.to} end={i.end} onClick={() => setOpen(false)}
              className={({ isActive }) => cn('rounded-xl px-3 py-2.5 text-[15px] font-medium transition-colors', isActive ? (isOwner ? 'bg-brand-ink text-white' : 'bg-accent text-white') : 'text-brand-ink/80 hover:bg-black/5')}>
              {i.label}
            </NavLink>
          ))}
        </nav>
        <button onClick={onLogout} className="rounded-xl px-3 py-2.5 text-left text-[15px] font-medium text-bad hover:bg-bad-soft">Keluar</button>
      </aside>
      {open && <div className="fixed inset-0 z-30 bg-brand-ink/40 lg:hidden" onClick={() => setOpen(false)} aria-hidden />}
      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex items-center justify-between gap-3 border-b border-black/5 bg-paper/80 px-5 py-3">
          <button className="rounded-lg p-2 text-xl lg:hidden" onClick={() => setOpen(true)} aria-label="Buka menu">☰</button>
          <p className="text-sm text-brand/70">{dateLong()}</p>
        </header>
        <main className="min-w-0 flex-1 p-5 lg:p-7"><Outlet /></main>
      </div>
    </div>
  )
}
