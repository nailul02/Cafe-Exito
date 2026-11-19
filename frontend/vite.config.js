import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// Frontend berdiri sendiri: tidak ada proxy wajib. Base URL API dibaca dari VITE_API_BASE_URL.
export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: { port: 5173, strictPort: false },
})
