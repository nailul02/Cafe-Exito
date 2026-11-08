import { useState } from 'react'
import { inventoryApi, purchasingApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { StatusPill } from '../../components/ui/Badge'
import Modal from '../../components/ui/Modal'
import { Field, Input, Select } from '../../components/ui/Form'
import FilterTabs from '../../components/ui/FilterTabs'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'
import { PURCHASE_STATUS } from '../../lib/status'
import { dateTime, qty, rupiah } from '../../lib/format'

function PurchaseForm({ open, onClose, onDone }) {
  const sup = useResource(() => purchasingApi.suppliers(), [open], { enabled: open })
  const ing = useResource(() => inventoryApi.ingredients({}), [open], { enabled: open })
  const [supplierId, setSupplierId] = useState('')
  const [lines, setLines] = useState([{ ingredient_id: '', qty_ordered: '', unit_price: '' }])
  const { busy, run } = useAction()
  const valid = supplierId && lines.some((l) => l.ingredient_id && Number(l.qty_ordered) > 0)
  const upd = (i, k, v) => setLines(lines.map((l, idx) => (idx === i ? { ...l, [k]: v } : l)))
  const save = async () => {
    const out = await run(() => purchasingApi.createPurchase({ supplier_id: Number(supplierId), details: lines.filter((l) => l.ingredient_id && Number(l.qty_ordered) > 0) }), 'Draft pembelian dibuat')
    if (out) { setSupplierId(''); setLines([{ ingredient_id: '', qty_ordered: '', unit_price: '' }]); onDone() }
  }
  return (
    <Modal open={open} size="lg" title="Buat pembelian baru" onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} disabled={!valid} onClick={save}>Simpan draft</Button></>}>
      <div className="space-y-4">
        <Field label="Supplier"><Select value={supplierId} onChange={(e) => setSupplierId(e.target.value)}><option value="">Pilih supplier…</option>{(sup.data || []).map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}</Select></Field>
        <div className="space-y-2">
          {lines.map((l, i) => (
            <div key={i} className="grid grid-cols-[1.6fr_1fr_1fr_auto] items-end gap-2">
              <Field label={i === 0 ? 'Bahan' : undefined}><Select value={l.ingredient_id} onChange={(e) => upd(i, 'ingredient_id', e.target.value)}><option value="">Pilih bahan…</option>{(ing.data || []).map((x) => <option key={x.id} value={x.id}>{x.name} ({x.unit})</option>)}</Select></Field>
              <Field label={i === 0 ? 'Jumlah' : undefined}><Input inputMode="decimal" value={l.qty_ordered} onChange={(e) => upd(i, 'qty_ordered', e.target.value.replace(/[^0-9.]/g, ''))} /></Field>
              <Field label={i === 0 ? 'Harga satuan' : undefined}><Input inputMode="numeric" value={l.unit_price} onChange={(e) => upd(i, 'unit_price', e.target.value.replace(/\D/g, ''))} /></Field>
              <button aria-label="Hapus baris" disabled={lines.length === 1} onClick={() => setLines(lines.filter((_, x) => x !== i))} className="mb-1 rounded-lg p-2 text-bad hover:bg-bad-soft disabled:opacity-30">✕</button>
            </div>
          ))}
          <Button size="sm" variant="outline" onClick={() => setLines([...lines, { ingredient_id: '', qty_ordered: '', unit_price: '' }])}>+ Tambah baris</Button>
        </div>
        <p className="text-xs text-brand/60">Harga satuan hanya pencatatan pembelian, bukan akuntansi.</p>
      </div>
    </Modal>
  )
}

function SupplierForm({ open, onClose, onDone }) {
  const [f, setF] = useState({ name: '', phone: '', address: '' })
  const { busy, run } = useAction()
  const save = async () => { const out = await run(() => purchasingApi.createSupplier(f), 'Supplier ditambahkan'); if (out) { setF({ name: '', phone: '', address: '' }); onDone() } }
  return (
    <Modal open={open} title="Tambah supplier" onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} disabled={!f.name} onClick={save}>Simpan</Button></>}>
      <div className="space-y-4"><Field label="Nama supplier"><Input value={f.name} onChange={(e) => setF({ ...f, name: e.target.value })} /></Field><Field label="Telepon"><Input value={f.phone} onChange={(e) => setF({ ...f, phone: e.target.value })} /></Field><Field label="Alamat"><Input value={f.address} onChange={(e) => setF({ ...f, address: e.target.value })} /></Field></div>
    </Modal>
  )
}

