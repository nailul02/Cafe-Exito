import { cn } from '../../lib/cn'

export function Field({ label, error, hint, className, children }) {
  return (
    <label className={cn('block text-sm', className)}>
      {label && <span className="mb-1 block font-medium text-brand-ink">{label}</span>}
      {children}
      {hint && !error && <span className="mt-1 block text-xs text-brand/60">{hint}</span>}
      {error && <span className="mt-1 block text-xs text-bad" role="alert">{Array.isArray(error) ? error[0] : error}</span>}
    </label>
  )
}
const base = 'w-full rounded-xl border border-black/10 bg-white px-3 py-2.5 text-[15px] text-brand-ink placeholder:text-stone focus:border-brand focus:outline-none'
export const Input = ({ className, ...p }) => <input className={cn(base, className)} {...p} />
export const Select = ({ className, children, ...p }) => <select className={cn(base, className)} {...p}>{children}</select>
export const Textarea = ({ className, ...p }) => <textarea className={cn(base, 'min-h-20', className)} {...p} />

export function Toggle({ checked, onChange, label, disabled }) {
  return (
    <button type="button" role="switch" aria-checked={checked} aria-label={label} disabled={disabled} onClick={() => onChange(!checked)}
      className={cn('relative h-6 w-11 shrink-0 rounded-full transition-colors disabled:opacity-50', checked ? 'bg-go' : 'bg-stone')}>
      <span className={cn('absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', checked && 'translate-x-5')} />
    </button>
  )
}
