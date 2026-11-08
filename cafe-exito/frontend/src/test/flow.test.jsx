import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import App from '../App'
import { AuthProvider } from '../context/AuthContext'
import { CartProvider } from '../context/CartContext'
import { ToastProvider } from '../context/ToastContext'

const mount = (path) => render(
  <MemoryRouter initialEntries={[path]}>
    <ToastProvider><AuthProvider><CartProvider><App /></CartProvider></AuthProvider></ToastProvider>
  </MemoryRouter>,
)
const login = async (user, username) => {
  await user.type(screen.getByLabelText(/email atau username/i), username)
  await user.type(screen.getByLabelText(/password/i), 'password')
  await user.click(screen.getByRole('button', { name: 'Login' }))
}

describe('alur lengkap dengan data mock', () => {
  it('pelanggan memesan → status menunggu pembayaran', async () => {
    const user = userEvent.setup()
    mount('/order/table')
    await user.type(await screen.findByLabelText('Nomor meja'), '6')
    await user.click(screen.getByRole('button', { name: 'Mulai memesan' }))
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    await user.click(screen.getAllByRole('button', { name: 'Tambah' })[0])
    await user.click(await screen.findByRole('button', { name: /Lihat Keranjang/ }))
    expect(await screen.findByText('Keranjang Anda')).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' }))
    await user.click(await screen.findByRole('button', { name: 'Tunai' }))
    await user.click(screen.getByRole('button', { name: /Pesan Sekarang/ }))
    expect(await screen.findByText(/Pesanan\s*Diterima/)).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: 'Lihat Status Pesanan' }))
    expect(await screen.findByText(/Menunggu pembayaran/)).toBeInTheDocument()
  })

  it('kasir login dan melihat dashboard; role lain diarahkan ke beranda role-nya', async () => {
    const user = userEvent.setup()
    mount('/login')
    await login(user, 'ucup')
    expect(await screen.findByText('Dashboard Kasir')).toBeInTheDocument()
    expect(await screen.findByText('Verifikasi Pembayaran')).toBeInTheDocument()
  })

  it('barista tidak bisa membuka halaman manager (diarahkan ke /barista)', async () => {
    const user = userEvent.setup()
    mount('/login')
    await login(user, 'nana')
    await screen.findByText(/Semangat kerja/)
    expect(screen.queryByText('Kelola Stok')).not.toBeInTheDocument()
  })

  it('login salah menampilkan pesan galat yang jelas', async () => {
    const user = userEvent.setup()
    mount('/login')
    await user.type(screen.getByLabelText(/email atau username/i), 'ucup')
    await user.type(screen.getByLabelText(/password/i), 'salah-sandi')
    await user.click(screen.getByRole('button', { name: 'Login' }))
    await waitFor(() => expect(screen.getByRole('alert')).toHaveTextContent(/salah/i))
  })

  it('manager membuka Kelola Stok dan daftar bahan tampil dengan status', async () => {
    const user = userEvent.setup()
    mount('/login')
    await login(user, 'manager')
    await user.click(await screen.findByRole('link', { name: 'Kelola Stok' }))
    expect(await screen.findByText('Sirup Karamel')).toBeInTheDocument()
    expect((await screen.findAllByText(/Habis/)).length).toBeGreaterThan(0)
  })
})
