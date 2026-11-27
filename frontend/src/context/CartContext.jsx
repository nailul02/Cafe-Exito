import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { tokenStorage } from '../api/tokenStorage'

const CartCtx = createContext(null)
export const useCart = () => useContext(CartCtx)
const KEY = 'exito.cart.v1'

// Keranjang disimpan di klien (keputusan T7); server memvalidasi saat checkout.
export function CartProvider({ children }) {
  const [state, setState] = useState(() => {
    const empty = { table: null, session: null, lines: [], method: null, lastOrder: null }
    try {
      const saved = JSON.parse(sessionStorage.getItem(KEY))
      return tokenStorage.getTableSession() ? { ...empty, ...saved } : empty
    } catch { return empty }
  })
  useEffect(() => { sessionStorage.setItem(KEY, JSON.stringify(state)) }, [state])

  const value = useMemo(() => {
    const qtyOf = (id) => state.lines.find((l) => l.menu.id === id)?.qty || 0
    return {
      ...state,
      count: state.lines.reduce((a, l) => a + l.qty, 0),
      total: state.lines.reduce((a, l) => a + l.qty * l.menu.price, 0),
      qtyOf,
      setTable: (table, token) => { tokenStorage.setTable(token || null); setState((s) => ({ ...s, table })) },
      setSession: (session, token) => {
        tokenStorage.setTable(null)
        tokenStorage.setTableSession(token || null)
        const next = { ...state, table: session?.table?.number || null, session, lines: [], method: null, lastOrder: null }
        sessionStorage.setItem(KEY, JSON.stringify(next))
        setState(next)
      },
      setQty: (menu, qty) => setState((s) => {
        const rest = s.lines.filter((l) => l.menu.id !== menu.id)
        return { ...s, lines: qty > 0 ? [...rest, { menu, qty }].sort((a, b) => a.menu.id - b.menu.id) : rest }
      }),
      setMethod: (method) => setState((s) => ({ ...s, method })),
      placed: (order) => setState((s) => ({ ...s, lines: [], method: null, lastOrder: { token: order.public_token, display: order.display_number, table: order.table?.number || null } })),
      reset: () => { tokenStorage.setTable(null); tokenStorage.setTableSession(null); setState({ table: null, session: null, lines: [], method: null, lastOrder: null }) },
    }
  }, [state])
  return <CartCtx.Provider value={value}>{children}</CartCtx.Provider>
}
