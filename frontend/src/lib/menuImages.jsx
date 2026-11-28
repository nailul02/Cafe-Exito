import { useState } from 'react'

/**
 * Pemetaan terpusat nama menu Cafe Éxito ke berkas gambar di /menu/.
 * Berkas fisik disimpan di frontend/public/menu/ dan disajikan oleh Vite pada /menu/<nama_file>.
 */
export const MENU_IMAGE_MAP = {
  // --- Kategori: Milkshake (Minuman) ---
  'Bubblegum': '/menu/bubblegum.jpg',
  'Choco Classic': '/menu/choco_classic.jpeg',
  'Green Tea': '/menu/green_tea.jpeg',
  'Sweetberry': '/menu/sweetberry.jpg',
  'Taro': '/menu/taro.jpeg',
  'Tiramisu': '/menu/tiramisu.jpeg',
  'Vanila': '/menu/vanilla.jpeg',

  // --- Kategori: Coffee (Minuman) ---
  'Americano': '/menu/americano.jpg',
  'Cafe Latte': '/menu/caffe_latte.png',
  'Cappuchino': '/menu/cappuccino.jpg',
  'Caramel Latte': '/menu/caramel_latte.jpg',
  'Espresso': '/menu/espresso.jpg',
  'Mochacino': '/menu/mochaccino.jpg',
  'Vanila Latte': '/menu/vanilla_latte.jpg',

  // --- Kategori: Tea (Minuman) ---
  'Lemon Tea': '/menu/lemon_tea.jpg',
  'Lychee Tea': '/menu/lychee_tea.jpg',
  'Strawberry Tea': '/menu/strawberry_tea.jpg',

  // --- Kategori: Tambahan Minuman (Minuman) ---
  'Es Kosong': '/menu/es_kosong.jpg',
  'Mineral Water': '/menu/air_mineral.jpg',

  // --- Kategori: Signature Drink (Minuman) ---
  'Butterscout': '/menu/butterscotch.jpg',
  'Caramel Machiato': '/menu/caramel_macchiato.jpg',
  'Kopsus Exito': '/menu/kopsus_exito.jpg',
  'Kopsus Regal': '/menu/kopi_susu_regal.jpg',
  'Kopsus Tiramisu': '/menu/kopi_susu_tiramisu.jpg',
  'Triple Peach': '/menu/triple_peach.jpg',

  // --- Kategori: Nasi Putih (Makanan) ---
  'Nasi Ajaib Exito': '/menu/nasi_ajaib_exito.jpg',
  'Nasi Telur Dadar': '/menu/nasi_telur_dadar.jpg',
  'Nasi Telur Tahu Tempe': '/menu/nasi_telur_tahu_tempe.jpg',
  'Pecel Ayam': '/menu/pecel_ayam.jpg',

  // --- Kategori: Olahan (Makanan) ---
  'Mie Goreng Biasa': '/menu/mie_goreng_biasa.jpg',
  'Mie Goreng Nugget': '/menu/mie_goreng_nugget.jpg',
  'Mie Goreng Sosis': '/menu/mie_goreng_sosis.jpg',
  'Mie Goreng Spesial': '/menu/mie_goreng_spesial.jpg',
  'Mie Nyemek': '/menu/mie_nyemek.jpg',
  'Nasi Goreng Biasa': '/menu/nasi_goreng_biasa.jpg',
  'Nasi Goreng Nugget': '/menu/nasi_goreng_nugget.jpg',
  'Nasi Goreng Sosis': '/menu/nasi_goreng_sosis.jpg',
  'Nasi Goreng Spesial': '/menu/nasi_goreng_spesial.jpg',

  // --- Kategori: Cemilan (Cemilan) ---
  'French Fries': '/menu/kentang_goreng.jpg',
  'Nugget': '/menu/nugget.jpg',
  'Sharing Snack': '/menu/sharing_snack.jpg',
  'Sosis': '/menu/sosis.jpg',

  // --- Kategori: Tambahan Makanan (Makanan) ---
  'Cabe Ijo': '/menu/cabe_ijo.jpg',
  'Cabe Terasi': '/menu/sambal_terasi.jpg',
  'Nasi': '/menu/nasi_putih.jpg',
}

// Normalizer untuk pencarian case-insensitive & variasi penulisan
const NORMALIZED_MAP = new Map()
for (const [key, value] of Object.entries(MENU_IMAGE_MAP)) {
  NORMALIZED_MAP.set(normalizeName(key), value)
}

function normalizeName(name) {
  if (!name) return ''
  return String(name)
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '')
}

/**
 * Mengembalikan URL gambar menu yang valid.
 * Prioritas:
 * 1. Jika menu memiliki image_url valid (bukan URL storage broken localhost:8000/storage/images/menus), gunakan itu.
 * 2. Cocokkan dengan MENU_IMAGE_MAP berdasarkan nama menu.
 * 3. Cocokkan dengan NORMALIZED_MAP untuk toleransi penulisan.
 * 4. Fallback ke null.
 */
export function getMenuImageUrl(menu) {
  if (!menu) return null

  // A valid backend image is the source of truth for menu customizations.
  if (menu.image_url && typeof menu.image_url === 'string') {
    const isBrokenStorage = menu.image_url.includes('/storage/images/menus/')
    if (!isBrokenStorage) {
      return menu.image_url
    }
  }

  // Use the local catalog map when the backend URL is absent or points to broken storage.
  if (menu.name && MENU_IMAGE_MAP[menu.name]) {
    return MENU_IMAGE_MAP[menu.name]
  }

  // Periksa normalisasi nama
  const normalized = normalizeName(menu.name)
  if (normalized && NORMALIZED_MAP.has(normalized)) {
    return NORMALIZED_MAP.get(normalized)
  }

  return null
}

/**
 * Komponen gambar menu dengan fallback elegan jika gambar belum tersedia atau gagal dimuat.
 */
export function MenuImage({
  menu,
  className = 'h-14 w-16 rounded-xl object-cover',
  fallbackClassName = 'grid h-14 w-16 place-items-center rounded-xl bg-cream text-xs text-brand/50',
  alt = '',
}) {
  const [failed, setFailed] = useState(false)
  const imageUrl = getMenuImageUrl(menu)

  if (!imageUrl || failed) {
    return (
      <div className={fallbackClassName} aria-hidden>
        Exito
      </div>
    )
  }

  return (
    <img
      src={imageUrl}
      alt={alt || menu?.name || ''}
      className={className}
      loading="lazy"
      onError={() => setFailed(true)}
    />
  )
}
