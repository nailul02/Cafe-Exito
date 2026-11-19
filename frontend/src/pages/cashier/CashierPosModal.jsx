import { useState, useMemo, useRef } from 'react'
import { customerApi, cashierApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import Button from '../../components/ui/Button'
import { Input } from '../../components/ui/Form'
import { rupiah } from '../../lib/format'
import { cn } from '../../lib/cn'

function newRequestId() {
  return globalThis.crypto?.randomUUID?.()
    || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
      const random = Math.floor(Math.random() * 16)
      return (char === 'x' ? random : (random & 0x3) | 0x8).toString(16)
    })
}
export default function CashierPosModal({ isOpen, onClose, onSuccess }) {
  const [serviceType, setServiceType] = useState('dine_in') // 'dine_in' | 'takeaway'
  const [tableNumber, setTableNumber] = useState('')
  const [customerName, setCustomerName] = useState('')
  const [cart, setCart] = useState({}) // { [menuId]: qty }
  const [search, setSearch] = useState('')
  const [activeCategory, setActiveCategory] = useState('all')
  const [paymentMethod, setPaymentMethod] = useState('tunai') // 'tunai' | 'qris'
  const requestId = useRef(null)

  const menusRes = useResource(() => customerApi.menus(), [isOpen], { initial: [] })
  const tablesRes = useResource(() => cashierApi.tables(), [isOpen], { initial: [] })
  const { busy, run } = useAction()

  const menus = menusRes.data || []
  const tables = tablesRes.data || []

  // Kategori menu
  const categories = useMemo(() => {
    const map = { all: 'Semua' }
    menus.forEach((m) => {
      if (m.category?.name) {
        map[m.category.id] = m.category.name
      }
    })
    return Object.entries(map).map(([id, label]) => ({ id, label }))
  }, [menus])

  // Filtered menus
  const filteredMenus = useMemo(() => {
    return menus.filter((m) => {
      const matchCat = activeCategory === 'all' || String(m.category?.id) === String(activeCategory)
      const matchSearch = !search || m.name.toLowerCase().includes(search.toLowerCase())
      return matchCat && matchSearch
    })
  }, [menus, activeCategory, search])

  // Cart operations
  const addToCart = (menu) => {
    if (!menu.is_orderable) return
    setCart((prev) => ({
      ...prev,
      [menu.id]: (prev[menu.id] || 0) + 1,
    }))
  }

  const updateQty = (menuId, delta) => {
    setCart((prev) => {
      const current = prev[menuId] || 0
      const next = current + delta
      if (next <= 0) {
        const copy = { ...prev }
        delete copy[menuId]
        return copy
      }
      return { ...prev, [menuId]: next }
    })
  }

  // Cart calculation
  const cartItems = useMemo(() => {
    return Object.entries(cart)
      .map(([menuId, qty]) => {
        const menu = menus.find((m) => String(m.id) === String(menuId))
        if (!menu) return null
        return {
          menu_id: menu.id,
          name: menu.name,
          price: menu.price,
          qty,
          subtotal: menu.price * qty,
        }
      })
      .filter(Boolean)
  }, [cart, menus])

  const totalAmount = useMemo(() => {
    return cartItems.reduce((acc, item) => acc + item.subtotal, 0)
  }, [cartItems])

  const handleSubmit = async () => {
    if (cartItems.length === 0) {
      alert('Pilih minimal satu menu untuk membuat pesanan.')
      return
    }

    const payload = {
      client_request_id: requestId.current || (requestId.current = newRequestId()),
      service_type: serviceType,
      table_number: serviceType === 'dine_in' && tableNumber ? tableNumber : null,
      customer_name: customerName.trim() || (serviceType === 'takeaway' ? 'Pelanggan Takeaway' : (tableNumber ? `Meja ${tableNumber}` : 'Dine In (Bar)')),
      payment_method: paymentMethod,
      items: cartItems.map((item) => ({
        menu_id: item.menu_id,
        qty: item.qty,
      })),
    }

    const res = await run(() => cashierApi.createPosOrder(payload), 'Pesanan POS dibuat; pembayaran menunggu konfirmasi kasir')
    if (res) {
      // Reset form
      setCart({})
      setTableNumber('')
      setCustomerName('')
      requestId.current = null
      onSuccess?.(res)
      onClose()
    }
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
      <div className="relative flex h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-brand-surface border border-brand-border text-white shadow-2xl">
        {/* Header */}
        <div className="flex items-center justify-between border-b border-brand-border px-6 py-4 bg-brand-navy">
          <div>
            <h2 className="text-xl font-bold tracking-tight text-brand-sand">Buat Pesanan Baru (POS Kasir)</h2>
            <p className="text-xs text-white/70">Pesanan langsung di kasir untuk Dine-In & Takeaway</p>
          </div>
          <button
            onClick={onClose}
            className="rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white transition"
            aria-label="Tutup"
          >
            ✕
          </button>
        </div>

        {/* Content Body: Split View */}
        <div className="grid flex-1 grid-cols-12 overflow-hidden">
          {/* Kolom Kiri: Menu Selector (7 cols) */}
          <div className="col-span-12 md:col-span-7 flex flex-col border-r border-brand-border overflow-hidden bg-brand-surface">
            {/* Filter & Search Bar */}
            <div className="p-4 border-b border-brand-border space-y-3 bg-brand-navy/40">
              <div className="flex gap-2">
                <Input
                  type="text"
                  placeholder="Cari menu kopi, makanan, snack..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full text-brand-ink text-sm bg-white"
                />
                {search && (
                  <Button variant="outline" size="sm" onClick={() => setSearch('')}>
                    Reset
                  </Button>
                )}
              </div>

              {/* Category Pills */}
              <div className="flex gap-2 overflow-x-auto pb-1 text-xs">
                {categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => setActiveCategory(cat.id)}
                    className={cn(
                      'px-3 py-1.5 rounded-full whitespace-nowrap transition font-medium',
                      activeCategory === cat.id
                        ? 'bg-brand-sand text-brand-navy shadow'
                        : 'bg-white/10 text-white/80 hover:bg-white/20'
                    )}
                  >
                    {cat.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Menu Grid */}
            <div className="flex-1 overflow-y-auto p-4">
              {filteredMenus.length === 0 ? (
                <div className="py-12 text-center text-white/60">
                  <p className="font-medium">Menu tidak ditemukan</p>
                  <p className="text-xs mt-1">Coba kata kunci pencarian atau kategori lain</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  {filteredMenus.map((menu) => {
                    const inCartQty = cart[menu.id] || 0
                    return (
                      <div
                        key={menu.id}
                        onClick={() => addToCart(menu)}
                        className={cn(
                          'flex flex-col justify-between p-3 rounded-xl border transition cursor-pointer select-none relative',
                          menu.is_orderable
                            ? 'bg-brand-navy/60 border-brand-border hover:border-brand-sand hover:bg-brand-navy'
                            : 'bg-white/5 border-white/10 opacity-50 cursor-not-allowed'
                        )}
                      >
                        {inCartQty > 0 && (
                          <div className="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-brand-sand text-xs font-bold text-brand-navy shadow">
                            {inCartQty}
                          </div>
                        )}
                        <div>
                          <p className="text-xs font-semibold text-brand-sand truncate">{menu.category?.name || 'Menu'}</p>
                          <h4 className="font-medium text-sm leading-tight text-white mt-0.5 line-clamp-2">{menu.name}</h4>
                        </div>
                        <div className="mt-3 flex items-center justify-between pt-2 border-t border-white/10">
                          <span className="text-xs font-bold text-white/90">{rupiah(menu.price)}</span>
                          {!menu.is_orderable && (
                            <span className="text-[10px] text-red-400 font-medium">Habis</span>
                          )}
                        </div>
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          </div>

          {/* Kolom Kanan: Pesanan & Pembayaran (5 cols) */}
          <div className="col-span-12 md:col-span-5 flex flex-col bg-brand-navy/30 overflow-y-auto">
            {/* Tipe Layanan & Info Pelanggan */}
            <div className="p-4 border-b border-brand-border space-y-3 bg-brand-navy/50">
              <div>
                <label className="text-xs font-semibold uppercase tracking-wider text-white/70 block mb-1.5">
                  Jenis Layanan
                </label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setServiceType('dine_in')}
                    className={cn(
                      'py-2 px-3 rounded-lg text-sm font-medium border transition',
                      serviceType === 'dine_in'
                        ? 'bg-brand-sand text-brand-navy border-brand-sand font-bold'
                        : 'bg-white/5 text-white/80 border-white/10 hover:bg-white/10'
                    )}
                  >
                    🍽️ Dine In (Makan di Tempat)
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setServiceType('takeaway')
                      setTableNumber('')
                    }}
                    className={cn(
                      'py-2 px-3 rounded-lg text-sm font-medium border transition',
                      serviceType === 'takeaway'
                        ? 'bg-brand-sand text-brand-navy border-brand-sand font-bold'
                        : 'bg-white/5 text-white/80 border-white/10 hover:bg-white/10'
                    )}
                  >
                    🛍️ Takeaway (Bungkus)
                  </button>
                </div>
              </div>

              {/* Conditional Inputs: Meja vs Pelanggan */}
              <div className="grid grid-cols-2 gap-2 pt-1">
                {serviceType === 'dine_in' ? (
                  <div>
                    <label className="text-xs text-white/80 block mb-1">Nomor Meja *</label>
                    <select
                      value={tableNumber}
                      onChange={(e) => setTableNumber(e.target.value)}
                      className="w-full rounded-lg bg-white px-3 py-2 text-sm text-brand-ink font-medium border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-sand"
                    >
                      <option value="">Pilih Meja...</option>
                      {tables.map((t) => (
                        <option key={t.id} value={t.table_number}>
                          Meja {t.table_number}
                        </option>
                      ))}
                    </select>
                  </div>
                ) : (
                  <div>
                    <label className="text-xs text-white/80 block mb-1">Nomor Meja</label>
                    <input
                      type="text"
                      disabled
                      value="Tidak ada meja (Takeaway)"
                      className="w-full rounded-lg bg-white/10 px-3 py-2 text-xs text-white/60 border border-white/10 cursor-not-allowed"
                    />
                  </div>
                )}
                <div>
                  <label className="text-xs text-white/80 block mb-1">Nama Pelanggan</label>
                  <Input
                    type="text"
                    placeholder={serviceType === 'takeaway' ? 'Cth: Budi' : 'Opsional'}
                    value={customerName}
                    onChange={(e) => setCustomerName(e.target.value)}
                    className="w-full text-brand-ink text-sm bg-white"
                  />
                </div>
              </div>
            </div>

            {/* Cart Items List */}
            <div className="flex-1 p-4 overflow-y-auto">
              <div className="flex items-center justify-between mb-2">
                <h3 className="text-sm font-semibold text-brand-sand">Item Pesanan ({cartItems.length})</h3>
                {cartItems.length > 0 && (
                  <button
                    onClick={() => setCart({})}
                    className="text-xs text-red-400 hover:text-red-300 transition"
                  >
                    Kosongkan
                  </button>
                )}
              </div>

              {cartItems.length === 0 ? (
                <div className="flex h-36 flex-col items-center justify-center rounded-xl border border-dashed border-white/20 p-4 text-center text-white/50">
                  <p className="text-sm">Keranjang masih kosong</p>
                  <p className="text-xs mt-1">Pilih menu dari panel sebelah kiri</p>
                </div>
              ) : (
                <div className="space-y-2">
                  {cartItems.map((item) => (
                    <div
                      key={item.menu_id}
                      className="flex items-center justify-between rounded-lg bg-white/5 p-2.5 border border-white/10"
                    >
                      <div className="min-w-0 flex-1 pr-2">
                        <p className="text-sm font-medium text-white truncate">{item.name}</p>
                        <p className="text-xs text-white/60">{rupiah(item.price)}</p>
                      </div>

                      <div className="flex items-center gap-2">
                        <div className="flex items-center rounded-lg bg-white/10 p-0.5">
                          <button
                            type="button"
                            onClick={() => updateQty(item.menu_id, -1)}
                            className="flex h-6 w-6 items-center justify-center rounded text-white/80 hover:bg-white/20"
                          >
                            -
                          </button>
                          <span className="w-7 text-center text-xs font-bold text-white">{item.qty}</span>
                          <button
                            type="button"
                            onClick={() => updateQty(item.menu_id, 1)}
                            className="flex h-6 w-6 items-center justify-center rounded text-white/80 hover:bg-white/20"
                          >
                            +
                          </button>
                        </div>
                        <span className="w-16 text-right text-xs font-semibold text-brand-sand">
                          {rupiah(item.subtotal)}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Total & Pembayaran */}
            <div className="border-t border-brand-border p-4 bg-brand-navy/60 space-y-3">
              {/* Ringkasan Total */}
              <div className="space-y-1">
                <div className="flex justify-between text-xs text-white/70">
                  <span>Subtotal</span>
                  <span>{rupiah(totalAmount)}</span>
                </div>
                <div className="flex justify-between text-base font-bold text-white border-t border-white/10 pt-1">
                  <span>Total Tagihan</span>
                  <span className="text-brand-sand">{rupiah(totalAmount)}</span>
                </div>
              </div>

              {/* Pilihan Metode Bayar */}
              <div>
                <label className="text-xs font-semibold uppercase tracking-wider text-white/70 block mb-1">
                  Metode Pembayaran
                </label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setPaymentMethod('tunai')}
                    className={cn(
                      'py-2 px-3 rounded-lg text-xs font-semibold border transition text-center',
                      paymentMethod === 'tunai'
                        ? 'bg-brand-sand text-brand-navy border-brand-sand'
                        : 'bg-white/5 text-white/80 border-white/10 hover:bg-white/10'
                    )}
                  >
                    💵 Uang Tunai (Cash)
                  </button>
                  <button
                    type="button"
                    onClick={() => setPaymentMethod('qris')}
                    className={cn(
                      'py-2 px-3 rounded-lg text-xs font-semibold border transition text-center',
                      paymentMethod === 'qris'
                        ? 'bg-brand-sand text-brand-navy border-brand-sand'
                        : 'bg-white/5 text-white/80 border-white/10 hover:bg-white/10'
                    )}
                  >
                    📱 QRIS Statis
                  </button>
                </div>
              </div>

              {/* Status pembayaran dicatat setelah kasir menerima pembayaran. */}
              {paymentMethod === 'tunai' ? (
                <div className="rounded-lg bg-white/5 p-3 border border-white/10 text-xs text-white/80">
                  Pesanan dapat diproses dengan status pembayaran menunggu. Catat nominal dan kembalian saat pembayaran diterima dari daftar pesanan.
                </div>
              ) : (
                <div className="rounded-lg bg-blue-900/30 p-3 border border-blue-500/30 text-xs text-blue-200">
                  <p className="font-semibold mb-1">ℹ️ Verifikasi QRIS Manual</p>
                  <p>Minta pelanggan memindai QRIS kasir dan pastikan nominal transfer berhasil sebelum memproses pesanan.</p>
                </div>
              )}

              {/* Action Buttons */}
              <div className="flex gap-2 pt-2">
                <Button variant="outline" className="w-1/3" onClick={onClose} disabled={busy}>
                  Batal
                </Button>
                <Button
                  variant="sand"
                  className="w-2/3 font-bold"
                  loading={busy}
                  disabled={
                    cartItems.length === 0 ||
                    (serviceType === 'dine_in' && !tableNumber)
                  }
                  onClick={handleSubmit}
                >
                  Proses & Selesaikan
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
