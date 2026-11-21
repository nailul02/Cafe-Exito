import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { cashierApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import Button from '../../components/ui/Button'
import PageHeader from '../../components/ui/PageHeader'
import { AsyncView } from '../../components/ui/States'
import { Input } from '../../components/ui/Form'
import { rupiah } from '../../lib/format'
import Receipt from './Receipt'

/** KS-03: Detail Transaksi + struk dan tombol Cetak Struk. */
export default function TransactionDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const res = useResource(() => cashierApi.receipt(id), [id])
  const order = useResource(() => cashierApi.order(id), [id])
  const [proofUrl, setProofUrl] = useState(null)
  const [amountReceived, setAmountReceived] = useState('')
  const [rejectReason, setRejectReason] = useState('')
  const { busy, run } = useAction()
  const payment = order.data?.payment
  const isPending = payment?.status?.code === 'pending'
  const isCustomerQris = order.data?.order_source === 'qr_customer' && payment?.method?.code === 'qris'
  const hasPendingProof = payment?.proof?.status === 'pending'

  useEffect(() => {
    let active = true
    let objectUrl
    if (payment?.proof?.preview_available && payment.method?.code === 'qris' && order.data?.order_source === 'qr_customer') {
      cashierApi.paymentProof(payment.id).then((blob) => {
        if (active) {
          objectUrl = URL.createObjectURL(blob)
          setProofUrl(objectUrl)
        }
      }).catch(() => { if (active) setProofUrl(null) })
    } else setProofUrl(null)
    return () => {
      active = false
      if (objectUrl) URL.revokeObjectURL(objectUrl)
    }
  }, [payment?.id, payment?.proof?.preview_available, payment?.method?.code, order.data?.order_source])

  const reloadOrder = () => { order.reload(); res.reload() }
  const verify = async () => {
    const payload = payment?.method?.code === 'tunai' && amountReceived ? { amount_received: Number(amountReceived) } : {}
    if (await run(() => cashierApi.verify(payment.id, payload), 'Pembayaran terverifikasi')) reloadOrder()
  }
  const reject = async () => {
    if (await run(() => cashierApi.reject(payment.id, { reason: rejectReason.trim() }), 'Pembayaran ditolak')) reloadOrder()
  }
  return (
    <>
      <PageHeader title="Detail Transaksi" actions={<Button variant="outline" onClick={() => navigate('/cashier/orders')}>Kembali</Button>} />
      <AsyncView res={order} empty={() => false}>
        {(data) => (
          <section className="mx-auto mb-5 max-w-2xl rounded-2xl border border-brand/15 bg-white p-5 text-brand-ink shadow-sm">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div><h2 className="text-lg font-semibold">Pembayaran {data.display_number}</h2><p className="text-sm text-brand/70">{payment?.method?.label} · Order: {data.status?.label} · Pembayaran: {payment?.status?.label}</p></div>
              <strong>{rupiah(payment?.amount ?? data.total)}</strong>
            </div>
            {payment?.method?.code === 'qris' && data.order_source === 'qr_customer' && (
              <div className="mt-4 rounded-xl border border-brand/15 p-4">
                <h3 className="font-medium">Bukti QRIS pelanggan</h3>
                {payment.proof ? <p className="text-sm text-brand/70">Status bukti: {payment.proof.status}{payment.proof.reject_reason ? ` · ${payment.proof.reject_reason}` : ''}</p> : <p className="text-sm text-brand/70">Pelanggan belum mengunggah bukti.</p>}
                {payment.proof?.preview_available && (proofUrl ? <img src={proofUrl} alt="Bukti pembayaran QRIS" className="mt-3 max-h-96 max-w-full rounded-lg bg-brand/5 object-contain" /> : <p className="mt-3 text-sm">Memuat bukti…</p>)}
              </div>
            )}
            {isPending && <div className="mt-4 space-y-3">
              {payment?.method?.code === 'qris' && data.order_source === 'pos_cashier' && <p className="text-sm text-brand/70">QRIS POS diperiksa langsung oleh Kasir; bukti unggahan pelanggan tidak diperlukan.</p>}
              {payment?.method?.code === 'tunai' && <label className="block text-sm">Uang diterima (opsional)<Input inputMode="numeric" value={amountReceived} onChange={(event) => setAmountReceived(event.target.value.replace(/\D/g, ''))} placeholder={String(payment.amount)} className="mt-1" /></label>}
              {payment?.method?.code === 'qris' && <label className="block text-sm">Alasan penolakan (wajib jika menolak)<textarea value={rejectReason} onChange={(event) => setRejectReason(event.target.value)} maxLength={255} className="mt-1 w-full rounded-lg border border-brand/20 p-2" /></label>}
              <div className="flex justify-end gap-2">
                  {payment?.method?.code === 'qris' && <Button variant="outline" loading={busy} disabled={!rejectReason.trim() || (isCustomerQris && !hasPendingProof)} onClick={reject}>{isCustomerQris ? 'Tolak bukti' : 'Tolak pembayaran'}</Button>}
                <Button variant="dark" loading={busy} disabled={isCustomerQris && !hasPendingProof} onClick={verify}>Konfirmasi pembayaran</Button>
              </div>
            </div>}
          </section>
        )}
      </AsyncView>
      <AsyncView res={res} empty={() => false}>
        {(data) => (
          <div className="mx-auto max-w-md rounded-2xl bg-brand p-5">
            <Receipt data={data} />
            <Button variant="sand" block className="mt-4" disabled={!data.transaction_number} onClick={() => window.print()}>Cetak Struk</Button>
            {!data.transaction_number && <p className="mt-2 text-center text-xs text-white/80">Struk tersedia setelah pembayaran diverifikasi.</p>}
          </div>
        )}
      </AsyncView>
    </>
  )
}
