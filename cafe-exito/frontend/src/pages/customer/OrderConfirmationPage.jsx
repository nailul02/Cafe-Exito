import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useResource } from '../../hooks/useResource'
import Button from '../../components/ui/Button'
import DevNote from '../../components/ui/DevNote'

/** P-06: Pesanan Diterima + nomor meja dan nomor pesanan. Petunjuk bayar ditambahkan (ASUMSI, tidak ada di Figma). */
export default function OrderConfirmationPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const info = useResource(() => customerApi.paymentInfo(), [])
  const last = cart.lastOrder
  const order = useResource(() => customerApi.order(last.token), [last?.token], { enabled: Boolean(last) })
  if (!last) return <Navigate to="/order" replace />
  const method = order.data?.payment?.method?.code

  return (
    <div className="flex flex-1 flex-col items-center px-6 pt-20 text-center">
      <h1 className="text-3xl font-medium leading-snug">Pesanan<br />Diterima</h1>
      <p className="mt-10 text-3xl font-medium">Meja {last.table}. {last.display}</p>
      {method && info.data && (
        <div className="mt-8 max-w-xs rounded-2xl bg-cream px-5 py-4 text-sm text-brand-ink">
          <p className="font-medium">Langkah berikutnya</p>
          <p className="mt-1">{info.data.instructions[method]}</p>
          {method === 'qris' && info.data.qris_image_url && <img src={info.data.qris_image_url} alt="QRIS Cafe Exito" className="mx-auto mt-3 h-44 w-44" />}
        </div>
      )}
      <div className="mt-auto w-full space-y-3 pb-6 pt-8">
        <DevNote>Layar petunjuk pembayaran tidak ada di Figma. Gambar QRIS statis diambil dari pengaturan backend (K-T8).</DevNote>
        <Button size="lg" block onClick={() => navigate(`/order/status/${last.token}`)}>Lihat Status Pesanan</Button>
        <Button size="lg" block variant="primary" onClick={() => navigate('/order/menu')}>Pesan Lagi</Button>
      </div>
    </div>
  )
}
