import { useState } from 'react'
import { inventoryApi, purchasingApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card, StatCard } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { Badge } from '../../components/ui/Badge'
import Progress from '../../components/ui/Progress'
import Modal from '../../components/ui/Modal'
import { Field, Input, Select, Textarea } from '../../components/ui/Form'
import FilterTabs from '../../components/ui/FilterTabs'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'
import { STOCK_LEVEL } from '../../lib/status'
import { dateTime, qty } from '../../lib/format'

function AdjustModal({ ingredient, onClose, onDone }) {
  const [change, setChange] = useState('')
  const [note, setNote] = useState('')
  const { busy, run } = useAction()
  const save = async () => { const out = await run(() => inventoryApi.adjust({ ingredient_id: ingredient.id, quantity_change: Number(change), note }), 'Stok dikoreksi'); if (out) onDone() }
  return (
    <Modal open={Boolean(ingredient)} title={`Sesuaikan stok: ${ingredient?.name}`} onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} disabled={!change || !note} onClick={save}>Simpan koreksi</Button></>}>
      <p className="mb-3 text-sm text-brand/70">Stok tercatat saat ini: <b>{qty(ingredient?.stock)} {ingredient?.unit}</b>. Isi selisih hasil stock opname (negatif untuk mengurangi).</p>
      <div className="space-y-4">
        <Field label={`Selisih (${ingredient?.unit})`}><Input inputMode="decimal" value={change} onChange={(e) => setChange(e.target.value.replace(/[^0-9.-]/g, ''))} placeholder="-250 atau 500" /></Field>
        <Field label="Catatan (wajib)"><Textarea value={note} onChange={(e) => setNote(e.target.value)} placeholder="Contoh: hasil opname akhir pekan" /></Field>
      </div>
    </Modal>
  )
}

/** Penerimaan barang berdasarkan PO (K-B10: rekomendasi (a), stok masuk hanya lewat penerimaan yang dikonfirmasi). */
function ReceivePanel({ onDone }) {
  const pos = useResource(() => purchasingApi.purchases(), [])
  const [poId, setPoId] = useState('')
  const [qtys, setQtys] = useState({})
  const [note, setNote] = useState('')
  const { busy, run } = useAction()
  const open = (pos.data || []).filter((p) => ['dipesan', 'diterima_sebagian'].includes(p.status.code))
  const po = open.find((p) => String(p.id) === String(poId))

  const submit = async () => {
    const details = po.details.filter((d) => Number(qtys[d.id]) > 0).map((d) => ({ purchase_detail_id: d.id, qty_received: Number(qtys[d.id]) }))
    const created = await run(() => purchasingApi.createReceipt(po.id, { details, note }))
    if (!created) return
    const receipt = created.receipts.slice(-1)[0]
    const out = await run(() => purchasingApi.confirmReceipt(receipt.id), 'Barang diterima, stok bertambah')
    if (out) { setQtys({}); setNote(''); setPoId(''); pos.reload(); onDone() }
  }
  return (
    <Card className="p-5">
      <h2 className="font-ui font-semibold text-brand-ink">Terima barang dari supplier</h2>
      <p className="mb-3 text-xs text-brand/60">Stok bertambah hanya setelah penerimaan dikonfirmasi.</p>
      <div className="space-y-3">
        <Field label="Pembelian (PO)"><Select value={poId} onChange={(e) => { setPoId(e.target.value); setQtys({}) }}><option value="">{open.length ? 'Pilih PO…' : 'Tidak ada PO yang menunggu'}</option>{open.map((p) => <option key={p.id} value={p.id}>{p.purchase_number} · {p.supplier.name}</option>)}</Select></Field>
        {po && po.details.map((d) => (
          <Field key={d.id} label={`${d.ingredient.name} (sisa ${qty(d.qty_outstanding)} ${d.ingredient.unit})`}><Input inputMode="decimal" value={qtys[d.id] || ''} onChange={(e) => setQtys({ ...qtys, [d.id]: e.target.value.replace(/[^0-9.]/g, '') })} placeholder="Jumlah diterima" /></Field>
        ))}
        {po && <Field label="Catatan / no. faktur pengiriman"><Input value={note} onChange={(e) => setNote(e.target.value)} /></Field>}
        <Button variant="dark" block loading={busy} disabled={!po || !Object.values(qtys).some((v) => Number(v) > 0)} onClick={submit}>Simpan & konfirmasi penerimaan</Button>
      </div>
    </Card>
  )
}

