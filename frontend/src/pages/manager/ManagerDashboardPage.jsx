import { reportsApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { Card, StatCard } from '../../components/ui/Card'
import { Badge, StatusPill } from '../../components/ui/Badge'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import { ORDER_STATUS, TICKET_STATUS } from '../../lib/status'
import { rupiah, timeHM } from '../../lib/format'

/** MG-01: dashboard Manager. Pantauan pesanan hanya-baca (K-B11 PENDING: apakah Manager boleh menjalankan tiket). */
export default function ManagerDashboardPage() {
  const res = useResource(() => reportsApi.managerDashboard(), [], { poll: true })
  return (
    <>
      <PageHeader title="Dashboard" subtitle="Pantau pesanan secara langsung." />
      <AsyncView res={res} empty={() => false}>
        {(d) => (
          <div className="space-y-5" data-testid="manager-dashboard-container">
            <div className="grid gap-3 sm:grid-cols-2">
              <StatCard label="Penjualan hari ini" value={rupiah(d?.revenue_today ?? 0)} />
              <StatCard label="Pesanan aktif" value={d?.active_orders?.total ?? 0} hint={`${d?.active_orders?.queued ?? 0} antre · ${d?.active_orders?.in_progress ?? 0} diproses`} />
            </div>
            <div className="grid gap-5 xl:grid-cols-3">
              <Card className="p-5 xl:col-span-2">
                <h2 className="mb-3 font-ui font-semibold text-brand-ink">Pantau pesanan masuk</h2>
                {!d?.orders || d.orders.length === 0 ? <p className="py-8 text-center text-sm text-brand/70">Belum ada pesanan hari ini.</p> : (
                  <ul className="grid gap-3 md:grid-cols-2">{d.orders.map((o) => (
                    <li key={o.id} className="rounded-2xl bg-cream/60 p-4">
                      <div className="flex items-center justify-between">
                        <p className="font-semibold">{o.display_number} <span className="font-normal text-brand/70">· {o.table?.number ? `Meja ${o.table.number}` : (o.customer_name || 'Takeaway')}</span></p>
                        <StatusPill map={ORDER_STATUS} code={o.status?.code || o.status} />
                      </div>
                      <p className="mt-2 text-sm">{(o.items || []).map((i) => `${i.qty}× ${i.name}`).join(', ')}</p>
                      <div className="mt-2 flex flex-wrap items-center gap-1.5">
                        {(o.tickets || []).map((t) => {
                          const code = t.status?.code || t.status || 'antre'
                          const st = TICKET_STATUS[code] || { label: code, tone: 'neutral' }
                          return <Badge key={t.id} tone={st.tone}>{t.station === 'barista' ? 'Bar' : 'Dapur'}: {st.label}</Badge>
                        })}
                        <span className="ml-auto text-sm font-medium">{rupiah(o.total)} · {timeHM(o.ordered_at)}</span>
                      </div>
                    </li>
                  ))}</ul>
                )}
              </Card>
            </div>
          </div>
        )}
      </AsyncView>
    </>
  )
}
