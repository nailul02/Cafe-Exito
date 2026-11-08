// Token staf disimpan di sessionStorage (hilang saat tab ditutup). Keputusan penyimpanan final: PENDING (lihat docs/DECISIONS_PENDING.md K-S2).
const TOKEN_KEY = 'exito.token'
const TABLE_KEY = 'exito.tableToken'

export const tokenStorage = {
  get: () => sessionStorage.getItem(TOKEN_KEY),
  set: (t) => sessionStorage.setItem(TOKEN_KEY, t),
  clear: () => sessionStorage.removeItem(TOKEN_KEY),
  getTable: () => sessionStorage.getItem(TABLE_KEY),
  setTable: (t) => (t ? sessionStorage.setItem(TABLE_KEY, t) : sessionStorage.removeItem(TABLE_KEY)),
}
