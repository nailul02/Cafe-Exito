import { useEffect, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import Button from '../../components/ui/Button'
import { Spinner } from '../../components/ui/States'
import DevNote from '../../components/ui/DevNote'

/** P-01: titik masuk dari QR. URL: /order?t=<qr_token>. Tanpa token, pelanggan mengetik nomor meja (K-T2 PENDING). */
export default function QrEntryPage() {
  const [params] = useSearchParams()
  const token = params.get('t')
  const navigate = useNavigate()
  const cart = useCart()
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!token) return
    customerApi.table(token)
      .then((t) => { cart.setTable(t.number, token); navigate('/order/table', { replace: true }) })
      .catch((e) => setError(e))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token])

  return (
    <div className="flex flex-1 flex-col items-center px-6 pt-10 text-center">
      <img src="/logo.png" alt="Cafe Exito" className="h-28 w-28 rounded-full" />
      <h1 className="mt-8 text-2xl font-medium tracking-wide">Scan untuk memesan</h1>
      {token && !error && <Spinner label="Mengenali meja Anda…" />}
      {error && <p className="mt-4 text-bad" role="alert">{error.message} Coba scan ulang atau masukkan nomor meja.</p>}
      {(!token || error) && (
        <div className="mt-6 w-full space-y-3">
          <p className="text-brand/80">Arahkan kamera ke QR di meja Anda. Jika tidak bisa, isi nomor meja secara manual.</p>
          <Button size="lg" block onClick={() => { cart.setTable(null, null); navigate('/order/table') }}>Masukkan nomor meja</Button>
        </div>
      )}
      <div className="mt-auto w-full pb-6 pt-10 text-left"><DevNote>Satu QR umum vs QR per meja belum diputuskan (K-T2). Kedua jalur didukung.</DevNote></div>
    </div>
  )
}
