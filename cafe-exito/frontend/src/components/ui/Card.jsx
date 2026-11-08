import { cn } from '../../lib/cn'

/** surface: "white" (tablet Owner/Manager) | "brown" (wireframe Kasir/Barista). */
export function Card({ surface = 'white', className, children, ...rest }) {
  return <section className={cn('rounded-2xl', surface === 'brown' ? 'bg-brand text-white' : 'border border-black/5 bg-white shadow-[0_1px_2px_rgba(52,33,30,.06)]', className)} {...rest}>{children}</section>
}

export function StatCard({ label, value, hint, tone, surface = 'white', className }) {
  const toneText = { bad: 'text-bad', ok: 'text-go-deep', warn: 'text-warn' }[tone] || ''
  return (
    <Card surface={surface} className={cn('flex flex-col justify-between p-4', className)}>
      <p className={cn('text-xs font-medium', surface === 'brown' ? 'text-white/80' : 'text-brand/70')}>{label}</p>
      <p className={cn('mt-1 font-semibold tabular-nums', surface === 'brown' ? 'text-3xl' : 'font-ui text-2xl', toneText)}>{value}</p>
      {hint && <p className={cn('mt-1 text-xs', surface === 'brown' ? 'text-white/70' : 'text-brand/60')}>{hint}</p>}
    </Card>
  )
}
