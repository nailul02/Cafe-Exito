import { useEffect, useState } from 'react'
import { usersApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { Badge } from '../../components/ui/Badge'
import Modal from '../../components/ui/Modal'
import { Field, Input, Select, Toggle } from '../../components/ui/Form'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'

const ROLES = [['manager', 'Manager'], ['kasir', 'Kasir'], ['barista_kitchen', 'Barista/Kitchen']]
const BLANK = { name: '', username: '', email: '', role: 'kasir', station: '', password: '' }

function EmployeeForm({ user, onClose, onDone }) {
  const isEdit = Boolean(user)
  const [form, setForm] = useState(BLANK)
  const [errors, setErrors] = useState({})
  const { busy, run } = useAction()

  useEffect(() => {
    setForm(user ? { name: user.name, username: user.username, email: user.email, role: user.role.code, station: user.station || '', password: '' } : BLANK)
    setErrors({})
  }, [user])

  const change = (key, value) => setForm((current) => ({ ...current, [key]: value, ...(key === 'role' && value !== 'barista_kitchen' ? { station: '' } : {}) }))
  const save = async () => {
    setErrors({})
    const payload = { name: form.name.trim(), username: form.username.trim().toLowerCase(), email: form.email.trim(), role: form.role }
    if (form.role === 'barista_kitchen') payload.station = form.station
    if (!isEdit) payload.password = form.password
    try {
      const result = await run(() => isEdit ? usersApi.update(user.id, payload) : usersApi.create(payload), isEdit ? 'Akun diperbarui' : 'Akun karyawan dibuat')
      if (result) onDone()
    } catch (error) {
      setErrors(error.errors || { form: [error.message || 'Data tidak dapat disimpan.'] })
    }
  }

  return (
    <Modal open title={isEdit ? `Edit akun: ${user.name}` : 'Tambah akun karyawan'} onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} disabled={!form.name.trim() || !form.username.trim() || !form.email.trim() || (!isEdit && form.password.length < 8) || (form.role === 'barista_kitchen' && !form.station)} onClick={save}>Simpan</Button></>}>
      <div className="space-y-4">
        {errors.form && <p role="alert" className="text-sm text-bad">{errors.form.join(' ')}</p>}
        <Field label="Nama lengkap" error={errors.name?.[0]}><Input autoComplete="name" value={form.name} onChange={(event) => change('name', event.target.value)} /></Field>
        <Field label="Username" error={errors.username?.[0]}><Input autoComplete="username" value={form.username} onChange={(event) => change('username', event.target.value.toLowerCase())} /></Field>
        <Field label="Email" error={errors.email?.[0]}><Input type="email" autoComplete="email" value={form.email} onChange={(event) => change('email', event.target.value)} /></Field>
        <Field label="Role" error={errors.role?.[0]}><Select value={form.role} onChange={(event) => change('role', event.target.value)}>{ROLES.map(([value, label]) => <option key={value} value={value}>{label}</option>)}</Select></Field>
        {form.role === 'barista_kitchen' && <Field label="Station" error={errors.station?.[0]}><Select value={form.station} onChange={(event) => change('station', event.target.value)}><option value="">Pilih station</option><option value="barista">Barista</option><option value="kitchen">Kitchen</option></Select></Field>}
        {!isEdit && <Field label="Kata sandi awal" hint="Minimal 8 karakter. Kata sandi tidak ditampilkan kembali." error={errors.password?.[0]}><Input type="password" autoComplete="new-password" value={form.password} onChange={(event) => change('password', event.target.value)} /></Field>}
      </div>
    </Modal>
  )
}

function PasswordReset({ user, onClose, onDone }) {
  const [password, setPassword] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [error, setError] = useState('')
  const { busy, run } = useAction()
  const submit = async () => {
    setError('')
    if (password.length < 8) { setError('Kata sandi harus memiliki minimal 8 karakter.'); return }
    if (password !== confirmation) { setError('Konfirmasi kata sandi tidak cocok.'); return }
    const result = await run(() => usersApi.resetPassword(user.id, password), 'Kata sandi berhasil diatur ulang')
    if (result !== undefined) onDone()
  }
  return (
    <Modal open title={`Reset kata sandi: ${user.name}`} onClose={onClose} footer={<><Button variant="outline" onClick={onClose}>Batal</Button><Button variant="dark" loading={busy} disabled={password.length < 8 || password !== confirmation} onClick={submit}>Reset kata sandi</Button></>}>
      <div className="space-y-4">
        <p className="text-sm text-brand/70">Masukkan kata sandi baru. Sesi login aktif karyawan akan dicabut setelah reset.</p>
        {error && <p role="alert" className="text-sm text-bad">{error}</p>}
        <Field label="Kata sandi baru"><Input type="password" autoComplete="new-password" value={password} onChange={(event) => setPassword(event.target.value)} /></Field>
        <Field label="Ulangi kata sandi baru"><Input type="password" autoComplete="new-password" value={confirmation} onChange={(event) => setConfirmation(event.target.value)} /></Field>
      </div>
    </Modal>
  )
}

export default function EmployeeAccountsPage() {
  const employees = useResource(() => usersApi.list(), [])
  const [editing, setEditing] = useState(null)
  const [creating, setCreating] = useState(false)
  const [resetting, setResetting] = useState(null)
  const { run } = useAction()

  const columns = [
    { key: 'name', header: 'Karyawan', render: (user) => <div><p className="font-medium">{user.name}</p><p className="text-xs text-brand/60">{user.username} · {user.email}</p></div> },
    { key: 'role', header: 'Role / station', render: (user) => <Badge tone="info">{user.role.label}{user.station ? ` · ${user.station}` : ''}</Badge> },
    { key: 'active', header: 'Status', render: (user) => <Toggle checked={user.is_active} disabled={user.role.code === 'owner'} label={`Aktif ${user.name}`} onChange={async (active) => { const result = await run(() => usersApi.setStatus(user.id, active), active ? 'Akun diaktifkan' : 'Akun dinonaktifkan'); if (result) employees.reload() }} /> },
    { key: 'actions', header: 'Tindakan', render: (user) => user.role.code === 'owner' ? <span className="text-xs text-brand/60">Akun Owner dilindungi</span> : <div className="flex flex-wrap gap-2"><Button size="sm" variant="outline" onClick={() => setEditing(user)}>Edit</Button><Button size="sm" variant="outline" onClick={() => setResetting(user)}>Reset kata sandi</Button></div> },
  ]

  return (
    <>
      <PageHeader title="Akun Karyawan" subtitle="Owner mengelola akun Manager, Kasir, dan Barista/Kitchen." actions={<Button variant="accent" onClick={() => setCreating(true)}>+ Tambah karyawan</Button>} />
      <DevNote>Akun Owner tidak dapat dibuat, diedit, dinonaktifkan, atau direset dari halaman ini. Akun staf dinonaktifkan, bukan dihapus, agar riwayat tetap utuh.</DevNote>
      <Card className="mt-5 p-5"><AsyncView res={employees}>{(rows) => <DataTable columns={columns} rows={rows} />}</AsyncView></Card>
      {creating && <EmployeeForm onClose={() => setCreating(false)} onDone={() => { setCreating(false); employees.reload() }} />}
      {editing && <EmployeeForm user={editing} onClose={() => setEditing(null)} onDone={() => { setEditing(null); employees.reload() }} />}
      {resetting && <PasswordReset user={resetting} onClose={() => setResetting(null)} onDone={() => { setResetting(null); employees.reload() }} />}
    </>
  )
}
