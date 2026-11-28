import { useMemo, useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useResource } from '../../hooks/useResource'
import { AsyncView } from '../../components/ui/States'
import { cn } from '../../lib/cn'
import { rupiah } from '../../lib/format'
import { tokenStorage } from '../../api/tokenStorage'
import { MenuImage } from '../../lib/menuImages'

const GROUPS = [['minuman', 'Minuman'], ['makanan', 'Makanan'], ['cemilan', 'Cemilan']]

export function Stepper({ value, onChange, disabled, plusDisabled = disabled }) {
  return (
    <div className="flex items-center gap-3">
      <button type="button" data-testid="stepper-minus" aria-label="Kurangi" disabled={disabled || value === 0} onClick={() => onChange(value - 1)} className="grid h-7 w-7 place-items-center rounded-full bg-stone text-lg leading-none disabled:opacity-40">−</button>
      <span className="w-4 text-center tabular-nums" aria-live="polite">{value}</span>
      <button type="button" data-testid="stepper-plus" aria-label="Tambah" disabled={plusDisabled} onClick={() => onChange(value + 1)} className="grid h-7 w-7 place-items-center rounded-full bg-stone text-lg leading-none disabled:opacity-40">+</button>
    </div>
  )
}

/** P-03: katalog dengan tab Minuman/Makanan/Cemilan dan bilah keranjang. */
export default function CatalogPage({ allowNoTable = false }) {
  const cart = useCart()
  const navigate = useNavigate()
  const [group, setGroup] = useState('minuman')
  const res = useResource(() => customerApi.menus(), [], { poll: true, interval: 15000 })
  const items = useMemo(() => (res.data || []).filter((m) => m.display_group === group), [res.data, group])
  const sections = useMemo(() => {
    const grouped = new Map()
    items.forEach((menu) => grouped.set(menu.category.name, [...(grouped.get(menu.category.name) || []), menu]))
    return [...grouped.entries()]
  }, [items])
  if (!cart.table && !tokenStorage.getTableSession() && !allowNoTable) return <Navigate to="/order" replace />

  return (
    <>
      <div className="sticky top-0 z-10 grid grid-cols-3 gap-2 bg-white px-3 py-3">
        {GROUPS.map(([k, l]) => (
          <button key={k} data-testid={`menu-tab-${k}`} onClick={() => setGroup(k)} aria-pressed={group === k} className={cn('rounded-full py-1.5 text-sm font-bold text-white', group === k ? 'bg-brand' : 'bg-sand')}>{l}</button>
        ))}
      </div>
      <div className="flex-1 px-5 pb-32">
        <AsyncView res={res} empty={() => false}>
          {() => items.length === 0
            ? <p className="py-16 text-center text-brand/70">Belum ada menu di kategori ini.</p>
            : (
              <div className="space-y-6">
                {sections.map(([category, menus]) => <section key={category} aria-label={category}>
                  <h2 className="mb-1 border-b border-black/10 pb-2 text-sm font-bold uppercase tracking-wide text-brand/70">{category}</h2>
                  <ul className="divide-y divide-black/5">
                {menus.map((m) => (
                  <li key={m.id} data-testid={`menu-item-${m.id}`} className={cn('flex items-center gap-4 py-5', !m.is_orderable && 'opacity-60')}>
                    <MenuImage menu={m} className="h-14 w-16 rounded-xl object-cover" />
                    <div className="min-w-0 flex-1">
                      <p className="text-lg font-medium leading-tight">{m.name}</p>
                      <p className="font-medium text-[#797474]">{rupiah(m.price)}</p>
                      {!m.is_orderable && <p className="text-xs font-bold text-bad">HABIS · {m.unavailable_reason || 'Menu tidak tersedia'}</p>}
                    </div>
                    <Stepper value={cart.qtyOf(m.id)} disabled={!m.is_orderable && cart.qtyOf(m.id) === 0} plusDisabled={!m.is_orderable} onChange={(q) => cart.setQty(m, q)} />
                  </li>
                ))}
                  </ul>
                </section>)}
              </div>
            )}
        </AsyncView>
      </div>
      {cart.count > 0 && (
        <div className="fixed inset-x-0 bottom-0 z-20 mx-auto max-w-md p-4">
          <button data-testid="view-cart-btn" onClick={() => navigate('/order/cart')} className="flex w-full items-center justify-between rounded-2xl bg-brand px-6 py-4 text-left text-white shadow-lg">
            <span><span className="block text-lg">{cart.count} item</span><span className="font-medium">{rupiah(cart.total)}</span></span>
            <span className="text-lg font-medium">Lihat Keranjang →</span>
          </button>
        </div>
      )}
    </>
  )
}
