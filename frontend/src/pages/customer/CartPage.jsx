import { Navigate, useNavigate } from 'react-router-dom'
import { useCart } from '../../context/CartContext'
import Button from '../../components/ui/Button'
import { Stepper } from './CatalogPage'
import { rupiah } from '../../lib/format'

/** P-04: Keranjang Anda. Pengubah jumlah ditambahkan (ASUMSI) agar pelanggan bisa menghapus item. */
export default function CartPage() {
  const cart = useCart()
  const navigate = useNavigate()
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
                  <div><p className="text-lg font-medium">{menu.name}</p><p className="text-sm font-medium text-[#797474]">{qty} x {rupiah(menu.price)}</p></div>
                  <div className="text-right"><p className="text-lg">{rupiah(qty * menu.price)}</p><div className="mt-2 flex justify-end"><Stepper value={qty} onChange={(q) => cart.setQty(menu, q)} /></div></div>
                </li>
              ))}
            </ul>
            <div className="mt-8 flex items-center justify-between border-t border-black/10 pt-5 text-lg"><span className="font-medium">Total</span><span className="font-medium tabular-nums">{rupiah(cart.total)}</span></div>
          </>
        )}
      </div>
      {cart.lines.length > 0 && <div className="fixed inset-x-0 bottom-0 mx-auto max-w-md p-4"><Button size="lg" block onClick={() => navigate('/order/payment')}>Pilih Metode Pembayaran</Button></div>}
    </>
  )
}
