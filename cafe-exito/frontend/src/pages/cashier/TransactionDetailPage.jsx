import { useNavigate, useParams } from 'react-router-dom'
import { cashierApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import Button from '../../components/ui/Button'
import PageHeader from '../../components/ui/PageHeader'
import { AsyncView } from '../../components/ui/States'
import Receipt from './Receipt'

/** KS-03: Detail Transaksi + struk dan tombol Cetak Struk. */
export default function TransactionDetailPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const res = useResource(() => cashierApi.receipt(id), [id])
  return (
    <>
      <PageHeader title="Detail Transaksi" actions={<Button variant="outline" onClick={() => navigate('/cashier/orders')}>Kembali</Button>} />
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
