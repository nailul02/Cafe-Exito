import { Link } from 'react-router-dom'
import { stationApi } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useResource } from '../../hooks/useResource'
import { StatCard } from '../../components/ui/Card'
import PageHeader from '../../components/ui/PageHeader'
import DevNote from '../../components/ui/DevNote'

/** BR-01 (Barista) / dashboard Kitchen: sapaan + tiga penghitung. Kitchen mengikuti pola Barista (tanpa desain Figma). */
export default function StationDashboardPage({ station }) {
  const { user } = useAuth()
  const res = useResource(() => stationApi.dashboard(station), [station], { poll: true })
  const d = res.data
  return (
    <>
      <PageHeader title="Dashboard" />
      {station === 'kitchen' && <DevNote>Desain Kitchen belum tersedia di Figma. Tampilan mengikuti pola Barista.</DevNote>}
      <p className="text-lg font-medium">Semangat kerja, {user.name.split(' ')[0]}!</p>
      <div className="mt-5 grid gap-3 sm:grid-cols-3">
        <StatCard surface="brown" label="Pesanan (menunggu)" value={d?.queued ?? '–'} />
        <StatCard surface="brown" label="Diproses" value={d?.in_progress ?? '–'} />
        <StatCard surface="brown" label="Selesai hari ini" value={d?.completed_today ?? '–'} />
      </div>
      <Link to={`/${station}/queue`} className="mt-6 inline-block rounded-xl bg-brand px-5 py-3 font-medium text-white">Buka antrean {station === 'barista' ? 'minuman' : 'makanan'}</Link>
    </>
  )
}
