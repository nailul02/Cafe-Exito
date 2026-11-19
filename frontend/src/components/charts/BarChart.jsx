import { rupiahShort } from '../../lib/format'

/** Bar chart SVG ringan untuk tren per jam/hari. Batang tertinggi diberi warna aksen (jam puncak). */
export default function BarChart({ data, height = 200 }) {
  const max = Math.max(1, ...data.map((d) => d.amount))
  const peak = data.reduce((p, d, i) => (d.amount > (data[p]?.amount ?? -1) ? i : p), 0)
  const w = 100 / Math.max(1, data.length)
  return (
    <div>
      <svg viewBox={`0 0 100 ${height / 4}`} preserveAspectRatio="none" className="w-full" style={{ height }} role="img" aria-label="Grafik penjualan">
        {data.map((d, i) => {
          const h = (d.amount / max) * (height / 4 - 4)
          return <rect key={d.label} x={i * w + w * 0.15} y={height / 4 - h} width={w * 0.7} height={Math.max(h, 0.6)} rx="0.8" className={i === peak && d.amount > 0 ? 'fill-accent' : 'fill-stone/60'}><title>{`${d.label}: ${rupiahShort(d.amount)}`}</title></rect>
        })}
      </svg>
      <div className="mt-1 flex justify-between text-[10px] text-brand/60">
        {data.filter((_, i) => data.length < 9 || i % 2 === 0).map((d) => <span key={d.label}>{d.label}</span>)}
      </div>
    </div>
  )
}
