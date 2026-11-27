import { useEffect, useRef, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { customerApi } from '../../api'
import { tokenStorage } from '../../api/tokenStorage'
import { useCart } from '../../context/CartContext'
import { Spinner } from '../../components/ui/States'

const PENDING_QR_KEY = 'exito.pendingTableQr'

/** QR meja membuka sesi baru atau memulihkan sesi aktif dari perangkat lain. */
export default function QrEntryPage() {
  const [params] = useSearchParams()
  const qrToken = params.get('t')
  const navigate = useNavigate()
  const cart = useCart()
  const [error, setError] = useState(null)
  const [retry, setRetry] = useState(0)
  const attempted = useRef(null)

  useEffect(() => {
    const requestKey = `${qrToken}:${retry}`
    if (!qrToken || attempted.current === requestKey) return
    attempted.current = requestKey
    setError(null)
    customerApi.openSession(qrToken)
      .then((result) => {
        if (result.requires_name) {
          if (!result.table?.number) throw new Error('QR meja tidak mengembalikan identitas meja yang valid.')
          sessionStorage.setItem(PENDING_QR_KEY, qrToken)
          navigate('/order/table', { replace: true, state: { table: result.table.number } })
          return
        }
        if (!result.session_token || !result.session || result.session.status !== 'active') {
          throw new Error('Sesi meja tidak dapat dipulihkan. Silakan coba scan QR kembali.')
        }
        sessionStorage.removeItem(PENDING_QR_KEY)
        cart.setSession({ ...result.session, table: result.table }, result.session_token)
        navigate('/order/menu', { replace: true })
      })
      .catch((reason) => setError(reason))
    // cart and navigate are stable context/router functions for this mount.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [qrToken, retry])

  return (
    <div className="flex flex-1 flex-col items-center px-6 pt-10 text-center">
      <img src="/logo.png" alt="Cafe Exito" className="h-28 w-28 rounded-full" />
      <h1 className="mt-8 text-2xl font-medium tracking-wide">Scan untuk memesan</h1>
      {qrToken && !error && <Spinner label="Mengenali QR dan memulihkan sesi Anda…" />}
      {error && <div className="mt-4 space-y-3"><p className="text-bad" role="alert">{error.message} Periksa koneksi lalu coba lagi.</p><button type="button" className="underline" onClick={() => setRetry((count) => count + 1)}>Coba lagi</button></div>}
      {!qrToken && <p className="mt-6 text-brand/80">Arahkan kamera ke QR unik yang terpasang pada meja.</p>}
    </div>
  )
}
