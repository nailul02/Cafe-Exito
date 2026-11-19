import { useCallback, useState } from 'react'
import { Link, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { getHomeByRole } from '../lib/status'
import Modal from '../components/ui/Modal'
import Button from '../components/ui/Button'
import { Field, Input } from '../components/ui/Form'

/** Layout pelanggan: satu kolom mobile (412 px di Figma), tanpa sidebar. */
export default function CustomerLayout() {
  const { user, login, logout } = useAuth()
  const navigate = useNavigate()
  const [loginOpen, setLoginOpen] = useState(false)
  const [form, setForm] = useState({ login: '', password: '' })
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)
  const closeLogin = useCallback(() => setLoginOpen(false), [])

  const submit = async (event) => {
    event.preventDefault()
    setBusy(true)
    setError(null)
    try {
      const signedIn = await login(form.login, form.password)
      setLoginOpen(false)
      setForm({ login: '', password: '' })
      const destination = getHomeByRole(signedIn?.role?.code || signedIn?.role, signedIn?.station)
      navigate(destination, { replace: true })
    } catch (err) {
      setError(err)
    } finally {
      setBusy(false)
    }
  }

  const onLogout = async () => {
    await logout()
    navigate('/', { replace: true })
  }

  return (
    <div className="mx-auto flex min-h-full max-w-md flex-col bg-white shadow-[0_0_40px_rgba(52,33,30,.08)]">
      <header className="sticky top-0 z-30 flex items-center justify-between border-b border-black/5 bg-white/95 px-4 py-3 backdrop-blur">
        <Link to="/" className="flex items-center gap-2 text-brand-ink" aria-label="Cafe Éxito, beranda">
          <img src="/logo.png" alt="" className="h-9 w-9 rounded-full" />
          <span className="font-semibold">Cafe Éxito</span>
        </Link>
        {user ? (
          <button type="button" onClick={onLogout} className="rounded-full border border-brand/20 px-3 py-2 text-sm font-medium text-brand hover:bg-cream">Keluar</button>
        ) : (
          <button type="button" onClick={() => { setError(null); setLoginOpen(true) }} className="rounded-full border border-brand/20 px-3 py-2 text-sm font-medium text-brand hover:bg-cream">Login Karyawan</button>
        )}
      </header>
      <Outlet />

      <Modal open={loginOpen} title="Login Karyawan" onClose={closeLogin}>
        <form onSubmit={submit} className="space-y-4">
          <Field label="Email atau username" error={error?.errors?.login}>
            <Input autoFocus value={form.login} onChange={(event) => setForm({ ...form, login: event.target.value })} placeholder="nama@cafeexito.com" autoComplete="username" required />
          </Field>
          <Field label="Password" error={error?.errors?.password}>
            <Input type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} placeholder="••••••••" autoComplete="current-password" required />
          </Field>
          {error && !error?.errors?.login && <p className="text-sm text-bad" role="alert">{error.message || 'Login gagal. Periksa kembali akun Anda.'}</p>}
          <Button type="submit" loading={busy} block>Login</Button>
        </form>
      </Modal>
    </div>
  )
}
