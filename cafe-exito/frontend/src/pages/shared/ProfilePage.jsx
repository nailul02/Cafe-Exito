import { useState } from 'react'
import { authApi } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useAction } from '../../hooks/useAction'
import Button from '../../components/ui/Button'
import { Card } from '../../components/ui/Card'
import Modal from '../../components/ui/Modal'
import { Field, Input } from '../../components/ui/Form'
import PageHeader from '../../components/ui/PageHeader'
import { cn } from '../../lib/cn'

/** Profil (Figma KS-05 / BR-04): nama, username, email, jabatan + Edit Profil. Foto profil: PENDING. */
export default function ProfilePage({ surface = 'brown' }) {
  const { user, setUser } = useAuth()
  const [edit, setEdit] = useState(false)
  const [form, setForm] = useState({ name: user.name, email: user.email })
  const { busy, run } = useAction()
  const brown = surface === 'brown'

  const rows = [['Nama', user.name], ['Username', user.username], ['Email', user.email], ['Jabatan', user.role.label]]
  const save = async () => {
    const u = await run(() => authApi.updateProfile(form), 'Profil diperbarui')
    if (u) { setUser(u); setEdit(false) }
  }
  return (
    <>
      <PageHeader title="Profil" />
      <Card surface={brown ? 'brown' : 'white'} className="mx-auto max-w-2xl p-8 text-center">
        <div className={cn('mx-auto grid h-28 w-28 place-items-center rounded-full text-4xl font-semibold', brown ? 'bg-[#d9d9d9] text-brand' : 'bg-cream text-brand-ink')} aria-hidden>{user.name.slice(0, 1)}</div>
        <dl className="mt-6 space-y-4">
          {rows.map(([k, v]) => (<div key={k}><dt className={cn('text-sm', brown ? 'text-white/75' : 'text-brand/70')}>{k}</dt><dd className="text-lg font-medium">{v}</dd></div>))}
        </dl>
        <Button variant={brown ? 'sand' : 'dark'} className="mt-7" onClick={() => setEdit(true)}>Edit profil</Button>
      </Card>
      <Modal open={edit} title="Edit profil" onClose={() => setEdit(false)} footer={<><Button variant="outline" onClick={() => setEdit(false)}>Batal</Button><Button loading={busy} onClick={save}>Simpan perubahan</Button></>}>
        <div className="space-y-4">
          <Field label="Nama"><Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} /></Field>
          <Field label="Email"><Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /></Field>
        </div>
      </Modal>
    </>
  )
}