/** Modul Pembelian (supplier + PO + riwayat). TANPA layar Figma: desain mengikuti design system Manager (ASUMSI). */
export default function PurchasingPage() {
  const [tab, setTab] = useState('po')
  const [showPo, setShowPo] = useState(false)
  const [showSup, setShowSup] = useState(false)
  const pos = useResource(() => purchasingApi.purchases(), [])
  const sup = useResource(() => purchasingApi.suppliers(), [])
  const { busy, run } = useAction()
  const act = async (fn, msg) => { await run(fn, msg); pos.reload() }

  const poCols = [
    { key: 'no', header: 'No. PO', render: (p) => <span className="font-medium">{p.purchase_number}</span> },
    { key: 'sup', header: 'Supplier', render: (p) => p.supplier.name },
    { key: 'items', header: 'Rincian', render: (p) => p.details.map((d) => `${d.ingredient.name} ${qty(d.qty_received)}/${qty(d.qty_ordered)} ${d.ingredient.unit}`).join(', ') },
    { key: 'total', header: 'Total', align: 'right', render: (p) => rupiah(p.total) },
    { key: 'st', header: 'Status', render: (p) => <StatusPill map={PURCHASE_STATUS} code={p.status.code} /> },
    { key: 'date', header: 'Dipesan', render: (p) => dateTime(p.ordered_at) },
    { key: 'act', header: '', render: (p) => (
      <div className="flex gap-1.5">
        {p.status.code === 'draft' && <Button size="sm" variant="dark" loading={busy} onClick={() => act(() => purchasingApi.placePurchase(p.id), 'PO ditandai dipesan')}>Pesan</Button>}
        {['draft', 'dipesan'].includes(p.status.code) && <Button size="sm" variant="outline" loading={busy} onClick={() => act(() => purchasingApi.cancelPurchase(p.id), 'PO dibatalkan')}>Batalkan</Button>}
      </div>) },
  ]
  return (
    <>
      <PageHeader title="Pembelian Bahan Baku" subtitle="Catat supplier dan pembelian. Stok baru bertambah setelah barang diterima dan dikonfirmasi di Kelola Stok."
        actions={tab === 'po' ? <Button variant="accent" onClick={() => setShowPo(true)}>+ Buat pembelian</Button> : <Button variant="accent" onClick={() => setShowSup(true)}>+ Tambah supplier</Button>} />
      <DevNote>Layar supplier dan pembelian belum ada di Figma; dibuat dengan gaya desain Manager. Hak Owner membuat PO menunggu K-B11.</DevNote>
      <FilterTabs variant="solid" className="mb-5" options={[{ value: 'po', label: 'Pembelian' }, { value: 'sup', label: 'Supplier' }]} value={tab} onChange={setTab} />
      <Card className="p-5">
        {tab === 'po'
          ? <AsyncView res={pos} emptyTitle="Belum ada pembelian" emptyHint="Buat pembelian pertama untuk bahan yang hampir habis.">{(rows) => <DataTable columns={poCols} rows={rows} />}</AsyncView>
          : <AsyncView res={sup} emptyTitle="Belum ada supplier">{(rows) => <DataTable columns={[{ key: 'name', header: 'Nama' }, { key: 'phone', header: 'Telepon' }, { key: 'address', header: 'Alamat' }, { key: 'x', header: '', render: (s) => <Button size="sm" variant="outline" onClick={async () => { await run(() => purchasingApi.removeSupplier(s.id), 'Supplier dinonaktifkan'); sup.reload() }}>Nonaktifkan</Button> }]} rows={rows} />}</AsyncView>}
      </Card>
      <PurchaseForm open={showPo} onClose={() => setShowPo(false)} onDone={() => { setShowPo(false); pos.reload() }} />
      <SupplierForm open={showSup} onClose={() => setShowSup(false)} onDone={() => { setShowSup(false); sup.reload() }} />
    </>
  )
}
