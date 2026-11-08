import { useMemo, useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { customerApi } from '../../api'
import { useCart } from '../../context/CartContext'
import { useResource } from '../../hooks/useResource'
import { AsyncView } from '../../components/ui/States'
import { cn } from '../../lib/cn'
import { rupiah } from '../../lib/format'

const GROUPS = [['minuman', 'Minuman'], ['makanan', 'Makanan'], ['cemilan', 'Cemilan']]

export function Stepper({ value, onChange, disabled }) {
  return (
    <div className="flex items-center gap-3">
      <button type="button" aria-label="Kurangi" disabled={disabled || value === 0} onClick={() => onChange(value - 1)} className="grid h-7 w-7 place-items-center rounded-full bg-stone text-lg leading-none disabled:opacity-40">−</button>
      <span className="w-4 text-center tabular-nums" aria-live="polite">{value}</span>
      <button type="button" aria-label="Tambah" disabled={disabled} onClick={() => onChange(value + 1)} className="grid h-7 w-7 place-items-center rounded-full bg-stone text-lg leading-none disabled:opacity-40">+</button>
    </div>
  )
}

/** P-03: katalog dengan tab Minuman/Makanan/Cemilan dan bilah keranjang. */
export default function CatalogPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const [group, setGroup] = useState('minuman')
  const res = useResource(() => customerApi.menus(), [], { poll: true, interval: 15000 })
  const items = useMemo(() => (res.data || []).filter((m) => m.display_group === group), [res.data, group])
  if (!cart.table) return <Navigate to="/order/table" replace />

  return (
    <>
      <div className="sticky top-0 z-10 grid grid-cols-3 gap-2 bg-white px-3 py-3">
        {GROUPS.map(([k, l]) => (
          <button key={k} onClick={() => setGroup(k)} aria-pressed={group === k} className={cn('rounded-full py-1.5 text-sm font-bold text-white', group === k ? 'bg-brand' : 'bg-sand')}>{l}</button>
        ))}
      </div>
      <div className="flex-1 px-5 pb-32">
        <AsyncView res={res} empty={() => false}>
          {() => items.length === 0
            ? <p className="py-16 text-center text-brand/70">Belum ada menu di kategori ini.</p>
            : (
              <ul className="divide-y divide-black/5">
                {items.map((m) => (
                  <li key={m.id} className={cn('flex items-center gap-4 py-5', !m.is_orderable && 'opacity-60')}>
                    {m.image_url ? <img src={m.image_url} alt="" className="h-14 w-16 rounded-xl object-cover" /> : <div className="grid h-14 w-16 place-items-center rounded-xl bg-cream text-xs text-brand/50" aria-hidden>Exito</div>}
                    <div className="min-w-0 flex-1">
                      <p className="text-lg font-medium leading-tight">{m.name}</p>
                      <p className="font-medium text-[#797474]">{rupiah(m.price)}</p>
                      {!m.is_orderable && <p className="text-xs font-medium text-bad">{m.unavailable_reason || 'Habis'}</p>}
                    </div>
                    <Stepper value={cart.qtyOf(m.id)} disabled={!m.is_orderable && cart.qtyOf(m.id) === 0} onChange={(q) => cart.setQty(m, q)} />
                  </li>
                ))}
              </ul>
            )}
        </AsyncView>
      </div>
      {cart.count > 0 && (
        <div className="fixed inset-x-0 bottom-0 z-20 mx-auto max-w-md p-4">
          <button onClick={() => navigate('/order/cart')} className="flex w-full items-center justify-between rounded-2xl bg-brand px-6 py-4 text-left text-white shadow-lg">
            <span><span className="block text-lg">{cart.count} item</span><span className="font-medium">{rupiah(cart.total)}</span></span>
            <span className="text-lg font-medium">Lihat Keranjang →</span>
          </button>
        </div>
      )}
    </>
  )
}
