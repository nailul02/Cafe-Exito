
import { useCallback, useState } from 'react'
import { Link, Outlet, useNavigate } from 'react-router-dom'
import { Eye, EyeOff } from 'lucide-react'

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
  const [form, setForm] = useState({
    login: '',
    password: '',
  })
  const [error, setError] = useState(null)
  const [busy, setBusy] = useState(false)
  const [showPassword, setShowPassword] = useState(false)

  const closeLogin = useCallback(() => {
    setLoginOpen(false)
    setShowPassword(false)
  }, [])

  const openLogin = () => {
    setError(null)
    setShowPassword(false)
    setLoginOpen(true)
  }

  const submit = async (event) => {
    event.preventDefault()
    setBusy(true)
    setError(null)

    try {
      const signedIn = await login(form.login, form.password)

      setLoginOpen(false)
      setForm({ login: '', password: '' })
      setShowPassword(false)

      const destination = getHomeByRole(
        signedIn?.role?.code || signedIn?.role,
        signedIn?.station
      )

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

      {/* Header */}
      <header className="sticky top-0 z-30 flex items-center justify-between border-b border-black/5 bg-white/95 px-4 py-3 backdrop-blur">
        <Link
          to="/"
          className="flex items-center gap-2 text-brand-ink"
          aria-label="Cafe Éxito, beranda"
        >
          <img
            src="/logo.png"
            alt=""
            className="h-9 w-9 rounded-full"
          />

          <span className="font-semibold">
            Cafe Éxito
          </span>
        </Link>

        {user ? (
          <button
            type="button"
            onClick={onLogout}
            className="rounded-full border border-brand/20 px-3 py-2 text-sm font-medium text-brand hover:bg-cream"
          >
            Keluar
          </button>
        ) : (
          <button
            type="button"
            onClick={openLogin}
            className="rounded-full border border-brand/20 px-3 py-2 text-sm font-medium text-brand hover:bg-cream"
          >
            Login Karyawan
          </button>
        )}
      </header>

      {/* Halaman pelanggan */}
      <Outlet />

      {/* Modal Login Karyawan */}
      <Modal
        open={loginOpen}
        title="Login Karyawan"
        onClose={closeLogin}
      >
        <form onSubmit={submit} className="space-y-4">

          {/* Username */}
          <Field
            label="Email atau username"
            error={error?.errors?.login}
          >
            <Input
              autoFocus
              value={form.login}
              onChange={(event) =>
                setForm({
                  ...form,
                  login: event.target.value,
                })
              }
              placeholder="nama@cafeexito.com"
              autoComplete="username"
              required
            />
          </Field>

          {/* Password dengan tombol tampil/sembunyikan */}
          <Field
            label="Password"
            error={error?.errors?.password}
          >
            <div className="relative">
              <Input
                type={showPassword ? 'text' : 'password'}
                value={form.password}
                onChange={(event) =>
                  setForm({
                    ...form,
                    password: event.target.value,
                  })
                }
                placeholder="••••••••"
                autoComplete="current-password"
                required
                className="pr-11"
              />

              <button
                type="button"
                onClick={() =>
                  setShowPassword((previous) => !previous)
                }
                className="absolute inset-y-0 right-0 flex items-center justify-center px-3 text-gray-500 transition-colors hover:text-brand-ink focus:outline-none"
                aria-label={
                  showPassword
                    ? 'Sembunyikan kata sandi'
                    : 'Tampilkan kata sandi'
                }
                title={
                  showPassword
                    ? 'Sembunyikan kata sandi'
                    : 'Tampilkan kata sandi'
                }
              >
                {showPassword ? (
                  <EyeOff size={20} />
                ) : (
                  <Eye size={20} />
                )}
              </button>
            </div>
          </Field>

          {/* Pesan error */}
          {error && !error?.errors?.login && (
            <p
              className="text-sm text-bad"
              role="alert"
            >
              {error.message || 'Login gagal. Periksa kembali akun Anda.'}
            </p>
          )}

          {/* Tombol Login */}
          <Button
            type="submit"
            loading={busy}
            block
          >
            Login
          </Button>

        </form>
      </Modal>
    </div>
  )
}
