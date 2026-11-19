import { Card } from '../../components/ui/Card'
import { AsyncView } from '../../components/ui/States'
import { dateTime } from '../../lib/format'

export default function AttendanceReportTable({ res, showEvidence = false }) {
  return (
    <Card className="mt-5 p-5">
      <AsyncView res={res} emptyTitle="Belum ada data absensi">
        {(rows) => (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b border-black/10 text-xs uppercase tracking-wide text-brand/60">
                <tr>
                  <th className="pb-3 pr-4">Pegawai</th>
                  <th className="pb-3 pr-4">Role</th>
                  <th className="pb-3 pr-4">Tanggal</th>
                  <th className="pb-3 pr-4">Check-in</th>
                  <th className="pb-3 pr-4">Check-out</th>
                  <th className="pb-3">Status</th>
                  {showEvidence && <th className="pb-3">Foto & lokasi</th>}
                </tr>
              </thead>
              <tbody className="divide-y divide-black/5">
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td className="py-3 pr-4 font-medium text-brand-ink">{row.user?.name || '—'}</td>
                    <td className="py-3 pr-4 text-brand/70">{row.user?.role?.label || '—'}</td>
                    <td className="py-3 pr-4">{row.attendance_date}</td>
                    <td className="py-3 pr-4">{row.check_in_at ? dateTime(row.check_in_at) : '—'}</td>
                    <td className="py-3 pr-4">{row.check_out_at ? dateTime(row.check_out_at) : '—'}</td>
                    <td className="py-3">{row.status?.label || row.status?.code || '—'}</td>
                    <td className="py-3"><div className="flex flex-col gap-1">{(row.events || []).map((event) => <span key={event.id}>{event.photo_url ? <a className="text-brand underline" href={event.photo_url} target="_blank" rel="noreferrer">{event.event_type === 'check_in' ? 'Check-in' : 'Check-out'} · Foto</a> : (event.event_type === 'check_in' ? 'Check-in' : 'Check-out')} · {event.latitude}, {event.longitude} · Akurasi {event.accuracy_meters == null ? '—' : `±${Math.round(event.accuracy_meters)} m`}</span>)}</div></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </AsyncView>
    </Card>
  )
}
