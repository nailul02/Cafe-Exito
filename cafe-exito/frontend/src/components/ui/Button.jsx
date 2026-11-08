import { cn } from '../../lib/cn'

const variants = {
  primary: 'bg-brand text-white hover:bg-brand-ink',
  dark: 'bg-brand-ink text-white hover:bg-black',
  sand: 'bg-sand text-brand-ink hover:bg-stone/60',
  outline: 'border border-brand/30 bg-white text-brand-ink hover:bg-cream',
  danger: 'bg-bad text-white hover:bg-bad/90',
  accent: 'bg-accent text-white hover:bg-accent-deep',
  ghost: 'text-brand-ink hover:bg-black/5',
}
const sizes = { sm: 'px-3 py-1.5 text-sm rounded-lg', md: 'px-4 py-2.5 text-[15px] rounded-xl', lg: 'px-5 py-4 text-base rounded-2xl' }

export default function Button({ variant = 'primary', size = 'md', loading = false, block = false, className, children, disabled, type = 'button', ...rest }) {
  return (
    <button type={type} disabled={disabled || loading} className={cn('inline-flex items-center justify-center gap-2 font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50', variants[variant], sizes[size], block && 'w-full', className)} {...rest}>
      {loading && <span className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden />}
      {children}
    </button>
  )
}
