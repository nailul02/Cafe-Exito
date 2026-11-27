import { menusApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import Button from '../ui/Button'
import { Card } from '../ui/Card'
import { rupiah } from '../../lib/format'

export default function MenuAvailabilityPanel({ title = 'Ketersediaan Menu' }) {
  const res = useResource(() => menusApi.list(), [], { poll: true, interval: 10000 })
  const { busy, run } = useAction()
  const menus = res.data || []

  return <section className="mt-6" aria-label={title}>
    <h2 className="mb-3 text-lg font-semibold text-brand-ink">{title}</h2>
    {res.error ? <p role="alert" className="text-sm text-bad">Daftar menu tidak dapat dimuat.</p> : null}
    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      {menus.map((menu) => <Card key={menu.id} className="flex items-center justify-between gap-3 p-4">
        <div className="min-w-0"><p className="truncate font-medium">{menu.name}</p><p className="text-sm text-brand/70">{rupiah(menu.price)} · {menu.category.name}</p></div>
        <Button size="sm" variant={menu.is_available ? 'primary' : 'outline'} loading={busy}
          aria-label={`${menu.is_available ? 'Tandai habis' : 'Tandai tersedia'}: ${menu.name}`}
          onClick={async () => { const saved = await run(() => menusApi.setAvailability(menu.id, !menu.is_available), 'Ketersediaan menu diperbarui'); if (saved) res.refresh() }}>
          {menu.is_available ? 'Tersedia' : 'Habis'}
        </Button>
      </Card>)}
    </div>
  </section>
}
