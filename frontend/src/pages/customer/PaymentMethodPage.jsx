import { useRef } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useAction } from '../../hooks/useAction'
import { tokenStorage } from '../../api/tokenStorage'
import Button from '../../components/ui/Button'
import { cn } from '../../lib/cn'
import { rupiah } from '../../lib/format'
import { useResource } from '../../hooks/useResource'

const METHODS = [['qris', 'QRIS'], ['tunai', 'Tunai']]

/** P-05: pilih QRIS / Tunai lalu "Pesan Sekarang". Hanya dua metode (keputusan: tanpa payment gateway). */
export default function PaymentMethodPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const { busy, run } = useAction()
  const paymentInfo = useResource(() => customerApi.paymentInfo(), [])
  const placed = useRef(false) // setelah pesanan dibuat keranjang kosong; jangan dialihkan kembali ke menu
  if (!placed.current && (!cart.table || cart.lines.length === 0)) return <Navigate to="/order/menu" replace />

  const submit = async () => {
    const order = await run(() => customerApi.createOrder({
      table_number: cart.table, payment_method: cart.method, table_token: tokenStorage.getTable() || undefined,
      items: cart.lines.map((l) => ({ menu_id: l.menu.id, qty: l.qty })),
      client_request_id: crypto.randomUUID(),
    }))
    if (order) { placed.current = true; cart.placed(order); navigate('/order/done', { replace: true }) }
  }
  return (
    <>
      <header className="flex items-center gap-2 px-5 pt-6">
        <button onClick={() => navigate('/order/cart')} aria-label="Kembali" className="-ml-2 rounded-lg p-2 text-xl">←</button>
        <h1 className="text-xl font-medium">Pilih Metode Pembayaran</h1>
      </header>
      <div className="flex-1 space-y-5 px-5 pt-8">
        {METHODS.map(([k, l]) => (
          <button key={k} onClick={() => cart.setMethod(k)} aria-pressed={cart.method === k}
            className={cn('w-full rounded-2xl py-10 text-3xl font-medium text-white transition-all', cart.method === k ? 'bg-brand ring-4 ring-accent/70' : 'bg-brand/90 opacity-80')}>{l}</button>
        ))}
        {cart.method === 'qris' && <div className="rounded-2xl border border-brand/20 bg-white p-4 text-center text-sm text-brand-ink">
          {paymentInfo.data?.qris_image_url && <img src={paymentInfo.data.qris_image_url} alt="QRIS statis Cafe Éxito" className="mx-auto mb-3 max-h-64 max-w-full object-contain" />}
          <p>{paymentInfo.data?.instructions?.qris || 'Bayar menggunakan QRIS statis Cafe Éxito. Simpan bukti pembayaran untuk diunggah setelah pesanan dibuat.'}</p>
          <p className="mt-2 font-medium">Setelah pesanan dibuat, unggah bukti JPEG, PNG, atau WEBP maksimal 3 MB.</p>
        </div>}
      </div>
      <div className="p-4"><Button size="lg" block disabled={!cart.method} loading={busy} onClick={submit}><span>Pesan Sekarang</span><span className="ml-2 font-normal">{rupiah(cart.total)}</span></Button></div>
    </>
  )
}
