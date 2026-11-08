import { dateTime, rupiah } from '../../lib/format'

/** Struk 80 mm (Figma KS-03). Hanya dicetak lewat CSS .print-area; printer termal dipilih di sistem kasir. */
export default function Receipt({ data }) {
  const { cafe, order, transaction_number: trx, cashier } = data
  return (
    <div className="print-area mx-auto w-full max-w-[320px] bg-white p-5 font-mono text-[13px] text-black">
      <p className="text-center text-base font-bold">{cafe.name}</p>
      <p className="text-center text-xs">{cafe.address}</p>
      <hr className="my-3 border-dashed border-black/60" />
      <dl className="space-y-0.5">
        <div className="flex justify-between"><dt>No Transaksi</dt><dd>{trx || '-'}</dd></div>
        <div className="flex justify-between"><dt>No Pesanan</dt><dd>{order.order_number}</dd></div>
        <div className="flex justify-between"><dt>Meja</dt><dd>{order.table.number}</dd></div>
        <div className="flex justify-between"><dt>Tanggal</dt><dd>{dateTime(order.paid_at || order.ordered_at)}</dd></div>
        <div className="flex justify-between"><dt>Kasir</dt><dd>{cashier || '-'}</dd></div>
      </dl>
      <hr className="my-3 border-dashed border-black/60" />
      <ul className="space-y-1">
        {order.items.map((i) => <li key={i.id} className="flex justify-between gap-2"><span>{i.name} × {i.qty}</span><span>{rupiah(i.subtotal)}</span></li>)}
      </ul>
      <hr className="my-3 border-dashed border-black/60" />
      <div className="flex justify-between font-bold"><span>Total</span><span>{rupiah(order.total)}</span></div>
      <div className="flex justify-between"><span>Pembayaran</span><span>{order.payment?.method?.label}</span></div>
      {order.payment?.amount_received != null && <><div className="flex justify-between"><span>Tunai</span><span>{rupiah(order.payment.amount_received)}</span></div><div className="flex justify-between"><span>Kembali</span><span>{rupiah(order.payment.change)}</span></div></>}
      <p className="mt-4 text-center text-xs">{cafe.footer}</p>
    </div>
  )
}
