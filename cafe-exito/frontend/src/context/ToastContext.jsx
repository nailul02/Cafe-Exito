import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { cn } from '../lib/cn'

const ToastCtx = createContext(null)
export const useToast = () => useContext(ToastCtx)

export function ToastProvider({ children }) {
  const [items, setItems] = useState([])
  const push = useCallback((type, message) => {
    const id = Math.random().toString(36).slice(2)
    setItems((l) => [...l, { id, type, message }])
    setTimeout(() => setItems((l) => l.filter((x) => x.id !== id)), 3800)
  }, [])
  const api = useMemo(() => ({ success: (m) => push('ok', m), error: (m) => push('bad', m) }), [push])
  return (
    <ToastCtx.Provider value={api}>
      {children}
      <div className="pointer-events-none fixed inset-x-0 bottom-4 z-[60] flex flex-col items-center gap-2 px-4" role="status" aria-live="polite">
        {items.map((t) => (
          <div key={t.id} className={cn('pointer-events-auto max-w-md rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg', t.type === 'ok' ? 'bg-go-deep' : 'bg-bad')}>{t.message}</div>
        ))}
      </div>
    </ToastCtx.Provider>
  )
}
