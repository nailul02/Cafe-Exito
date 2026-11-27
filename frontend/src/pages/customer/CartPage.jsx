import { useMemo } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useResource } from '../../hooks/useResource'
import Button from '../../components/ui/Button'
import { Stepper } from './CatalogPage'
import { rupiah } from '../../lib/format'

/** P-04: Keranjang Anda. Pengubah jumlah ditambahkan (ASUMSI) agar pelanggan bisa menghapus item. */
export default function CartPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const cartKey = useMemo(() => JSON.stringify(cart.lines.map(({ menu, qty }) => ({ menu_id: menu.id, qty }))), [cart.lines])
  const validation = useResource(() => customerApi.validateCart(JSON.parse(cartKey)), [cartKey], { enabled: cart.lines.length > 0, poll: true, interval: 10000 })
  const issues = validation.data?.issues || []
  if (!cart.table) return <Navigate to="/order/table" replace />
  return (
    <>
      <header className="flex items-center gap-2 px-5 pt-6">
        <button onClick={() => navigate('/order/menu')} aria-label="Kembali ke menu" className="-ml-2 rounded-lg p-2 text-xl">←</button>
        <h1 className="text-xl font-medium">Keranjang Anda</h1>
      </header>
      <div className="flex-1 px-5 pt-6 pb-32">
        {cart.lines.length === 0 ? (
          <div className="py-16 text-center"><p className="font-medium">Keranjang masih kosong</p><p className="text-sm text-brand/70">Pilih menu dulu untuk mulai memesan.</p><Button className="mt-4" onClick={() => navigate('/order/menu')}>Lihat menu</Button></div>
        ) : (
          <>
            <ul className="space-y-5">
              {cart.lines.map(({ menu, qty }) => (
                <li key={menu.id} className="flex items-start justify-between gap-3">
                  <div><p className="text-lg font-medium">{menu.name}</p><p className="text-sm font-medium text-[#797474]">{qty} x {rupiah(menu.price)}</p>{issues.filter((issue) => issue.menu_id === menu.id).map((issue) => <p role="alert" key={issue.menu_id} className="text-sm font-semibold text-bad">{issue.message}. Hapus item ini atau pilih menu lain.</p>)}</div>
                  <div className="text-right"><p className="text-lg">{rupiah(qty * menu.price)}</p><div className="mt-2 flex justify-end"><Stepper value={qty} onChange={(q) => cart.setQty(menu, q)} /></div></div>
                </li>
              ))}
            </ul>
            <div className="mt-8 flex items-center justify-between border-t border-black/10 pt-5 text-lg"><span className="font-medium">Total</span><span className="font-medium tabular-nums">{rupiah(cart.total)}</span></div>
          </>
        )}
      </div>
      {cart.lines.length > 0 && <div className="fixed inset-x-0 bottom-0 mx-auto max-w-md p-4"><Button data-testid="proceed-to-payment-btn" size="lg" block disabled={validation.loading || issues.length > 0} onClick={() => navigate('/order/payment')}>{issues.length ? 'Periksa menu yang habis' : 'Pilih Metode Pembayaran'}</Button></div>}
    </>
  )
}
