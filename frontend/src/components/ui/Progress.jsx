import { cn } from '../../lib/cn'

const tone = { ok: 'bg-go', warn: 'bg-accent', bad: 'bg-bad' }
export default function Progress({ percent, tone: t = 'ok', className }) {
  return (
    <div className={cn('h-2 w-full overflow-hidden rounded-full bg-black/10', className)} role="progressbar" aria-valuenow={Math.min(100, percent)} aria-valuemin={0} aria-valuemax={100}>
      <div className={cn('h-full rounded-full', tone[t])} style={{ width: `${Math.max(0, Math.min(100, percent))}%` }} />
    </div>
  )
}
