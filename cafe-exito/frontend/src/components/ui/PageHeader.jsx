export default function PageHeader({ title, subtitle, actions }) {
  return (
    <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 className="font-ui text-2xl font-semibold text-brand-ink">{title}</h1>
        {subtitle && <p className="mt-1 max-w-2xl text-sm text-brand/70">{subtitle}</p>}
      </div>
      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  )
}
