import { useAuth } from '../context/AuthContext'

export default function UserChip({ dark = false }) {
  const { user } = useAuth()
  if (!user) return null
  return (
    <div className="flex items-center gap-3">
      <div className="grid h-10 w-10 place-items-center rounded-full bg-sand font-semibold text-brand-ink" aria-hidden>{user.name.slice(0, 1)}</div>
      <div className="leading-tight">
        <p className={dark ? 'font-medium text-white' : 'font-medium text-brand-ink'}>{user.name}</p>
        <p className="text-sm text-stone">{user.role.label}</p>
      </div>
    </div>
  )
}
