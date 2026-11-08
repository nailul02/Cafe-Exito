import { useNavigate, useParams } from 'react-router-dom'
import { customerApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { customerSteps } from '../../lib/status'
import { AsyncView } from '../../components/ui/States'
import Button from '../../components/ui/Button'
import { cn } from '../../lib/cn'

/** P-07: Status Pesanan (3 langkah) dengan polling. Keadaan menunggu pembayaran/dibatalkan ditambahkan (ASUMSI). */
export default function OrderStatusPage() {
  const { token } = useParams()
  const navigate = useNavigate()
  const res = useResource(() => customerApi.order(token), [token], { poll: true })
  const { busy, run } = useAction()

  return (
    <div className="flex flex-1 flex-col px-6 pt-8">
      <h1 className="text-2xl font-medium">Status Pesanan</h1>
      <AsyncView res={res} empty={() => false}>
        {(o) => {
          const steps = customerSteps(o.status.code)
          const itemCount = o.items.reduce((a, i) => a + i.qty, 0)
          return (
            <>
              <div className="mt-8"><p className="text-3xl font-medium">{o.display_number}</p><p className="text-lg font-medium text-[#797474]">Meja {o.table.number} · {itemCount} item</p></div>
              {o.status.code === 'menunggu_pembayaran' && <p className="mt-6 rounded-xl bg-warn-soft px-4 py-3 text-sm font-medium text-warn">Menunggu pembayaran. Selesaikan pembayaran di kasir agar pesanan Anda diproses.</p>}
              {o.status.code === 'dibatalkan' && <p className="mt-6 rounded-xl bg-bad-soft px-4 py-3 text-sm font-medium text-bad">Pesanan dibatalkan. Silakan pesan ulang atau hubungi kasir.</p>}
              {o.status.code !== 'dibatalkan' && (
                <ol className="mt-10 space-y-8">
                  {steps.map((s) => (
                    <li key={s.label} className="flex items-center gap-6">
                      <span className={cn('h-11 w-12 rounded-full', s.done ? 'bg-go' : 'bg-stone', s.current && 'ring-4 ring-go/30')} aria-hidden />
                      <span className="text-lg font-medium">{s.label}{s.done && <span className="sr-only"> (tercapai)</span>}</span>
                    </li>
                  ))}
                </ol>
              )}
              <div className="mt-auto space-y-3 pb-6 pt-10">
                {o.status.code === 'menunggu_pembayaran' && <Button variant="outline" block loading={busy} onClick={async () => { await run(() => customerApi.cancelOrder(token), 'Pesanan dibatalkan'); res.reload() }}>Batalkan pesanan</Button>}
                <div className="rounded-2xl bg-brand px-6 py-5 text-center text-lg font-medium leading-snug text-white">Selamat Menikmati<br />Terimakasih Ditunggu<br />Pesanan Berikutnya</div>
                <Button variant="sand" block onClick={() => navigate('/order/menu')}>Pesan lagi</Button>
              </div>
            </>
          )
        }}
      </AsyncView>
    </div>
  )
}
