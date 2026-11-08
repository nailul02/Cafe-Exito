import { useEffect, useState } from 'react'
import { inventoryApi, menusApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card, StatCard } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { Badge } from '../../components/ui/Badge'
import { Field, Input, Select, Toggle } from '../../components/ui/Form'
import FilterTabs from '../../components/ui/FilterTabs'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'
import { rupiah } from '../../lib/format'
import { cn } from '../../lib/cn'

const EMPTY = { name: '', sku: '', price: '', category_id: '', cost_price: '' }

function MenuEditor({ menu, categories, ingredients, onSaved }) {
  const [form, setForm] = useState(EMPTY)
  const [recipe, setRecipe] = useState([])
  const [errors, setErrors] = useState(null)
  const { busy, run } = useAction()

  useEffect(() => {
    setErrors(null)
    if (!menu) { setForm({ ...EMPTY, category_id: categories[0]?.id || '' }); setRecipe([]); return }
    setForm({ name: menu.name, sku: menu.sku || '', price: menu.price, category_id: menu.category.id, cost_price: menu.cost_price ?? '' })
    menusApi.recipe(menu.id).then((r) => setRecipe(r.map((x) => ({ ingredient_id: x.ingredient_id, quantity: x.quantity }))))
  }, [menu, categories])

  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value })
  const save = async () => {
    setErrors(null)
    try {
      const payload = { ...form, price: Number(form.price), category_id: Number(form.category_id), cost_price: form.cost_price === '' ? null : Number(form.cost_price) }
      const saved = await run(() => (menu ? menusApi.update(menu.id, payload) : menusApi.create(payload)), 'Menu disimpan')
      if (saved) { await run(() => menusApi.saveRecipe(saved.id, recipe.filter((r) => r.ingredient_id && Number(r.quantity) > 0))); onSaved(saved) }
    } catch (e) { setErrors(e.errors) }
  }
  const unitOf = (id) => ingredients.find((i) => i.id === Number(id))?.unit

  return (
    <Card className="p-5">
      <h2 className="font-ui text-lg font-semibold text-brand-ink">{menu ? `Edit menu: ${menu.name}` : 'Tambah menu baru'}</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        <Field label="Nama menu" error={errors?.name} className="sm:col-span-2"><Input value={form.name} onChange={set('name')} /></Field>
        <Field label="Kode SKU" hint="Opsional"><Input value={form.sku} onChange={set('sku')} /></Field>
        <Field label="Kategori"><Select value={form.category_id} onChange={set('category_id')}>{categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}</Select></Field>
        <Field label="Harga jual (Rp)" error={errors?.price}><Input inputMode="numeric" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value.replace(/\D/g, '') })} /></Field>
        <Field label="HPP / food cost (Rp)" hint="Opsional, belum dipakai laporan (K-B3)"><Input inputMode="numeric" value={form.cost_price} onChange={(e) => setForm({ ...form, cost_price: e.target.value.replace(/\D/g, '') })} /></Field>
      </div>
      <div className="mt-5 rounded-2xl bg-cream/60 p-4">
        <div className="mb-3 flex items-center justify-between"><h3 className="text-sm font-semibold">Komposisi bahan resep (BOM)</h3><Button size="sm" variant="outline" onClick={() => setRecipe([...recipe, { ingredient_id: '', quantity: '' }])}>+ Bahan</Button></div>
        {recipe.length === 0 && <p className="text-sm text-brand/70">Belum ada resep. Menu tanpa resep tidak mengurangi stok.</p>}
        <ul className="space-y-2">{recipe.map((r, idx) => (
          <li key={idx} className="flex items-center gap-2">
            <Select value={r.ingredient_id} onChange={(e) => setRecipe(recipe.map((x, i) => (i === idx ? { ...x, ingredient_id: e.target.value } : x)))} aria-label="Bahan"><option value="">Pilih bahan…</option>{ingredients.map((i) => <option key={i.id} value={i.id}>{i.name}</option>)}</Select>
            <Input className="w-24" inputMode="decimal" value={r.quantity} onChange={(e) => setRecipe(recipe.map((x, i) => (i === idx ? { ...x, quantity: e.target.value } : x)))} aria-label="Jumlah" />
            <span className="w-8 text-sm text-brand/70">{unitOf(r.ingredient_id)}</span>
            <button aria-label="Hapus bahan" onClick={() => setRecipe(recipe.filter((_, i) => i !== idx))} className="rounded-lg p-2 text-bad hover:bg-bad-soft">✕</button>
          </li>
        ))}</ul>
      </div>
      <div className="mt-5 flex justify-end"><Button variant="dark" loading={busy} onClick={save} disabled={!form.name || !form.price}>Simpan menu</Button></div>
    </Card>
  )
}

