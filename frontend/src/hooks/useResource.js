import { useCallback, useEffect, useRef, useState } from 'react'
import { env } from '../config/env'

/**
 * Memuat data async + polling opsional (kontrak: interval 3-5 dtk).
 * - loading hanya true pada pemuatan pertama / ganti dependensi (bukan saat polling) agar UI tidak berkedip.
 * - polling berhenti saat tab tidak terlihat.
 */
export function useResource(fetcher, deps = [], { poll = false, interval = env.pollIntervalMs, enabled = true } = {}) {
  const [state, setState] = useState({ data: null, error: null, loading: enabled })
  const fetchRef = useRef(fetcher)
  fetchRef.current = fetcher
  const alive = useRef(true)

  const run = useCallback(async (silent = false) => {
    if (!silent) setState((s) => ({ ...s, loading: true, error: null }))
    try {
      const data = await fetchRef.current()
      if (alive.current) setState({ data, error: null, loading: false })
    } catch (error) {
      if (alive.current) setState((s) => ({ data: silent ? s.data : null, error: silent ? s.error : error, loading: false }))
    }
  }, [])

  useEffect(() => {
    alive.current = true
    if (!enabled) return undefined
    run(false)
    let timer
    if (poll) timer = setInterval(() => { if (!document.hidden) run(true) }, interval)
    return () => { alive.current = false; clearInterval(timer) }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, enabled, poll, interval])

  return { ...state, reload: () => run(false), refresh: () => run(true), setData: (data) => setState((s) => ({ ...s, data })) }
}
