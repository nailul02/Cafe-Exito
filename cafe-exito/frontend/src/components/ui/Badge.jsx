import { cn } from '../../lib/cn'

const tones = {
  ok: 'bg-go-soft text-go-deep', warn: 'bg-warn-soft text-warn', bad: 'bg-bad-soft text-bad',
  info: 'bg-info-soft text-info', neutral: 'bg-black/5 text-brand-ink',
}
export function Badge({ tone = 'neutral', className, children }) {
  return <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap', tones[tone], className)}>{children}</span>
}
/** map: objek dari lib/status.js; code: kode status API. */
export function StatusPill({ map, code }) {
  const s = map[code] || { label: code, tone: 'neutral' }
  return <Badge tone={s.tone}>{s.label}</Badge>
}
