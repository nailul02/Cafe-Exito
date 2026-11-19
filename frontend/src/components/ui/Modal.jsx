import { useEffect, useRef } from 'react'
import { cn } from '../../lib/cn'

export default function Modal({ open, title, onClose, children, footer, size = 'md' }) {
  const ref = useRef(null)
  useEffect(() => {
    if (!open) return undefined
    const onKey = (e) => e.key === 'Escape' && onClose?.()
    document.addEventListener('keydown', onKey)
    ref.current?.focus()
    return () => document.removeEventListener('keydown', onKey)
  }, [open, onClose])
  if (!open) return null
  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center bg-brand-ink/50 p-0 sm:items-center sm:p-4" onMouseDown={(e) => e.target === e.currentTarget && onClose?.()}>
      <div ref={ref} tabIndex={-1} role="dialog" aria-modal="true" aria-label={title} className={cn('max-h-[92vh] w-full overflow-y-auto rounded-t-3xl bg-paper shadow-2xl outline-none sm:rounded-3xl', size === 'lg' ? 'sm:max-w-2xl' : 'sm:max-w-lg')}>
        <header className="flex items-start justify-between gap-4 border-b border-black/5 px-6 py-4">
          <h2 className="font-ui text-lg font-semibold text-brand-ink">{title}</h2>
          <button onClick={onClose} aria-label="Tutup" className="rounded-lg p-1 text-brand/70 hover:bg-black/5">✕</button>
        </header>
        <div className="px-6 py-5">{children}</div>
        {footer && <footer className="flex flex-wrap justify-end gap-2 border-t border-black/5 px-6 py-4">{footer}</footer>}
      </div>
    </div>
  )
}
