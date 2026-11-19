import { useState } from 'react'
import { useToast } from '../context/ToastContext'

/** Menjalankan aksi (POST/PUT) dengan status busy dan toast galat seragam. */
export function useAction() {
  const [busy, setBusy] = useState(false)
  const toast = useToast()
  const run = async (fn, successMessage) => {
    setBusy(true)
    try {
      const out = await fn()
      if (successMessage) toast.success(successMessage)
      return out
    } catch (e) {
      toast.error(e.message || 'Terjadi kesalahan.')
      return undefined
    } finally { setBusy(false) }
  }
  return { busy, run }
}
