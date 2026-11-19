import Button from './Button'

export function Spinner({ label = 'Memuat…' }) {
  return (
    <div className="flex items-center justify-center gap-3 py-10 text-sm text-brand/70" role="status">
      <span className="h-5 w-5 animate-spin rounded-full border-2 border-brand/40 border-t-brand" aria-hidden />{label}
    </div>
  )
}
export function EmptyState({ title, hint, action }) {
  return (
    <div className="flex flex-col items-center gap-1 px-4 py-10 text-center">
      <p className="font-medium text-brand-ink">{title}</p>
      {hint && <p className="max-w-xs text-sm text-brand/70">{hint}</p>}
      {action && <div className="mt-3">{action}</div>}
    </div>
  )
}
export function ErrorState({ error, onRetry }) {
  return (
    <div className="flex flex-col items-center gap-2 px-4 py-10 text-center">
      <p className="font-medium text-bad">{error?.message || 'Data tidak dapat dimuat.'}</p>
      {error?.code && <p className="text-xs text-brand/60">Kode: {error.code}</p>}
      {onRetry && <Button variant="outline" size="sm" onClick={onRetry}>Coba lagi</Button>}
    </div>
  )
}
/** Pembungkus status async: loading -> error -> empty -> konten. */
export function AsyncView({ res, empty, emptyTitle = 'Belum ada data', emptyHint, children }) {
  if (res.loading && !res.data) return <Spinner />
  if (res.error) return <ErrorState error={res.error} onRetry={res.reload} />
  const isEmpty = empty ? empty(res.data) : Array.isArray(res.data) && res.data.length === 0
  if (isEmpty) return <EmptyState title={emptyTitle} hint={emptyHint} />
  return children(res.data)
}
