import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useCart } from '../../context/CartContext'
import Button from '../../components/ui/Button'

/** P-02: Selamat Datang + isi nomor meja. Tombol "Mulai memesan" ditambahkan karena tidak tergambar di Figma. */
export default function TableNumberPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const [value, setValue] = useState(cart.table || '')
  const fromQr = Boolean(cart.table)
  const valid = /^\d{1,3}$/.test(String(value)) && Number(value) > 0

  const go = (e) => { e.preventDefault(); if (valid) { cart.setTable(String(Number(value)), fromQr ? undefined : null); navigate('/order/menu') } }
  return (
    <form onSubmit={go} className="flex flex-1 flex-col items-center px-6 pt-10 text-center">
      <img src="/logo.png" alt="Cafe Exito" className="h-28 w-28 rounded-full" />
      <h1 className="mt-6 text-4xl font-normal">Selamat Datang!</h1>
      <p className="mt-8 max-w-xs text-xl font-medium">Silakan isi nomor meja anda untuk mulai memesan</p>
      <div className="mt-10 flex items-center gap-6">
        <img src="/table.png" alt="" className="h-24" />
        <div className="text-left">
          <label htmlFor="meja" className="text-xl">Nomor Meja</label>
          <input id="meja" inputMode="numeric" pattern="[0-9]*" maxLength={3} value={value} onChange={(e) => setValue(e.target.value.replace(/\D/g, ''))} placeholder="0" aria-label="Nomor meja"
            className="mt-2 block w-24 rounded-xl bg-brand py-2 text-center text-2xl text-white placeholder:text-white/50" />
        </div>
      </div>
      {fromQr && <p className="mt-3 text-sm text-brand/70">Terdeteksi dari QR meja. Pastikan nomor sudah benar.</p>}
      <div className="mt-auto w-full pb-6 pt-10"><Button type="submit" size="lg" block disabled={!valid}>Mulai memesan</Button></div>
    </form>
  )
}
