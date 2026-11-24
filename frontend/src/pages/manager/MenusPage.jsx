import { useEffect, useState } from 'react'
import { menusApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card, StatCard } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { Badge } from '../../components/ui/Badge'
import { Field, Input, Select } from '../../components/ui/Form'
import FilterTabs from '../../components/ui/FilterTabs'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'
import { rupiah } from '../../lib/format'
import { cn } from '../../lib/cn'

const EMPTY = { name: '', sku: '', price: '', category_id: '', cost_price: '' }

function MenuEditor({ menu, categories, onSaved }) {
  const [form, setForm] = useState(EMPTY)
  const [errors, setErrors] = useState(null)
  const { busy, run } = useAction()

  useEffect(() => {
    setErrors(null)
    if (!menu) {
      setForm({ ...EMPTY, category_id: categories[0]?.id || '' })
      return
    }
    setForm({ name: menu.name, sku: menu.sku || '', price: menu.price, category_id: menu.category.id, cost_price: menu.cost_price ?? '' })
  }, [menu, categories])

  const set = (key) => (event) => setForm({ ...form, [key]: event.target.value })
  const save = async () => {
    setErrors(null)
    try {
      const payload = { ...form, price: Number(form.price), category_id: Number(form.category_id), cost_price: form.cost_price === '' ? null : Number(form.cost_price) }
      const saved = await run(() => (menu ? menusApi.update(menu.id, payload) : menusApi.create(payload)), 'Menu disimpan')
      if (saved) onSaved(saved)
    } catch (error) { setErrors(error.errors) }
  }

  return (
    <Card className="p-5">
      <h2 className="font-ui text-lg font-semibold text-brand-ink">{menu ? `Edit menu: ${menu.name}` : 'Tambah menu baru'}</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <Field label="Nama menu" error={errors?.name} className="sm:col-span-2"><Input value={form.name} onChange={set('name')} /></Field>
        <Field label="Kode SKU" hint="Opsional"><Input value={form.sku} onChange={set('sku')} /></Field>
        <Field label="Kategori"><Select value={form.category_id} onChange={set('category_id')}>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</Select></Field>
        <Field label="Harga jual (Rp)" error={errors?.price}><Input inputMode="numeric" value={form.price} onChange={(event) => setForm({ ...form, price: event.target.value.replace(/\D/g, '') })} /></Field>
        <Field label="HPP / food cost (Rp)" hint="Opsional, belum dipakai laporan"><Input inputMode="numeric" value={form.cost_price} onChange={(event) => setForm({ ...form, cost_price: event.target.value.replace(/\D/g, '') })} /></Field>
      </div>
      <div className="mt-5 flex justify-end"><Button variant="dark" loading={busy} onClick={save} disabled={!form.name || !form.price}>Simpan menu</Button></div>
    </Card>
  )
}

/** Daftar dan editor menu Manager; status ketersediaan ditampilkan saja. */
export default function MenusPage() {
  const [search, setSearch] = useState('')
  const [filter, setFilter] = useState('semua')
  const [selected, setSelected] = useState(undefined)
  const menus = useResource(() => menusApi.list({ search }), [search])
  const cats = useResource(() => menusApi.categories(), [])
  const all = menus.data || []
  const rows = all.filter((menu) => filter === 'semua' || (filter === 'tersedia' ? menu.is_available : !menu.is_available))
  return (
    <>
      <PageHeader title="Kelola Data Menu" subtitle="Atur nama, kategori, dan harga jual menu." actions={<Button variant="accent" onClick={() => setSelected(null)}>+ Tambah menu baru</Button>} />
      <DevNote>Foto menu, pajak PB1, opsi tambahan, dan promo belum termasuk alur operasional yang disetujui.</DevNote>
      <div className="mb-5 grid gap-3 sm:grid-cols-2">
        <StatCard label="Menu tersedia" value={all.filter((menu) => menu.is_available).length} />
        <StatCard label="Menu habis" value={all.filter((menu) => !menu.is_available).length} tone="bad" />
      </div>
      <div className="grid gap-5 xl:grid-cols-5">
        <div className="space-y-3 xl:col-span-2">
          <Input placeholder="Cari nama atau SKU…" value={search} onChange={(event) => setSearch(event.target.value)} aria-label="Cari menu" />
          <FilterTabs variant="solid" options={[{ value: 'semua', label: 'Semua' }, { value: 'tersedia', label: 'Tersedia' }, { value: 'habis', label: 'Habis' }]} value={filter} onChange={setFilter} />
          <AsyncView res={menus} emptyTitle="Menu tidak ditemukan">
            {() => <ul className="space-y-2">{rows.map((menu) => (
              <li key={menu.id} data-testid={`menu-row-${menu.id}`}>
                <div className={cn('flex items-center gap-3 rounded-2xl border bg-white p-3', selected?.id === menu.id ? 'border-accent ring-2 ring-accent/30' : 'border-black/5')}>
                  {menu.image_url ? <img src={menu.image_url} alt="" className="h-12 w-12 rounded-xl object-cover" /> : <div className="grid h-12 w-12 place-items-center rounded-xl bg-cream text-[10px] text-brand/50" aria-hidden>Exito</div>}
                  <button className="min-w-0 flex-1 text-left" onClick={() => setSelected(menu)}>
                    <p className="truncate font-medium text-brand-ink">{menu.name}</p>
                    <p className="text-xs text-brand/70">{menu.sku} · {menu.category.name} · {rupiah(menu.price)}</p>
                    {!menu.is_orderable && <Badge tone="bad" className="mt-1">{menu.unavailable_reason}</Badge>}
                  </button>
                  <Badge tone={menu.is_available ? 'ok' : 'bad'}>{menu.is_available ? 'Tersedia' : 'Habis'}</Badge>
                </div>
              </li>
            ))}</ul>}
          </AsyncView>
        </div>
        <div className="xl:col-span-3">
          {selected === undefined ? <Card className="grid place-items-center p-10 text-center text-sm text-brand/70">Pilih menu di kiri untuk mengubah detail atau tambah menu baru.</Card>
            : <MenuEditor menu={selected} categories={cats.data || []} onSaved={() => { menus.reload(); setSelected(undefined) }} />}
        </div>
      </div>
    </>
  )
}
