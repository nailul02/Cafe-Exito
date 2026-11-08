import { useState } from 'react'
import { Navigate, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { env } from '../../config/env'
import { HOME_BY_ROLE } from '../../lib/status'
import Button from '../../components/ui/Button'
import { Field, Input } from '../../components/ui/Form'
import { resetMock } from '../../mocks'

const DEMO = [['owner', 'Owner'], ['manager', 'Manager'], ['ucup', 'Kasir'], ['nana', 'Barista'], ['dewi', 'Kitchen']]

export default function LoginPage() {
  const { user, login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [form, setForm] = useState({ login: '', password: '' })
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)

  if (user) return <Navigate to={HOME_BY_ROLE[user.role.code]} replace />

  const submit = async (e) => {
    e.preventDefault(); setBusy(true); setError(null)
    try {
      const u = await login(form.login, form.password)
      navigate(location.state?.from || HOME_BY_ROLE[u.role.code], { replace: true })
    } catch (err) { setError(err) } finally { setBusy(false) }
  }

  return (
    <div className="grid min-h-full place-items-center bg-white px-4 py-10">
      <div className="w-full max-w-sm">
        <img src="/logo.png" alt="Cafe Exito" className="mx-auto h-28 w-28 rounded-full" />
        <h1 className="mt-3 text-center text-3xl font-bold text-brand-ink">Login</h1>
        <form onSubmit={submit} className="mt-6 space-y-4 rounded-xl border border-black/15 p-6">
          <Field label="Email atau username" error={error?.errors?.login}>
            <Input value={form.login} onChange={(e) => setForm({ ...form, login: e.target.value })} placeholder="nama@cafeexito.com" autoComplete="username" required />
          </Field>
          <Field label="Password" error={error?.errors?.password}>
            <Input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} placeholder="••••••••" autoComplete="current-password" required />
          </Field>
          {error && !error.errors && <p className="text-sm text-bad" role="alert">{error.message}</p>}
          <Button type="submit" loading={busy} block>Login</Button>
        </form>
        {env.useMock && (
          <div className="mt-5 rounded-xl border border-dashed border-accent/60 bg-warn-soft/50 p-4 text-xs text-warn">
            <p className="font-medium">Mode demo (data mock, kata sandi: password)</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {DEMO.map(([u, l]) => <button key={u} type="button" onClick={() => setForm({ login: u, password: 'password' })} className="rounded-full bg-white px-3 py-1 font-medium text-brand-ink ring-1 ring-black/10">{l}</button>)}
            </div>
            <button type="button" onClick={() => { resetMock(); window.location.reload() }} className="mt-3 underline">Reset data demo</button>
          </div>
        )}
      </div>
    </div>
  )
}
