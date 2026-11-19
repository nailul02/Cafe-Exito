import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter, useLocation } from 'react-router-dom'
import { afterEach, describe, expect, it, vi } from 'vitest'
import App from '../App'
import { AuthProvider } from '../context/AuthContext'
import { CartProvider } from '../context/CartContext'
import { ToastProvider } from '../context/ToastContext'

const Pathname = () => <output data-testid="pathname">{useLocation().pathname}</output>
const mount = (path) => render(
  <MemoryRouter initialEntries={[path]}>
    <ToastProvider><AuthProvider><CartProvider><App /></CartProvider></AuthProvider></ToastProvider>
    <Pathname />
  </MemoryRouter>,
)
const login = async (user, username) => {
  const open = screen.queryByRole('button', { name: 'Login Karyawan' })
  if (open) await user.click(open)
  await user.type(screen.getByLabelText(/email atau username/i), username)
  await user.type(screen.getByLabelText(/password/i), 'password')
  await user.click(screen.getByRole('button', { name: 'Login' }))
}

const changedProperties = []
const replaceProperty = (target, key, value) => {
  changedProperties.push([target, key, Object.getOwnPropertyDescriptor(target, key)])
  Object.defineProperty(target, key, { configurable: true, writable: true, value })
}

afterEach(() => {
  changedProperties.splice(0).reverse().forEach(([target, key, descriptor]) => {
    if (descriptor) Object.defineProperty(target, key, descriptor)
    else delete target[key]
  })
  vi.restoreAllMocks()
})

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

  it('beranda menampilkan menu pelanggan dan login karyawan melalui modal', async () => {
    const user = userEvent.setup()
    mount('/')
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: 'Login Karyawan' }))
    expect(screen.getByRole('dialog', { name: 'Login Karyawan' })).toBeInTheDocument()
    expect(screen.getByLabelText(/email atau username/i)).toBeInTheDocument()
    expect(screen.getByTestId('pathname')).toHaveTextContent('/')
  })

  it('kasir login dari modal dan melihat halaman kasir', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'ucup')
    expect(screen.getByLabelText(/email atau username/i)).toHaveValue('ucup')
    expect(screen.getByLabelText(/password/i)).toHaveValue('password')
    expect(await screen.findByText('Dashboard Kasir')).toBeInTheDocument()
    expect(await screen.findByText('Verifikasi Pembayaran')).toBeInTheDocument()
  })

  it('barista_kitchen tidak bisa membuka halaman manager (diarahkan ke /production)', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'nana')
    await screen.findByText(/Dashboard Produksi/)
    expect(screen.queryByText('Kelola Stok')).not.toBeInTheDocument()
  })

  it('Barista masuk ke antrean Minuman saja', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'nana')
    expect(await screen.findByText(/antrean minuman di stasiun barista/i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /Kitchen|Food|Makanan/i })).not.toBeInTheDocument()
  })

  it('Kitchen masuk ke antrean Makanan saja', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'dewi')
    expect(await screen.findByText(/antrean makanan di stasiun kitchen/i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /Barista|Drink|Minuman/i })).not.toBeInTheDocument()
  })

  it('manager dapat memilih antrean Barista dan Kitchen untuk monitoring saja', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    await user.click(await screen.findByRole('link', { name: 'Antrean Produksi' }))

    expect(await screen.findByRole('tab', { name: /Minuman.*Barista/ })).toHaveAttribute('aria-selected', 'true')
    expect(screen.queryByRole('button', { name: 'Mulai Proses' })).not.toBeInTheDocument()
    await user.click(screen.getByRole('tab', { name: /Makanan.*Kitchen/ }))
    expect(await screen.findByText(/antrean makanan di stasiun kitchen/i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: 'Mulai Proses' })).not.toBeInTheDocument()
  })

  it('login salah menampilkan pesan galat yang jelas', async () => {
    const user = userEvent.setup()
    mount('/')
    await user.click(screen.getByRole('button', { name: 'Login Karyawan' }))
    await user.type(screen.getByLabelText(/email atau username/i), 'ucup')
    await user.type(screen.getByLabelText(/password/i), 'salah-sandi')
    await user.click(screen.getByRole('button', { name: 'Login' }))
    await waitFor(() => expect(screen.getByRole('alert')).toHaveTextContent(/salah/i))
    expect(screen.getByRole('dialog', { name: 'Login Karyawan' })).toBeInTheDocument()
    expect(screen.getByText('Es Kopi Susu')).toBeInTheDocument()
  })

  it('modul stok tidak tersedia pada navigasi Manager', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    expect(await screen.findByRole('link', { name: 'Dashboard' })).toBeInTheDocument()
    expect(screen.queryByRole('link', { name: 'Kelola Stok' })).not.toBeInTheDocument()
    expect(screen.queryByRole('link', { name: 'Pembelian' })).not.toBeInTheDocument()
  })

  it('owner login dan diarahkan ke /owner', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'owner')
    expect(await screen.findByText('Owner Dashboard')).toBeInTheDocument()
  })

  it('sesi karyawan dipulihkan saat halaman internal dimuat ulang', async () => {
    const user = userEvent.setup()
    const app = mount('/')
    await login(user, 'manager')
    expect(await screen.findByText('Manager Dashboard')).toBeInTheDocument()
    app.unmount()
    mount('/manager')
    expect(await screen.findByText('Manager Dashboard')).toBeInTheDocument()
  })

  it('halaman absensi dapat diakses oleh staf berwenang', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'ucup')
    mount('/attendance')
    expect(await screen.findByText(/Absensi/i)).toBeInTheDocument()
  })

  it('manager dapat membuka rekap absensi /manager/attendance', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    mount('/manager/attendance')
    expect(await screen.findByText(/Rekap Absensi/i)).toBeInTheDocument()
  })

  it('owner dapat membuka laporan absensi /owner/attendance', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'owner')
    mount('/owner/attendance')
    expect(await screen.findByText(/Laporan Absensi/i)).toBeInTheDocument()
  })

  it('owner dapat membuka halaman akun karyawan', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'owner')
    await user.click(await screen.findByRole('link', { name: 'Akun Karyawan' }))
    expect(await screen.findByRole('heading', { name: 'Akun Karyawan' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /Tambah karyawan/i })).toBeInTheDocument()
  })

  it('manager tidak dapat membuka akun karyawan Owner-only', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    mount('/owner/users')
    expect(await screen.findByText('Manager Dashboard')).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: 'Akun Karyawan' })).not.toBeInTheDocument()
  })

  it('absensi mengambil selfie dari getUserMedia dan GPS sebelum mengirim check-out', async () => {
    const user = userEvent.setup()
    const stop = vi.fn()
    const stream = { getTracks: () => [{ stop }] }
    const getUserMedia = vi.fn().mockResolvedValue(stream)
    replaceProperty(navigator, 'mediaDevices', { getUserMedia })
    replaceProperty(navigator, 'geolocation', {
      getCurrentPosition: vi.fn((success) => success({ coords: { latitude: -6.2, longitude: 106.8, accuracy: 8.5 } })),
    })
    replaceProperty(HTMLMediaElement.prototype, 'play', vi.fn().mockResolvedValue())
    replaceProperty(HTMLVideoElement.prototype, 'videoWidth', 640)
    replaceProperty(HTMLVideoElement.prototype, 'videoHeight', 480)
    replaceProperty(HTMLCanvasElement.prototype, 'getContext', () => ({ drawImage: vi.fn() }))
    replaceProperty(HTMLCanvasElement.prototype, 'toBlob', function (callback) { callback(new Blob(['camera-image'], { type: 'image/jpeg' })) })
    replaceProperty(URL, 'createObjectURL', vi.fn(() => 'blob:camera-selfie'))
    replaceProperty(URL, 'revokeObjectURL', vi.fn())

    mount('/')
    await login(user, 'ucup')
    await user.click(await screen.findByRole('link', { name: 'Absensi' }))
    await user.click(await screen.findByRole('button', { name: 'Aktifkan kamera' }))
    await screen.findByLabelText('Preview kamera selfie')
    expect(getUserMedia).toHaveBeenCalledWith(expect.objectContaining({ video: expect.objectContaining({ facingMode: 'user' }) }))
    await user.click(screen.getByRole('button', { name: 'Ambil foto' }))
    expect(await screen.findByAltText('Preview selfie absensi')).toBeInTheDocument()
    expect(stop).toHaveBeenCalled()

    await user.click(screen.getByRole('button', { name: 'Ambil lokasi GPS' }))
    expect(await screen.findByText('Lokasi berhasil diperoleh')).toBeInTheDocument()
    await waitFor(() => expect(screen.getByRole('button', { name: 'Check-out' })).toBeEnabled())
    await user.click(screen.getByRole('button', { name: 'Check-out' }))
    expect(await screen.findByText(/Check-out tercatat/i)).toBeInTheDocument()
  })

  it('kasir tidak dapat membuka halaman manager (diarahkan ke /cashier)', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'ucup')
    mount('/manager')
    expect(await screen.findByText('Dashboard Kasir')).toBeInTheDocument()
    expect(screen.queryByText('Manager Dashboard')).not.toBeInTheDocument()
  })

  it('logout mengembalikan pengguna ke menu pelanggan', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'ucup')
    await screen.findByText('Dashboard Kasir')
    await user.click(screen.getByRole('button', { name: 'Keluar' }))
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Login Karyawan' })).toBeInTheDocument()
  })

  it('URL internal tanpa autentikasi kembali ke menu publik', async () => {
    mount('/manager')
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    expect(screen.queryByText('Manager Dashboard')).not.toBeInTheDocument()
  })
})
