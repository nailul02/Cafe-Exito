import { usersApi } from '../../api'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import { Card } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import DataTable from '../../components/ui/DataTable'
import { AsyncView } from '../../components/ui/States'
import PageHeader from '../../components/ui/PageHeader'
import TableQrCode from '../../components/tables/TableQrCode'

export default function UsersPage() {
  const tables = useResource(() => usersApi.tables(), [])
  const { run } = useAction()
  const columns = [
    { key: 'number', header: 'Meja', render: (table) => <span className="font-medium">Meja {table.table_number}</span> },
    { key: 'status', header: 'Status', render: (table) => table.status?.label || table.status?.code || table.status },
    { key: 'qr', header: 'QR Code unik', render: (table) => <TableQrCode table={table} /> },
    { key: 'actions', header: 'Tindakan', render: (table) => <Button size="sm" variant="outline" onClick={async () => { await run(() => usersApi.regenerateQr(table.id), 'QR dibuat ulang; QR lama tidak berlaku'); tables.reload() }}>Buat ulang QR</Button> },
  ]
  const addTable = async () => {
    const tableNumber = window.prompt('Nomor meja baru?')
    if (!tableNumber?.trim()) return
    const result = await run(() => usersApi.createTable({ table_number: tableNumber.trim() }), 'Meja ditambahkan')
    if (result) tables.reload()
  }

  return (
    <>
      <PageHeader title="Meja & QR" subtitle="Kelola meja dan tautan QR pelanggan." actions={<Button variant="accent" onClick={addTable}>+ Tambah meja</Button>} />
      <Card className="mt-5 p-5"><AsyncView res={tables}>{(rows) => <DataTable columns={columns} rows={rows} />}</AsyncView></Card>
    </>
  )
}
