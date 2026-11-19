import { env } from '../../config/env'

/** Penanda layar tanpa desain Figma / berbasis asumsi (lihat docs/IMPLEMENTATION_STATUS.md). */
export default function DevNote({ children }) {
  if (!env.showDevNotes) return null
  return <p className="mb-4 rounded-lg border border-dashed border-accent/60 bg-warn-soft/60 px-3 py-2 text-xs text-warn">ASUMSI · {children}</p>
}
