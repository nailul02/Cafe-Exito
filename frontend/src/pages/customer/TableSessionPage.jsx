import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useResource } from '../../hooks/useResource'
import { AsyncView } from '../../components/ui/States'
import Button from '../../components/ui/Button'
import { rupiah } from '../../lib/format'
import { ORDER_STATUS } from '../../lib/status'

function paymentLabel(payment) {
  if (payment.proof_status === 'pending') return 'QRIS: bukti menunggu verifikasi'
  if (payment.status === 'verified' || payment.status === 'refunded') return `${payment.method.toUpperCase()}: terverifikasi`
  if (payment.status === 'pending' && payment.method === 'tunai') return 'Tunai: menunggu pembayaran di kasir'
  if (payment.status === 'pending' && payment.method === 'qris') return 'QRIS: menunggu pembayaran dan verifikasi'
  return `${payment.method.toUpperCase()}: ${payment.status}`
}

export default function TableSessionPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const session = useResource(() => customerApi.currentSession(), [], { poll: true, interval: 10000 })
  if (!cart.table) return <Navigate to="/order" replace />

  return (
    <div className="flex flex-1 flex-col px-5 py-6">
      <h1 className="text-2xl font-semibold">Riwayat Sesi Meja</h1>
      <AsyncView res={session} empty={() => false}>
        {(data) => (
          <>
            <p className="mt-1 text-sm text-brand/70">Meja {data.table.number} · {data.customer_name}</p>
            <div className="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-cream p-3 text-center text-sm">
              <div><p className="text-brand/60">Total</p><p className="font-semibold">{rupiah(data.total)}</p></div>
              <div><p className="text-brand/60">Terbayar</p><p className="font-semibold">{rupiah(data.paid)}</p></div>
              <div><p className="text-brand/60">Sisa</p><p className="font-semibold">{rupiah(data.balance)}</p></div>
            </div>
            <h2 className="mt-7 text-lg font-semibold">Pesanan dalam sesi</h2>
            <div className="mt-3 space-y-3">
              {data.orders.length === 0 && <p className="rounded-xl border border-dashed p-4 text-sm text-brand/60">Belum ada pesanan di sesi ini.</p>}
              {data.orders.map((order) => (
                <article key={order.public_token} className="rounded-xl border border-brand/15 p-4">
                  <div className="flex items-start justify-between gap-3">
                    <div><p className="font-semibold">{order.display_number}</p><p className="text-sm text-brand/70">{ORDER_STATUS[order.status]?.label || order.status}</p></div>
                    <div className="text-right"><p className="font-semibold">{rupiah(order.total)}</p><p className="text-xs text-brand/70">Sisa {rupiah(order.balance)}</p></div>
                  </div>
                  <ul className="mt-3 space-y-1 text-sm">
                    {order.items.map((item, index) => <li key={`${item.name}-${index}`} className="flex justify-between gap-3"><span>{item.name} × {item.qty - item.qty_cancelled}</span><span>{rupiah(item.unit_price * (item.qty - item.qty_cancelled))}</span></li>)}
                  </ul>
                  <div className="mt-3 space-y-1 border-t border-brand/10 pt-2 text-xs text-brand/70">
                    {order.payments.map((payment, index) => <p key={`${payment.method}-${index}`}>{paymentLabel(payment)} · {rupiah(payment.amount)}</p>)}
                  </div>
                  <Button className="mt-3" size="sm" variant="outline" onClick={() => navigate(`/order/status/${order.public_token}`)}>Detail pesanan</Button>
                </article>
              ))}
            </div>
            <div className="mt-auto pt-6"><Button size="lg" block onClick={() => navigate('/order/menu')}>Tambah Pesanan</Button></div>
          </>
        )}
      </AsyncView>
    </div>
  )
}
