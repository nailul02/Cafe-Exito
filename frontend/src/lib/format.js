const rupiahFmt = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 })

/** Uang selalu bilangan bulat rupiah (kontrak API: T4). */
export const rupiah = (n) => `Rp ${rupiahFmt.format(Number(n || 0))}`
export const rupiahShort = (n) => {
  const v = Number(n || 0)
  if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1).replace('.', ',')} jt`
  if (v >= 1_000) return `Rp ${Math.round(v / 1_000)} rb`
  return rupiah(v)
}
export const qty = (n) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 3 }).format(Number(n || 0))

export const timeHM = (iso) => {
  if (!iso) return '-'
  const d = new Date(iso)
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(':', '.')
}
export const dateLong = (iso = new Date()) =>
  new Date(iso).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
export const dateTime = (iso) =>
  iso ? new Date(iso).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: false }) : '-'