/** MG-02: daftar menu + saklar ketersediaan + editor (nama, SKU, kategori, harga, HPP, resep). Foto, PB1, opsi tambahan, dan promo: PENDING (K-B4/K-B5). */
export default function MenusPage() {
  const [search, setSearch] = useState('')
  const [filter, setFilter] = useState('semua')
  const [selected, setSelected] = useState(undefined)
  const menus = useResource(() => menusApi.list({ search }), [search])
  const cats = useResource(() => menusApi.categories(), [])
  const ings = useResource(() => inventoryApi.ingredients({}), [])
  const { run } = useAction()

  const rows = (menus.data || []).filter((m) => filter === 'semua' || (filter === 'tersedia' ? m.is_orderable : !m.is_orderable))
  const toggle = async (m) => { await run(() => menusApi.setAvailability(m.id, !m.is_available)); menus.refresh() }
  const all = menus.data || []

  return (
    <>
      <PageHeader title="Kelola Data & Ketersediaan Menu" subtitle="Atur ketersediaan, harga jual, dan komposisi bahan baku (resep)." actions={<Button variant="accent" onClick={() => setSelected(null)}>+ Tambah menu baru</Button>} />
      <DevNote>Foto menu, pajak PB1, opsi tambahan, promo, dan saklar kanal POS ada di Figma tetapi menunggu keputusan (K-B2, K-B4, K-B5). Ketersediaan dihitung otomatis dari stok (K-T4).</DevNote>
      <div className="mb-5 grid gap-3 sm:grid-cols-3">
        <StatCard label="Menu aktif" value={all.filter((m) => m.is_available).length} />
        <StatCard label="Menu habis / nonaktif" value={all.filter((m) => !m.is_orderable).length} tone="bad" />
        <StatCard label="Tanpa resep" value={all.filter((m) => !m.has_recipe).length} hint="Stok tidak berkurang" />
      </div>
      <div className="grid gap-5 xl:grid-cols-5">
        <div className="space-y-3 xl:col-span-2">
          <Input placeholder="Cari nama atau SKU…" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Cari menu" />
          <FilterTabs variant="solid" options={[{ value: 'semua', label: 'Semua' }, { value: 'tersedia', label: 'Tersedia' }, { value: 'habis', label: 'Habis' }]} value={filter} onChange={setFilter} />
          <AsyncView res={menus} emptyTitle="Menu tidak ditemukan">
            {() => (
              <ul className="space-y-2">{rows.map((m) => (
                <li key={m.id}>
                  <div className={cn('flex items-center gap-3 rounded-2xl border bg-white p-3', selected?.id === m.id ? 'border-accent ring-2 ring-accent/30' : 'border-black/5')}>
                    {m.image_url ? <img src={m.image_url} alt="" className="h-12 w-12 rounded-xl object-cover" /> : <div className="grid h-12 w-12 place-items-center rounded-xl bg-cream text-[10px] text-brand/50" aria-hidden>Exito</div>}
                    <button className="min-w-0 flex-1 text-left" onClick={() => setSelected(m)}>
                      <p className="truncate font-medium text-brand-ink">{m.name}</p>
                      <p className="text-xs text-brand/70">{m.sku} · {m.category.name} · {rupiah(m.price)}</p>
                      {!m.is_orderable && <Badge tone="bad" className="mt-1">{m.unavailable_reason}</Badge>}
                      {m.max_portions != null && m.is_orderable && <p className="text-[11px] text-brand/50">Cukup untuk ±{m.max_portions} porsi</p>}
                    </button>
                    <Toggle checked={m.is_available} onChange={() => toggle(m)} label={`Ketersediaan manual ${m.name}`} />
                  </div>
                </li>
              ))}</ul>
            )}
          </AsyncView>
        </div>
        <div className="xl:col-span-3">
          {selected === undefined ? <Card className="grid place-items-center p-10 text-center text-sm text-brand/70">Pilih menu di kiri untuk mengubah harga dan resep, atau tambah menu baru.</Card>
            : <MenuEditor menu={selected} categories={cats.data || []} ingredients={ings.data || []} onSaved={() => { menus.reload(); setSelected(undefined) }} />}
        </div>
      </div>
    </>
  )
}
