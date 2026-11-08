import { useState } from 'react'
import { usersApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { Badge } from '../../components/ui/Badge'
import Modal from '../../components/ui/Modal'
import { Field, Input, Select, Toggle } from '../../components/ui/Form'
import FilterTabs from '../../components/ui/FilterTabs'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'

const ROLES = [['kasir', 'Kasir'], ['barista', 'Barista'], ['kitchen', 'Kitchen'], ['admin', 'Manager']]
const PERM_LABEL = { 'queue.barista': 'Antrean Barista', 'queue.kitchen': 'Antrean Kitchen', 'purchases.receive': 'Terima barang', 'ingredients.view': 'Lihat bahan', 'stock.view': 'Lihat riwayat stok', 'menus.view': 'Lihat menu' }

function UserForm({ open, onClose, onDone }) {
  const [f, setF] = useState({ name: '', username: '', email: '', role: 'kasir', password: '' })
  const [errors, setErrors] = useState(null)
  const { busy, run } = useAction()
  const save = async () => {
    setErrors(null)
    try { const out = await usersApi.create(f); if (out) { onDone(); setF({ name: '', username: '', email: '', role: 'kasir', password: '' }) } } catch (e) { setErrors(e.errors || { username: [e.message] }) }
  }
  void run; void busy
  return (
    <Modal open={open} title="Tambah pengguna" onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" disabled={!f.name || !f.username || !f.email || f.password.length < 8} onClick={save}>Simpan</Button></>}>
      <div className="space-y-4">
        <Field label="Nama lengkap" error={errors?.name}><Input value={f.name} onChange={(e) => setF({ ...f, name: e.target.value })} /></Field>
        <Field label="Username" error={errors?.username}><Input value={f.username} onChange={(e) => setF({ ...f, username: e.target.value.toLowerCase() })} /></Field>
        <Field label="Email" error={errors?.email}><Input type="email" value={f.email} onChange={(e) => setF({ ...f, email: e.target.value })} /></Field>
        <Field label="Role"><Select value={f.role} onChange={(e) => setF({ ...f, role: e.target.value })}>{ROLES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}</Select></Field>
        <Field label="Kata sandi awal" hint="Minimal 8 karakter"><Input type="password" value={f.password} onChange={(e) => setF({ ...f, password: e.target.value })} /></Field>
      </div>
    </Modal>
  )
}

function PermissionsModal({ user, onClose, onDone }) {
  const res = useResource(() => usersApi.permissions(user.id), [user.id])
  const [picked, setPicked] = useState(null)
  const { busy, run } = useAction()
  const current = picked ?? res.data?.granted ?? []
  const toggle = (p) => setPicked(current.includes(p) ? current.filter((x) => x !== p) : [...current, p])
  const save = async () => { const out = await run(() => usersApi.savePermissions(user.id, current), 'Izin khusus disimpan'); if (out) onDone() }
  return (
    <Modal open title={`Izin khusus: ${user.name}`} onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} onClick={save}>Simpan izin</Button></>}>
      <AsyncView res={res} empty={() => false}>
        {(d) => (
          <>
            <p className="mb-3 text-sm text-brand/70">Izin tambahan di luar bawaan role {user.role.label}. Setiap perubahan dicatat. Hanya izin tertentu yang dapat diberikan.</p>
            <ul className="space-y-2">{d.grantable.map((p) => <li key={p}><label className="flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 ring-1 ring-black/5"><input type="checkbox" checked={current.includes(p)} disabled={d.defaults.includes(p)} onChange={() => toggle(p)} /><span className="text-sm">{PERM_LABEL[p] || p}</span>{d.defaults.includes(p) && <Badge className="ml-auto">Bawaan role</Badge>}</label></li>)}</ul>
          </>
        )}
      </AsyncView>
    </Modal>
  )
}

/** Pengguna & hak akses + meja/QR. TANPA layar Figma (ASUMSI). Akun Owner tidak dapat diubah dari sini. */
export default function UsersPage() {
  const [tab, setTab] = useState('users')
  const [add, setAdd] = useState(false)
  const [perm, setPerm] = useState(null)
  const users = useResource(() => usersApi.list(), [])
  const tables = useResource(() => usersApi.tables(), [])
  const { run } = useAction()
  const toast = (m) => navigator.clipboard?.writeText(m)

  const userCols = [
    { key: 'name', header: 'Nama', render: (u) => <div><p className="font-medium">{u.name}</p><p className="text-xs text-brand/60">{u.username} · {u.email}</p></div> },
    { key: 'role', header: 'Role', render: (u) => <Badge tone="info">{u.role.label}</Badge> },
    { key: 'active', header: 'Aktif', render: (u) => <Toggle checked={u.is_active} disabled={u.role.code === 'owner'} label={`Aktif ${u.name}`} onChange={async (v) => { await run(() => usersApi.setStatus(u.id, v)); users.reload() }} /> },
    { key: 'perm', header: '', render: (u) => u.role.code !== 'owner' && <Button size="sm" variant="outline" onClick={() => setPerm(u)}>Izin khusus</Button> },
  ]
  const tableCols = [
    { key: 'n', header: 'Meja', render: (t) => <span className="font-medium">Meja {t.table_number}</span> },
    { key: 'url', header: 'Tautan QR', render: (t) => <code className="text-xs break-all">{t.qr_url}</code> },
    { key: 'a', header: '', render: (t) => <div className="flex gap-1.5"><Button size="sm" variant="outline" onClick={() => toast(t.qr_url)}>Salin tautan</Button><Button size="sm" variant="outline" onClick={async () => { await run(() => usersApi.regenerateQr(t.id), 'QR dibuat ulang; QR lama tidak berlaku'); tables.reload() }}>Buat ulang</Button></div> },
  ]
  return (
    <>
      <PageHeader title="Pengguna & Meja" subtitle="Kelola akun staf, izin khusus, dan tautan QR setiap meja."
        actions={tab === 'users' ? <Button variant="accent" onClick={() => setAdd(true)}>+ Tambah pengguna</Button> : <Button variant="accent" onClick={async () => { const n = prompt('Nomor meja baru?'); if (n) { await run(() => usersApi.createTable({ table_number: n }), 'Meja ditambahkan'); tables.reload() } }}>+ Tambah meja</Button>} />
      <DevNote>Layar pengguna, izin, dan meja/QR belum ada di Figma. QR dicetak dari tautan di sini (gambar QR dirender frontend; K-T2/K-T4 pending).</DevNote>
      <FilterTabs variant="solid" className="mb-5" options={[{ value: 'users', label: 'Pengguna' }, { value: 'tables', label: 'Meja & QR' }]} value={tab} onChange={setTab} />
      <Card className="p-5">{tab === 'users' ? <AsyncView res={users}>{(rows) => <DataTable columns={userCols} rows={rows} />}</AsyncView> : <AsyncView res={tables}>{(rows) => <DataTable columns={tableCols} rows={rows} />}</AsyncView>}</Card>
      <UserForm open={add} onClose={() => setAdd(false)} onDone={() => { setAdd(false); users.reload() }} />
      {perm && <PermissionsModal user={perm} onClose={() => setPerm(null)} onDone={() => { setPerm(null); users.reload() }} />}
    </>
  )
}
