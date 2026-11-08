import { useState } from 'react'
import { stationApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import FilterTabs from '../../components/ui/FilterTabs'
import Button from '../../components/ui/Button'
import Modal from '../../components/ui/Modal'
import PageHeader from '../../components/ui/PageHeader'
import { AsyncView } from '../../components/ui/States'
import { Card } from '../../components/ui/Card'
import DevNote from '../../components/ui/DevNote'
import { timeHM } from '../../lib/format'
import { cn } from '../../lib/cn'

const FILTERS = [{ value: 'semua', label: 'Semua' }, { value: 'antre', label: 'Menunggu' }, { value: 'diproses', label: 'Diproses' }, { value: 'selesai', label: 'Selesai' }]
const DOT = { antre: 'bg-bad', diproses: 'bg-[#e0b400]', selesai: 'bg-go', dibatalkan: 'bg-stone' }
const WORD = { antre: 'Menunggu', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }
const TEXT = { barista: { title: 'Antrean Minuman', sub: 'Kelola pesanan minuman yang masuk dari pelanggan' }, kitchen: { title: 'Antrean Makanan', sub: 'Kelola pesanan makanan yang masuk dari pelanggan' } }

/** BR-02: kartu tiket FIFO per stasiun. Tombol Mulai/Selesai ditambahkan (ASUMSI: tidak terlihat di layar Barista; label diambil dari dashboard Manager). */
export default function StationQueuePage({ station }) {
  const [filter, setFilter] = useState('semua')
  const [detail, setDetail] = useState(null)
  const res = useResource(() => stationApi.tickets(station, { 'filter[status]': filter }), [station, filter], { poll: true })
  const { busy, run } = useAction()

  const act = async (t, kind) => {
    const fn = kind === 'start' ? stationApi.start : stationApi.complete
    const out = await run(() => fn(station, t.id), kind === 'start' ? 'Pesanan mulai diproses' : 'Pesanan selesai')
    if (out) { setDetail(null); res.refresh() }
  }
  return (
    <>
      <PageHeader title={TEXT[station].title} subtitle={TEXT[station].sub} />
      {station === 'kitchen' && <DevNote>Desain layar Kitchen belum tersedia; memakai pola antrean Barista.</DevNote>}
      <FilterTabs options={FILTERS} value={filter} onChange={setFilter} />
      <div className="mt-5">
        <AsyncView res={res} emptyTitle="Belum ada pesanan masuk" emptyHint="Pesanan yang sudah dibayar akan muncul di sini, urut dari yang paling lama.">
          {(rows) => (
            <ul className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              {rows.map((t) => (
                <li key={t.id}>
                  <Card surface="brown" className="flex h-full flex-col gap-3 p-5">
                    <div className="flex items-start justify-between gap-3">
                      <div><p className="text-xl font-medium">{t.display_number} <span className="text-white/70">· Meja {t.table_number}</span></p><p className="text-sm text-white/70">Masuk {timeHM(t.ordered_at)}</p></div>
                      <span className="flex items-center gap-2 text-sm font-medium"><span className={cn('h-3 w-3 rounded-full', DOT[t.status.code])} aria-hidden />{WORD[t.status.code]}</span>
                    </div>
                    <ul className="flex-1 space-y-1 text-lg">{t.items.map((i) => <li key={i.name}>{i.qty} × {i.name}</li>)}</ul>
                    <div className="flex gap-2">
                      <Button size="sm" variant="sand" onClick={() => setDetail(t)}>Detail</Button>
                      {t.status.code === 'antre' && <Button size="sm" variant="accent" loading={busy} onClick={() => act(t, 'start')}>Mulai proses</Button>}
                      {t.status.code === 'diproses' && <Button size="sm" variant="accent" loading={busy} onClick={() => act(t, 'complete')}>Selesaikan</Button>}
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          )}
        </AsyncView>
      </div>
      <Modal open={Boolean(detail)} title={detail ? `Pesanan ${detail.display_number}` : ''} onClose={() => setDetail(null)}>
        {detail && (
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between"><dt>Meja</dt><dd className="font-medium">{detail.table_number}</dd></div>
            <div className="flex justify-between"><dt>Nomor pesanan</dt><dd className="font-medium">{detail.order_number}</dd></div>
            <div className="flex justify-between"><dt>Masuk</dt><dd className="font-medium">{timeHM(detail.ordered_at)}</dd></div>
            <div className="flex justify-between"><dt>Mulai diproses</dt><dd className="font-medium">{timeHM(detail.started_at)}</dd></div>
            <div className="flex justify-between"><dt>Dikerjakan oleh</dt><dd className="font-medium">{detail.handled_by || '-'}</dd></div>
            <ul className="mt-3 border-t border-black/10 pt-3 text-base">{detail.items.map((i) => <li key={i.name}>{i.qty} × {i.name}</li>)}</ul>
          </dl>
        )}
      </Modal>
    </>
  )
}
