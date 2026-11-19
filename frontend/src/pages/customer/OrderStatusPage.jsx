import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { customerApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { customerSteps } from '../../lib/status'
import { AsyncView } from '../../components/ui/States'
import Button from '../../components/ui/Button'
import { cn } from '../../lib/cn'
import { useAction } from '../../hooks/useAction'

/** P-07: Status Pesanan (3 langkah) dengan polling. Keadaan menunggu pembayaran/dibatalkan ditambahkan (ASUMSI). */
export default function OrderStatusPage() {
  const { token } = useParams()
  const navigate = useNavigate()
  const res = useResource(() => customerApi.order(token), [token], { poll: true })
  const [file, setFile] = useState(null)
  const [fileError, setFileError] = useState('')
  const { busy, run } = useAction()
  const upload = async () => {
    if (!file) return
    const result = await run(() => customerApi.uploadPaymentProof(token, file), 'Bukti dikirim untuk verifikasi kasir')
    if (result) { setFile(null); setFileError(''); res.reload() }
  }

  return (
    <div className="flex flex-1 flex-col px-6 pt-8">
      <h1 className="text-2xl font-medium">Status Pesanan</h1>
      <AsyncView res={res} empty={() => false}>
        {(o) => {
          const steps = customerSteps(o.status.code)
          const itemCount = o.items.reduce((a, i) => a + i.qty, 0)
          return (
            <>
              <div className="mt-8"><p className="text-3xl font-medium">{o.display_number}</p><p className="text-lg font-medium text-[#797474]">{o.table?.number ? `Meja ${o.table.number}` : (o.customer_name || 'Takeaway')} · {itemCount} item</p></div>
              {o.payment?.method?.code === 'qris' && <p className="mt-4 rounded-xl border px-4 py-3 text-sm">Status pembayaran aktual: <strong>{o.payment.status.label}</strong></p>}
              {o.status.code === 'menunggu_pembayaran' && <p className="mt-6 rounded-xl bg-warn-soft px-4 py-3 text-sm font-medium text-warn">Menunggu pembayaran. Selesaikan pembayaran di kasir agar pesanan Anda diproses.</p>}
              {o.payment?.method?.code === 'qris' && o.status.code === 'menunggu_pembayaran' && <section className="mt-4 space-y-3 rounded-xl border p-4">
                {(o.payment.proof?.reject_reason || o.payment.previous_rejection?.reason || o.payment.reject_reason) && <p className="text-bad">Bukti ditolak: {o.payment.proof?.reject_reason || o.payment.previous_rejection?.reason || o.payment.reject_reason}. Silakan unggah bukti baru.</p>}
                {o.payment.proof?.status === 'pending' && <p className="text-warn">Bukti menunggu verifikasi kasir.</p>}
                {o.payment.proof?.status === 'verified' && <p className="text-go">Bukti diterima.</p>}
                {(!o.payment.proof || o.payment.proof.status === 'rejected') && <>
                  <label className="block text-sm">Unggah bukti QRIS (JPEG, PNG, WEBP; maksimal 3 MB)
                    <input className="mt-2 block w-full" type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => { const chosen = e.target.files?.[0] || null; const typeOk = !chosen || ['image/jpeg', 'image/png', 'image/webp'].includes(chosen.type); const sizeOk = !chosen || chosen.size <= 3 * 1024 * 1024; setFileError(!typeOk ? 'Format file harus JPEG, PNG, atau WEBP.' : !sizeOk ? 'Ukuran file maksimal 3 MB.' : ''); setFile(chosen && typeOk && sizeOk ? chosen : null) }} />
                  </label>
                  {fileError && <p role="alert" className="text-bad">{fileError}</p>}
                  {file && <Button disabled={busy || !!fileError} onClick={upload}>Kirim Bukti</Button>}
                </>}
              </section>}
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
