import { cn } from '../../lib/cn'

export default function DataTable({ columns, rows, rowKey = 'id', onRowClick, surface = 'white', empty = 'Tidak ada data.' }) {
  const brown = surface === 'brown'
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[520px] text-left text-sm">
        <thead>
          <tr className={cn('border-b', brown ? 'border-white/20 text-white/80' : 'border-black/5 text-brand/70')}>
            {columns.map((c) => <th key={c.key} className={cn('px-3 py-2.5 font-medium whitespace-nowrap', c.align === 'right' && 'text-right')}>{c.header}</th>)}
          </tr>
        </thead>
        <tbody>
          {rows.length === 0 && <tr><td colSpan={columns.length} className="px-3 py-8 text-center opacity-70">{empty}</td></tr>}
          {rows.map((r) => (
            <tr key={r[rowKey]} onClick={onRowClick ? () => onRowClick(r) : undefined}
              className={cn('border-b last:border-0', brown ? 'border-white/10' : 'border-black/5', onRowClick && (brown ? 'cursor-pointer hover:bg-white/5' : 'cursor-pointer hover:bg-cream/60'))}>
              {columns.map((c) => <td key={c.key} className={cn('px-3 py-3 align-middle', c.align === 'right' && 'text-right tabular-nums')}>{c.render ? c.render(r) : r[c.key]}</td>)}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
