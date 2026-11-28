import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter, useLocation } from 'react-router-dom'
import { afterEach, describe, expect, it, vi } from 'vitest'
import App from '../App'
import { customerApi } from '../api'
import { AuthProvider } from '../context/AuthContext'
import { CartProvider } from '../context/CartContext'
import { ToastProvider } from '../context/ToastContext'
import { buildSeed, createOrderIn, menuIsOrderable, verifyIn } from '../mocks/db'

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
  await user.type(screen.getByLabelText('Password'), 'password')
  await user.click(screen.getByRole('button', { name: 'Login' }))
}
const beginCustomerSession = async (user, tableNumber = '6', customerName = 'Pelanggan Test') => {
  mount(`/order?t=demo-table-${String(tableNumber).padStart(2, '0')}`)
  await user.type(await screen.findByLabelText('Nama pelanggan'), customerName)
  await user.click(screen.getByTestId('start-order-submit'))
  await screen.findByText('Es Kopi Susu')
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
  it('tidak menyediakan pilihan meja manual', async () => {
    const user = userEvent.setup()
    mount('/order/table')
    expect(await screen.findByText('Arahkan kamera ke QR unik yang terpasang pada meja.')).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /^Meja / })).not.toBeInTheDocument()
  })

  it.each(['1', '8', '15'])('scan QR meja %s membuka sesi dengan nama pelanggan', async (number) => {
    const user = userEvent.setup()
    const open = vi.spyOn(customerApi, 'openSession')
    mount(`/order?t=demo-table-${number.padStart(2, '0')}`)
    const name = await screen.findByLabelText('Nama pelanggan')
    await user.type(name, 'Budi Test')
    await user.click(screen.getByTestId('start-order-submit'))
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    expect(open).toHaveBeenCalledWith(`demo-table-${number.padStart(2, '0')}`, 'Budi Test')
    expect(localStorage.getItem('exito.tableSessionToken')).toContain('mock-session-')
  })

  it('memulihkan sesi aktif saat QR yang sama dipindai dari perangkat lain', async () => {
    const user = userEvent.setup()
    const open = vi.spyOn(customerApi, 'openSession')
    const first = mount('/order?t=demo-table-07')
    await user.type(await screen.findByLabelText('Nama pelanggan'), 'Nana')
    await user.click(screen.getByTestId('start-order-submit'))
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    first.unmount()
    const recovered = mount('/order?t=demo-table-07')
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    expect(screen.queryByLabelText('Nama pelanggan')).not.toBeInTheDocument()
    expect(open).toHaveBeenLastCalledWith('demo-table-07')
    recovered.unmount()
    mount('/order/menu')
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    expect(screen.getByTestId('pathname')).toHaveTextContent('/order/menu')
  })

  it('menampilkan error dan retry untuk QR yang tidak dikenali', async () => {
    const user = userEvent.setup()
    vi.spyOn(customerApi, 'openSession').mockRejectedValueOnce(new Error('QR meja tidak dikenali'))
    mount('/order?t=invalid-qr')
    expect(await screen.findByRole('alert')).toHaveTextContent('QR meja tidak dikenali')
    expect(screen.getByRole('button', { name: 'Coba lagi' })).toBeInTheDocument()
  })

  it('QR menggunakan token dan resolver menolak nomor meja biasa', async () => {
    const resolve = vi.spyOn(customerApi, 'table')
    await customerApi.table('demo-table-01')
    await expect(customerApi.table('1')).rejects.toThrow(/QR meja tidak dikenali/)
    expect(resolve).toHaveBeenCalledWith('demo-table-01')
  })

  it('POS Kasir memilih meja dari daftar ID dan menampilkan nomor pada pesanan', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'ucup')
    await user.click(await screen.findByTestId('new-pos-order-btn'))
    await screen.findByRole('heading', { name: 'Buat Pesanan Baru (POS Kasir)' })

    const tableOption = await screen.findByRole('option', { name: 'Meja 15' })
    await user.selectOptions(screen.getByRole('combobox'), tableOption.value)
    await user.click(screen.getByRole('heading', { name: 'Es Kopi Susu' }))
    await user.click(screen.getByRole('button', { name: 'Buat Pesanan' }))

    expect(await screen.findByText(/Meja 15/)).toBeInTheDocument()
  })

  it('menampilkan atau menyembunyikan kata sandi tanpa mengubah nilainya', async () => {
    const user = userEvent.setup()
    mount('/manager/reports')
    const password = await screen.findByTestId('login-password')
    await user.type(password, 'password')
    expect(password).toHaveAttribute('type', 'password')

    await user.click(screen.getByRole('button', { name: 'Tampilkan password' }))
    expect(password).toHaveAttribute('type', 'text')
    expect(password).toHaveValue('password')

    await user.click(screen.getByRole('button', { name: 'Sembunyikan password' }))
    expect(password).toHaveAttribute('type', 'password')
    expect(password).toHaveValue('password')
  })

  it('deep link staf tanpa sesi diarahkan ke login dan tujuan asal dipertahankan', async () => {
    const user = userEvent.setup()
    mount('/manager/reports')
    expect(await screen.findByRole('heading', { name: 'Login' })).toBeInTheDocument()
    await login(user, 'manager')
    expect(await screen.findByText('Kelola Laporan & Analitik')).toBeInTheDocument()
    expect(screen.getByTestId('pathname')).toHaveTextContent('/manager/reports')
  })

  it('navigasi Manager Meja & QR membuka pengelolaan meja, bukan akun pengguna', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    await screen.findByText('Manager Dashboard')
    await user.click(screen.getByRole('link', { name: 'Meja & QR' }))
    expect(await screen.findByRole('heading', { name: 'Meja & QR' })).toBeInTheDocument()
    expect(await screen.findByText('QR Code unik')).toBeInTheDocument()
  })

  it('QRIS Self-Order wajib memiliki bukti dan tidak bisa diubah menjadi tunai', () => {
    const state = buildSeed()
    const { order, payment } = createOrderIn(state, { table: 1, items: [[1, 1]], method: 'qris' })

    expect(() => verifyIn(state, payment.id, 3)).toThrow(/Bukti QRIS pelanggan/)
    expect(() => verifyIn(state, payment.id, 3, { method: 'tunai' })).toThrow(/metode pembayaran Self-Order tidak dapat diubah/i)
    expect(payment.status).toBe('pending')
    expect(order.status).toBe('menunggu_pembayaran')
    expect(state.tickets.filter((ticket) => ticket.order_id === order.id)).toHaveLength(0)
  })

  it('QRIS POS menunggu verifikasi manual dan menu tidak bergantung pada stok', () => {
    const state = buildSeed()
    expect(state.ingredients).toEqual([])
    expect(menuIsOrderable(state, state.menus[0]).ok).toBe(true)

    const { order, payment } = createOrderIn(state, { table: 1, items: [[1, 1]], method: 'qris', order_source: 'pos_cashier' })
    expect(payment.status).toBe('pending')
    expect(state.tickets.filter((ticket) => ticket.order_id === order.id)).toHaveLength(0)
    verifyIn(state, payment.id, 3)
    expect(payment.status).toBe('verified')
    expect(state.tickets.filter((ticket) => ticket.order_id === order.id)).toHaveLength(1)
    expect(state.movements).toEqual([])
  })

  it('pelanggan memesan → status menunggu pembayaran', async () => {
    const user = userEvent.setup()
    await beginCustomerSession(user, '6')
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
    await user.click(screen.getAllByRole('button', { name: 'Tambah' })[0])
    await user.click(await screen.findByRole('button', { name: /Lihat Keranjang/ }))
    expect(await screen.findByText('Keranjang Anda')).toBeInTheDocument()
    await waitFor(() => expect(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' })).toBeEnabled())
    await user.click(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' }))
    await user.click(await screen.findByRole('button', { name: 'Tunai' }))
    await user.click(screen.getByRole('button', { name: /Pesan Sekarang/ }))
    expect(await screen.findByText(/Pesanan\s*Diterima/)).toBeInTheDocument()
    expect(await screen.findByText(/Meja 6/)).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: 'Lihat Status Pesanan' }))
    expect(await screen.findByText(/Menunggu pembayaran/)).toBeInTheDocument()
  })

  it('pelanggan membatalkan sebagian kuantitas dengan alasan wajib', async () => {
    const user = userEvent.setup()
    await beginCustomerSession(user, '6')
    const add = screen.getAllByRole('button', { name: 'Tambah' })[0]
    await user.click(add); await user.click(add)
    await user.click(await screen.findByRole('button', { name: /Lihat Keranjang/ }))
    await waitFor(() => expect(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' })).toBeEnabled())
    await user.click(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' }))
    await user.click(await screen.findByRole('button', { name: 'Tunai' }))
    await user.click(screen.getByRole('button', { name: /Pesan Sekarang/ }))
    await screen.findByText(/Pesanan\s*Diterima/)
    await user.click(screen.getByRole('button', { name: 'Lihat Status Pesanan' }))
    const cancel = await screen.findByRole('button', { name: 'Batalkan' })
    expect(cancel).toBeDisabled()
    await user.type(screen.getByLabelText('Alasan pembatalan wajib'), 'Salah pesan')
    await user.selectOptions(screen.getByLabelText(/Jumlah pembatalan Es Kopi Susu/), '1')
    await user.click(cancel)
    expect(await screen.findByText(/1 dibatalkan · Salah pesan/)).toBeInTheDocument()
    expect(await screen.findByText(/1 item/)).toBeInTheDocument()
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
    expect(screen.getByLabelText('Password')).toHaveValue('password')
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
    await user.type(screen.getByLabelText('Password'), 'salah-sandi')
    await user.click(screen.getByRole('button', { name: 'Login' }))
    await waitFor(() => expect(screen.getByRole('alert')).toHaveTextContent(/salah/i))
    expect(screen.getByRole('dialog', { name: 'Login Karyawan' })).toBeInTheDocument()
    expect(await screen.findByText('Es Kopi Susu')).toBeInTheDocument()
  })

  it('modul stok tidak tersedia pada navigasi Manager', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    expect(await screen.findByRole('link', { name: 'Dashboard' })).toBeInTheDocument()
    expect(screen.queryByRole('link', { name: 'Kelola Stok' })).not.toBeInTheDocument()
    expect(screen.queryByRole('link', { name: 'Pembelian' })).not.toBeInTheDocument()
  })

  it('Manager hanya melihat status menu dan tidak mendapat widget pengaturan ketersediaan', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'manager')
    await user.click(await screen.findByRole('link', { name: 'Kelola Menu' }))
    expect(await screen.findByRole('heading', { name: 'Kelola Data Menu' })).toBeInTheDocument()
    expect(screen.queryByRole('switch')).not.toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /Ketersediaan Menu/ })).not.toBeInTheDocument()
  })

  it('stasiun Barista hanya melihat minuman dan dapat mengubah statusnya', async () => {
    const user = userEvent.setup()
    mount('/')
    await login(user, 'nana')
    await user.click(await screen.findByRole('link', { name: 'Antrean Produksi' }))
    expect(await screen.findByRole('heading', { name: 'Ketersediaan Menu Minuman' })).toBeInTheDocument()
    const panel = screen.getByRole('region', { name: 'Ketersediaan Menu Minuman' })
    await waitFor(() => expect(panel.textContent).toContain('Es Kopi Susu'))
    expect(panel.textContent).not.toContain('Nasi Goreng Exito')
    await user.click(panel.querySelector('button[aria-label="Tandai habis: Es Kopi Susu"]'))
    await waitFor(() => expect(panel.querySelector('button[aria-label="Tandai tersedia: Es Kopi Susu"]')).toBeInTheDocument())
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

  it('URL internal tanpa autentikasi diarahkan ke login staf', async () => {
    mount('/manager')
    expect(await screen.findByRole('heading', { name: 'Login' })).toBeInTheDocument()
    expect(screen.queryByText('Manager Dashboard')).not.toBeInTheDocument()
  })

  it('pelanggan mengunggah bukti QRIS tanpa melunasi payment otomatis', async () => {
    const user = userEvent.setup()
    await beginCustomerSession(user, '6')
    await user.click(screen.getAllByRole('button', { name: 'Tambah' })[0])
    await user.click(await screen.findByRole('button', { name: /Lihat Keranjang/ }))
    await waitFor(() => expect(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' })).toBeEnabled())
    await user.click(screen.getByRole('button', { name: 'Pilih Metode Pembayaran' }))
    await user.click(await screen.findByRole('button', { name: 'QRIS' }))
    await user.click(screen.getByRole('button', { name: /Pesan Sekarang/ }))
    await user.click(await screen.findByRole('button', { name: 'Lihat Status Pesanan' }))
    const file = new File(['bukti demo'], 'bukti.jpg', { type: 'image/jpeg' })
    await user.upload(await screen.findByLabelText('Pilih bukti pembayaran QRIS'), file)
    await user.click(screen.getByRole('button', { name: 'Kirim bukti' }))
    expect(await screen.findByText(/Bukti sudah diunggah dan sedang menunggu verifikasi Kasir/)).toBeInTheDocument()
  })
})
