import { useState } from 'react'
import { Navigate, useLocation, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import Button from '../../components/ui/Button'
import { Input } from '../../components/ui/Form'

const PENDING_QR_KEY = 'exito.pendingTableQr'

/** Form nama hanya ditampilkan ketika QR belum memiliki sesi aktif. */
export default function TableNumberPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const location = useLocation()
  // Keep the QR stable across the context update that happens before navigation.
  // The pending token remains in sessionStorage until the session opens successfully,
  // so a refresh of this form can still resume the same QR flow.
  const [qrToken] = useState(() => sessionStorage.getItem(PENDING_QR_KEY))
  const [name, setName] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  if (!qrToken) return <Navigate to="/order" replace />

  const submit = async (event) => {
    event.preventDefault()
    const customerName = name.trim()
    if (!customerName) return setError('Nama pelanggan wajib diisi.')
    setBusy(true)
    setError('')
    try {
      const result = await customerApi.openSession(qrToken, customerName)
      if (result.requires_name || !result.session_token || !result.session || result.session.status !== 'active') {
        throw new Error('Sesi belum berhasil dibuka. Silakan coba lagi.')
      }
      sessionStorage.removeItem(PENDING_QR_KEY)
      cart.setSession({ ...result.session, table: result.table }, result.session_token)
      navigate('/order/menu', { replace: true })
    } catch (reason) {
      setError(reason.message || 'Sesi tidak dapat dibuka. Silakan scan ulang QR meja.')
    } finally {
      setBusy(false)
    }
  }

  return (
    <form onSubmit={submit} className="flex flex-1 flex-col items-center px-5 pt-8 text-center sm:px-6 sm:pt-10">
      <img src="/logo.png" alt="Cafe Exito" className="h-24 w-24 rounded-full sm:h-28 sm:w-28" />
      <h1 className="mt-5 text-3xl font-normal sm:mt-6 sm:text-4xl">Selamat Datang!</h1>
      <p className="mt-4 text-lg font-medium">{location.state?.table ? `Meja ${location.state.table}` : 'Sesi meja baru'}</p>
      <p className="mt-2 max-w-xs text-sm text-brand/70">Masukkan nama pelanggan untuk memulai sesi pemesanan.</p>
      <label className="mt-7 w-full max-w-sm text-left text-sm font-medium">
        Nama pelanggan
        <Input autoComplete="name" autoFocus maxLength={100} value={name} onChange={(event) => setName(event.target.value)} className="mt-2 w-full" placeholder="Contoh: Budi" />
      </label>
      {error && <p className="mt-4 text-sm text-bad" role="alert">{error}</p>}
      <div className="mt-auto w-full pb-6 pt-8"><Button data-testid="start-order-submit" type="submit" size="lg" block loading={busy} disabled={!name.trim()}>Mulai memesan</Button></div>
    </form>
  )
}
