import { useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { stationApi } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import FilterTabs from '../../components/ui/FilterTabs'
import Button from '../../components/ui/Button'
import Modal from '../../components/ui/Modal'
import PageHeader from '../../components/ui/PageHeader'
import { AsyncView } from '../../components/ui/States'
import { Card, StatCard } from '../../components/ui/Card'
import { timeHM } from '../../lib/format'
import { cn } from '../../lib/cn'

const STATUS_FILTERS = [
  { value: 'semua', label: 'Semua Status' },
  { value: 'antre', label: 'Menunggu' },
  { value: 'diproses', label: 'Diproses' },
  { value: 'selesai', label: 'Selesai' },
]

const DOT = { antre: 'bg-bad', diproses: 'bg-[#e0b400]', selesai: 'bg-go', dibatalkan: 'bg-stone' }
const WORD = { antre: 'Menunggu', diproses: 'Diproses', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }

export default function ProductionDashboardPage() {
  const { user } = useAuth()
  const rawRole = user?.role?.code || user?.role
  const ownStation = user?.station || (rawRole === 'kitchen' ? 'kitchen' : 'barista')
  const [searchParams, setSearchParams] = useSearchParams()
  const canViewBoth = (user?.permissions || []).includes('queue.view_all')
  const [selectedStation, setSelectedStation] = useState(searchParams.get('station') || ownStation)
  const station = canViewBoth ? selectedStation : ownStation
  const canAct = (user?.permissions || []).includes(`queue.${station}`)
  const stationLabel = station === 'barista' ? 'Minuman' : 'Makanan'
  const [statusFilter, setStatusFilter] = useState('semua')
  const [detail, setDetail] = useState(null)

  const dashboardRes = useResource(
    () => stationApi.dashboard(station),
    [],
    { poll: true, interval: 8000 }
  )

  const ticketsRes = useResource(
    () => {
      return stationApi.tickets(station, { 'filter[status]': statusFilter })
    },
    [station, statusFilter],
    { poll: true, interval: 6000 }
  )

  const { busy, run } = useAction()

  const act = async (t, kind) => {
    const fn = kind === 'start' ? stationApi.start : stationApi.complete
    const msg = kind === 'start' ? 'Tiket mulai diproses' : 'Tiket telah diselesaikan'
    const out = await run(() => fn(t.station, t.id), msg)
    if (out) {
      setDetail(null)
      ticketsRes.refresh()
      dashboardRes.refresh()
    }
  }

  const startItem = async (ticket, item) => {
    const out = await run(() => stationApi.startItem(ticket.station, ticket.id, item.order_detail_id), 'Item mulai diproses')
    if (out) { setDetail(null); ticketsRes.refresh(); dashboardRes.refresh() }
  }

  const d = dashboardRes.data
  const userName = user?.name ? user.name.split(' ')[0] : 'Tim'

  return (
    <>
      <PageHeader
        title={canViewBoth ? 'Monitoring Antrean Produksi' : 'Dashboard Produksi'}
        subtitle={`Selamat bertugas, ${userName}! Kelola antrean ${stationLabel.toLowerCase()} di stasiun ${station === 'barista' ? 'Barista' : 'Kitchen'}.`}
      />

      {canViewBoth && (
        <div className="mt-5 flex gap-2" role="tablist" aria-label="Pilih antrean produksi">
          {[
            { id: 'barista', label: 'Minuman · Barista' },
            { id: 'kitchen', label: 'Makanan · Kitchen' },
          ].map((item) => (
            <button
              key={item.id}
              type="button"
              role="tab"
              aria-selected={station === item.id}
              onClick={() => { setSelectedStation(item.id); setSearchParams({ station: item.id }) }}
              className={cn(
                'rounded-lg px-4 py-2 text-sm font-medium',
                station === item.id ? 'bg-brand text-white' : 'bg-white text-brand-ink ring-1 ring-black/10'
              )}
            >
              {item.label}
            </button>
          ))}
        </div>
      )}

      {/* Ringkasan hanya untuk stasiun akun yang sedang login. */}
      <div className="grid gap-3 sm:grid-cols-3">
        <StatCard
          surface="brown"
          label={`Antrean ${stationLabel}`}
          value={`${d?.queued ?? '–'} antre`}
          hint="Menunggu dikerjakan"
        />
        <StatCard
          surface="brown"
          label="Sedang Diproses"
          value={d?.in_progress ?? '–'}
          hint="Dikerjakan di stasiun ini"
        />
        <StatCard
          surface="brown"
          label="Selesai Hari Ini"
          value={d?.completed_today ?? '–'}
          hint="Tiket stasiun ini"
        />
      </div>

      <div className="mt-6 flex flex-wrap items-center justify-end gap-3 border-b border-black/10 pb-4">
        <FilterTabs
          options={STATUS_FILTERS}
          value={statusFilter}
          onChange={setStatusFilter}
        />
      </div>

      {/* Daftar Antrean Tiket FIFO */}
      <div className="mt-5">
        <AsyncView
          res={ticketsRes}
          emptyTitle="Belum ada tiket antrean"
          emptyHint={`Pesanan ${stationLabel.toLowerCase()} yang terverifikasi akan masuk ke antrean ini secara berurutan (FIFO).`}
        >
          {(rows) => (
            <ul className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              {rows.map((t) => (
                <li key={t.id}>
                  <Card surface="brown" className="flex h-full flex-col justify-between gap-3 p-5">
                    <div>
                      <div className="flex items-start justify-between gap-2">
                        <div>
                          <p className="text-xl font-medium tracking-tight">
                            {t.display_number}{' '}
                            <span className="text-white/80">
                              · {t.table_number ? `Meja ${t.table_number}` : 'Takeaway (Bungkus)'}
                            </span>
                          </p>
                          {t.customer_name && (
                            <p className="text-sm font-normal text-white/90">
                              Atas nama: <span className="font-semibold text-accent">{t.customer_name}</span>
                            </p>
                          )}
                          <p className="text-xs text-white/70">
                            Masuk: {timeHM(t.ordered_at)}
                          </p>
                        </div>

                        <div className="flex flex-col items-end gap-1">
                          <span className={cn(
                            'rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider',
                            t.station === 'barista' ? 'bg-amber-700/80 text-amber-100' : 'bg-orange-800/80 text-orange-100'
                          )}>
                            {t.station === 'barista' ? '☕ Drink' : '🍳 Food'}
                          </span>
                          <span className="flex items-center gap-1.5 text-xs font-medium text-white/90">
                            <span className={cn('h-2.5 w-2.5 rounded-full', DOT[t.status?.code || t.status] || 'bg-stone')} aria-hidden />
                            {WORD[t.status?.code || t.status] || t.status?.label || t.status}
                          </span>
                        </div>
                      </div>

                      {/* Item yang ditangani */}
                      <ul className="mt-4 space-y-1.5 border-t border-white/10 pt-3">
                        {t.items.map((i, idx) => (
                          <li key={idx} className="flex items-center justify-between gap-2 text-base font-medium">
                            <span>{i.name}</span>
                            <span className="flex items-center gap-2"><span className="font-bold text-accent">×{i.qty}</span>{canAct && i.qty > 0 && !i.started_at && <Button size="sm" variant="accent" loading={busy} onClick={() => startItem(t, i)}>Mulai</Button>}{i.started_at && <span className="text-xs text-white/75">Diproses</span>}</span>
                          </li>
                        ))}
                      </ul>
                    </div>

                    {/* Tombol Aksi */}
                    <div className="mt-2 flex items-center justify-between border-t border-white/10 pt-3">
                      <Button size="sm" variant="sand" onClick={() => setDetail(t)}>
                        Detail
                      </Button>

                      {canAct && (
                        <div className="flex gap-2">
                          {t.status.code === 'diproses' && t.items.every((i) => !i.qty || i.started_at) && (
                            <Button size="sm" variant="accent" loading={busy} onClick={() => act(t, 'complete')}>
                              Selesaikan
                            </Button>
                          )}
                        </div>
                      )}
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          )}
        </AsyncView>
      </div>

      {/* Modal Detail Tiket */}
      <Modal
        open={Boolean(detail)}
        title={detail ? `Rincian Tiket ${detail.display_number} (${detail.station === 'barista' ? 'Drink' : 'Food'})` : ''}
        onClose={() => setDetail(null)}
      >
        {detail && (
          <dl className="space-y-2.5 text-sm">
            <div className="flex justify-between border-b pb-1">
              <dt className="text-brand/70">Tipe Pelayanan</dt>
              <dd className="font-semibold text-brand-ink">
                {detail.table_number ? `Dine In (Meja ${detail.table_number})` : 'Takeaway (Bawa Pulang)'}
              </dd>
            </div>
            {detail.customer_name && (
              <div className="flex justify-between border-b pb-1">
                <dt className="text-brand/70">Nama Pemesan</dt>
                <dd className="font-semibold text-brand-ink">{detail.customer_name}</dd>
              </div>
            )}
            <div className="flex justify-between border-b pb-1">
              <dt className="text-brand/70">Nomor Pesanan Induk</dt>
              <dd className="font-mono font-medium">{detail.order_number}</dd>
            </div>
            <div className="flex justify-between border-b pb-1">
              <dt className="text-brand/70">Stasiun Produksi</dt>
              <dd className="font-semibold capitalize">{detail.station === 'barista' ? 'Barista (Minuman)' : 'Kitchen (Makanan)'}</dd>
            </div>
            <div className="flex justify-between border-b pb-1">
              <dt className="text-brand/70">Waktu Masuk</dt>
              <dd>{timeHM(detail.ordered_at)}</dd>
            </div>
            {detail.started_at && (
              <div className="flex justify-between border-b pb-1">
                <dt className="text-brand/70">Mulai Diproses</dt>
                <dd>{timeHM(detail.started_at)}</dd>
              </div>
            )}
            {detail.ready_at && (
              <div className="flex justify-between border-b pb-1">
                <dt className="text-brand/70">Selesai</dt>
                <dd>{timeHM(detail.ready_at)}</dd>
              </div>
            )}
            {detail.handled_by && (
              <div className="flex justify-between border-b pb-1">
                <dt className="text-brand/70">Dikerjakan Oleh</dt>
                <dd className="font-medium">{detail.handled_by}</dd>
              </div>
            )}
            <div className="mt-3 pt-2">
              <p className="font-semibold text-brand-ink">Daftar Item Menu:</p>
              <ul className="mt-1.5 space-y-1">
                {detail.items.map((i, idx) => (
                  <li key={idx} className="flex justify-between rounded bg-cream/70 px-3 py-1.5 font-medium">
                    <span>{i.name}</span>
                    <span className="font-bold text-accent-deep">×{i.qty}</span>
                  </li>
                ))}
              </ul>
            </div>
          </dl>
        )}
      </Modal>
    </>
  )
}
