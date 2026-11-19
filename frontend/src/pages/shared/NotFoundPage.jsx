import { Link } from 'react-router-dom'

export default function NotFoundPage() {
  return (
    <div className="grid min-h-full place-items-center px-6 text-center">
      <div>
        <p className="text-5xl font-bold text-brand">404</p>
        <p className="mt-2 text-lg font-medium text-brand-ink">Halaman tidak ditemukan</p>
        <p className="mt-1 text-sm text-brand/70">Alamat yang Anda buka tidak ada atau sudah dipindahkan.</p>
        <Link to="/" className="mt-5 inline-block rounded-xl bg-brand px-5 py-2.5 text-white">Kembali ke beranda</Link>
      </div>
    </div>
  )
}
