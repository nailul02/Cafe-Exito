import { cn } from '../../lib/cn'

/** variant "pill" = chip abu (wireframe Figma); "solid" = tab tablet Owner/Manager. */
export default function FilterTabs({ options, value, onChange, variant = 'pill', className }) {
  return (
    <div role="tablist" className={cn('flex flex-wrap gap-2', className)}>
      {options.map((o) => {
        const active = o.value === value
        return (
          <button key={o.value} role="tab" aria-selected={active} onClick={() => onChange(o.value)}
            className={cn('rounded-full px-4 py-1.5 text-sm font-medium transition-colors',
              variant === 'pill' ? (active ? 'bg-brand text-white' : 'bg-sand text-brand-ink hover:bg-stone/60') : (active ? 'bg-brand-ink text-white' : 'bg-white text-brand-ink ring-1 ring-black/10 hover:bg-cream'))}>
            {o.label}{o.count != null && <span className="ml-1.5 opacity-70">{o.count}</span>}
          </button>
        )
      })}
    </div>
  )
}
