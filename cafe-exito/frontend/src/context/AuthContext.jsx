import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { authApi } from '../api'
import { tokenStorage } from '../api/tokenStorage'

const AuthCtx = createContext(null)
export const useAuth = () => useContext(AuthCtx)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [booting, setBooting] = useState(Boolean(tokenStorage.get()))

  useEffect(() => {
    if (!tokenStorage.get()) return
    authApi.me().then(setUser).catch(() => tokenStorage.clear()).finally(() => setBooting(false))
  }, [])

  useEffect(() => {
    const onExpired = () => setUser(null)
    window.addEventListener('auth:expired', onExpired)
    return () => window.removeEventListener('auth:expired', onExpired)
  }, [])

  const login = useCallback(async (loginId, password) => {
    const res = await authApi.login(loginId, password)
    tokenStorage.set(res.token)
    setUser(res.user)
    return res.user
  }, [])

  const logout = useCallback(async () => {
    try { await authApi.logout() } catch { /* token mungkin sudah tidak berlaku */ }
    tokenStorage.clear(); setUser(null)
  }, [])

  const can = useCallback((perm) => Boolean(user?.permissions?.includes(perm)), [user])
  const value = useMemo(() => ({ user, booting, login, logout, can, setUser }), [user, booting, login, logout, can])
  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>
}
