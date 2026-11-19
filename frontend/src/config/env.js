// Satu-satunya tempat membaca variabel lingkungan Vite.
const bool = (v, d = false) => (v === undefined ? d : String(v).toLowerCase() === 'true')

export const env = {
  apiBaseUrl: (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1').replace(/\/$/, ''),
  useMock: bool(import.meta.env.VITE_USE_MOCK, true),
  pollIntervalMs: Number(import.meta.env.VITE_POLL_INTERVAL_MS || 4000),
  showDevNotes: bool(import.meta.env.VITE_SHOW_DEV_NOTES, true),
}