/** MG-03: Kelola Stok Bahan & Inventaris. */
export default function InventoryPage() {
  const [category, setCategory] = useState('semua')
  const [search, setSearch] = useState('')
  const [onlyLow, setOnlyLow] = useState(false)
  const [adjust, setAdjust] = useState(null)
  const summary = useResource(() => inventoryApi.summary(), [], { poll: true, interval: 15000 })
  const list = useResource(() => inventoryApi.ingredients({ search, status: onlyLow ? 'low' : undefined, category: category === 'semua' ? undefined : category }), [search, onlyLow, category], { poll: true, interval: 15000 })
  const moves = useResource(() => inventoryApi.movements({ type: 'penerimaan_pembelian' }), [])
  const s = summary.data
  const refresh = () => { summary.reload(); list.reload(); moves.reload() }

  return (
    <>
      <PageHeader title="Kelola Stok Bahan & Inventaris" subtitle="Pantau sisa persediaan, buffer aman, dan riwayat penambahan stok." />
      <DevNote>EXP date, nomor batch, dan foto faktur (Figma) belum dibangun (K-B10). Stok masuk hanya lewat penerimaan PO.</DevNote>
      <div className="mb-5 grid gap-3 sm:grid-cols-3">
        <StatCard label="Total bahan" value={s?.total_items ?? '–'} />
        <StatCard label="Stok kritis & habis" value={s?.critical_count ?? '–'} tone="bad" hint="Perlu restok segera" />
        <StatCard label="Stok masuk hari ini" value={s?.incoming_today?.length ? s.incoming_today.map((x) => `${qty(x.total)} ${x.unit}`).join(' · ') : '–'} />
      </div>
      <div className="grid gap-5 xl:grid-cols-3">
        <Card className="p-5 xl:col-span-2">
          <div className="mb-3 flex flex-wrap items-center gap-3">
            <Input className="max-w-60" placeholder="Cari bahan baku…" value={search} onChange={(e) => setSearch(e.target.value)} aria-label="Cari bahan" />
            <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={onlyLow} onChange={(e) => setOnlyLow(e.target.checked)} />Hanya stok rendah</label>
          </div>
          <FilterTabs variant="solid" className="mb-4" value={category} onChange={setCategory} options={[{ value: 'semua', label: 'Semua' }, ...(s?.categories || []).map((c) => ({ value: c.name, label: c.name, count: c.count }))]} />
          <AsyncView res={list} emptyTitle="Bahan tidak ditemukan">
            {(rows) => (
              <ul className="divide-y divide-black/5">{rows.map((i) => (
                <li key={i.id} className="grid grid-cols-[1fr_auto] items-center gap-3 py-3 sm:grid-cols-[1.4fr_1fr_auto]">
                  <div><p className="font-medium text-brand-ink">{i.name}</p><p className="text-xs text-brand/60">{i.category}</p></div>
                  <div><div className="mb-1 flex items-center gap-2 text-sm"><Badge tone={STOCK_LEVEL[i.level.code].tone}>{STOCK_LEVEL[i.level.code].label} · {i.stock_percent}%</Badge></div><Progress percent={i.stock_percent} tone={i.level.code === 'aman' ? 'ok' : i.level.code === 'menipis' ? 'warn' : 'bad'} /><p className="mt-1 text-xs text-brand/60">Sisa <b>{qty(i.stock)} {i.unit}</b> · minimum {qty(i.min_stock)}</p></div>
                  <Button size="sm" variant="outline" onClick={() => setAdjust(i)}>Sesuaikan</Button>
                </li>
              ))}</ul>
            )}
          </AsyncView>
        </Card>
        <div className="space-y-5">
          <ReceivePanel onDone={refresh} />
          <Card className="p-5">
            <h2 className="mb-3 font-ui font-semibold text-brand-ink">Riwayat stok masuk terakhir</h2>
            <AsyncView res={moves} emptyTitle="Belum ada stok masuk" emptyHint="Riwayat muncul setelah penerimaan barang dikonfirmasi.">
              {(rows) => <ul className="space-y-3">{rows.slice(0, 6).map((m) => <li key={m.id} className="flex items-start justify-between gap-2 text-sm"><div><p className="font-medium">{m.ingredient.name}</p><p className="text-xs text-brand/60">{m.reference} · {m.user}</p></div><div className="text-right"><p className="font-semibold text-go-deep">+{qty(m.quantity_change)} {m.ingredient.unit}</p><p className="text-xs text-brand/60">{dateTime(m.created_at)}</p></div></li>)}</ul>}
            </AsyncView>
          </Card>
        </div>
      </div>
      {adjust && <AdjustModal ingredient={adjust} onClose={() => setAdjust(null)} onDone={() => { setAdjust(null); refresh() }} />}
    </>
  )
}
