import { useEffect, useRef, useState } from 'react'
import { attendanceApi } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useResource } from '../../hooks/useResource'
import { useAction } from '../../hooks/useAction'
import PageHeader from '../../components/ui/PageHeader'
import { Card, StatCard } from '../../components/ui/Card'
import Button from '../../components/ui/Button'
import { AsyncView } from '../../components/ui/States'
import { dateTime, timeHM } from '../../lib/format'

const validCoordinates = (latitude, longitude) => Number.isFinite(latitude)
  && Number.isFinite(longitude)
  && latitude >= -90 && latitude <= 90
  && longitude >= -180 && longitude <= 180

export default function StaffAttendancePage() {
  const { user } = useAuth()
  const statusRes = useResource(() => attendanceApi.todayStatus(), [])
  const historyRes = useResource(() => attendanceApi.myHistory({ per_page: 30 }), [])
  const { busy, run } = useAction()
  const videoRef = useRef(null)
  const [cameraStream, setCameraStream] = useState(null)
  const [photo, setPhoto] = useState(null)
  const [photoPreview, setPhotoPreview] = useState('')
  const [location, setLocation] = useState(null)
  const [captureError, setCaptureError] = useState('')
  const [locationSuccess, setLocationSuccess] = useState(false)
  const [cameraBusy, setCameraBusy] = useState(false)
  const status = statusRes.data
  const canCheckIn = status && !status.checked_in
  const canCheckOut = status?.checked_in && !status.checked_out

  useEffect(() => {
    const video = videoRef.current
    if (!video || !cameraStream) return
    video.srcObject = cameraStream
    video.play().catch(() => setCaptureError('Kamera tidak dapat menampilkan preview. Coba mulai kamera kembali.'))
  }, [cameraStream])

  useEffect(() => () => cameraStream?.getTracks().forEach((track) => track.stop()), [cameraStream])
  useEffect(() => () => { if (photoPreview) URL.revokeObjectURL(photoPreview) }, [photoPreview])

  const stopCamera = () => {
    cameraStream?.getTracks().forEach((track) => track.stop())
    setCameraStream(null)
    if (videoRef.current) videoRef.current.srcObject = null
  }

  const startCamera = async () => {
    setCaptureError('')
    if (!navigator.mediaDevices?.getUserMedia) {
      setCaptureError('Browser tidak mendukung kamera langsung. Buka aplikasi melalui HTTPS atau localhost di browser yang mendukung kamera.')
      return
    }
    setCameraBusy(true)
    try {
      setCameraStream(await navigator.mediaDevices.getUserMedia({
        audio: false,
        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } },
      }))
    } catch (error) {
      setCaptureError(error?.name === 'NotAllowedError'
        ? 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini lalu coba lagi.'
        : error?.name === 'NotFoundError'
          ? 'Kamera tidak ditemukan pada perangkat ini.'
          : 'Kamera tidak dapat dibuka. Periksa izin dan koneksi kamera perangkat.')
    } finally {
      setCameraBusy(false)
    }
  }

  const capturePhoto = () => {
    const video = videoRef.current
    if (!video || video.videoWidth < 1 || video.videoHeight < 1) {
      setCaptureError('Preview kamera belum siap. Tunggu sebentar lalu coba lagi.')
      return
    }
    const canvas = document.createElement('canvas')
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    canvas.getContext('2d')?.drawImage(video, 0, 0, canvas.width, canvas.height)
    canvas.toBlob((blob) => {
      if (!blob) { setCaptureError('Foto tidak dapat diproses. Coba ambil ulang.'); return }
      if (blob.size > 2 * 1024 * 1024) { setCaptureError('Ukuran foto melebihi 2 MB. Ambil ulang dengan ukuran yang lebih kecil.'); return }
      setPhoto(new File([blob], `attendance-${Date.now()}.jpg`, { type: 'image/jpeg' }))
      setPhotoPreview(URL.createObjectURL(blob))
      setCaptureError('')
      stopCamera()
    }, 'image/jpeg', 0.88)
  }

  const retakePhoto = async () => {
    setPhoto(null)
    setPhotoPreview('')
    await startCamera()
  }

  const obtainLocation = async () => {
    setCaptureError('')
    setLocationSuccess(false)
    setLocation(null)
    if (!navigator.geolocation) {
      setCaptureError('Browser tidak mendukung lokasi. Gunakan browser yang mendukung Geolocation dan izinkan akses lokasi.')
      return
    }
    try {
      const position = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(
        resolve, reject, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 },
      ))
      const latitude = Number(position.coords.latitude)
      const longitude = Number(position.coords.longitude)
      const accuracy = position.coords.accuracy
      if (!Number.isFinite(accuracy) || accuracy < 0) {
        setCaptureError('Browser tidak memberikan nilai akurasi GPS yang valid. Aktifkan layanan lokasi presisi lalu coba lagi.')
        return
      }
      if (!validCoordinates(latitude, longitude)) {
        setCaptureError('Koordinat yang diterima tidak valid. Aktifkan lokasi perangkat lalu coba lagi.')
        return
      }
      setLocation({ latitude, longitude, accuracy, capturedAt: Date.now() })
      setLocationSuccess(true)
      setCaptureError('')
    } catch (error) {
      setLocationSuccess(false)
      setCaptureError(error?.code === 1
        ? 'Izin lokasi ditolak. Aktifkan izin lokasi untuk situs ini lalu coba lagi.'
        : error?.code === 3
          ? 'Waktu memperoleh lokasi habis. Pastikan GPS aktif lalu coba lagi.'
          : 'Lokasi tidak tersedia. Aktifkan layanan lokasi perangkat dan coba lagi.')
    }
  }

  const record = async (type) => {
    const isIn = type === 'check-in'
    if (!photo) { setCaptureError('Ambil selfie melalui kamera terlebih dahulu.'); return }
    if (!location || !validCoordinates(location.latitude, location.longitude)) {
      setCaptureError('Lokasi GPS yang valid wajib diperoleh sebelum absensi.')
      return
    }
    if (Date.now() - location.capturedAt > 5 * 60 * 1000) {
      setLocation(null)
      setCaptureError('Lokasi sudah lebih dari 5 menit. Ambil lokasi GPS kembali sebelum mengirim.')
      return
    }
    const payload = new FormData()
    payload.append('photo', photo)
    payload.append('latitude', String(location.latitude))
    payload.append('longitude', String(location.longitude))
    payload.append('accuracy_meters', String(location.accuracy))
    payload.append('device_info', navigator.userAgent.slice(0, 255))
    const result = await run(
      () => (isIn ? attendanceApi.checkIn(payload) : attendanceApi.checkOut(payload)),
      isIn ? 'Check-in tercatat' : 'Check-out tercatat',
    )
    if (result) {
      setPhoto(null)
      setPhotoPreview('')
      setLocation(null)
      setLocationSuccess(false)
      setCaptureError('')
      statusRes.refresh()
      historyRes.refresh()
    }
  }

  return (
    <>
      <PageHeader title="Absensi Staf" subtitle={`Catat kehadiran harian untuk ${user?.name || 'akun Anda'}.`} />
      <div className="mt-5 grid gap-4 sm:grid-cols-3">
        <StatCard label="Status hari ini" value={!status ? '—' : status.checked_out ? 'Selesai' : status.checked_in ? 'Sedang bertugas' : 'Belum check-in'} hint={status?.attendance_date || 'Memuat tanggal'} />
        <StatCard label="Check-in" value={status?.check_in_at ? timeHM(status.check_in_at) : '—'} hint={status?.check_in_at ? dateTime(status.check_in_at) : 'Belum tercatat'} />
        <StatCard label="Check-out" value={status?.check_out_at ? timeHM(status.check_out_at) : '—'} hint={status?.check_out_at ? dateTime(status.check_out_at) : 'Belum tercatat'} />
      </div>

      <Card className="mt-5 flex flex-col gap-4 p-5">
        <div>
          <h2 className="font-semibold text-brand-ink">Kehadiran hari ini</h2>
          <p className="mt-1 text-sm text-brand/70">Check-in dan check-out memerlukan selfie langsung dari kamera serta lokasi GPS perangkat. Waktu resmi dicatat oleh server.</p>
        </div>
        <div className="grid gap-5 lg:grid-cols-2">
          <section className="space-y-3" aria-label="Selfie absensi">
            <h3 className="text-sm font-semibold text-brand-ink">Foto kamera</h3>
            {cameraStream && <video ref={videoRef} autoPlay muted playsInline className="aspect-[4/3] w-full max-w-lg rounded-xl bg-black object-cover" aria-label="Preview kamera selfie" />}
            {photoPreview && <img src={photoPreview} alt="Preview selfie absensi" className="aspect-[4/3] w-full max-w-lg rounded-xl bg-black object-cover" />}
            {!cameraStream && !photoPreview && <div className="flex aspect-[4/3] w-full max-w-lg items-center justify-center rounded-xl bg-brand-ink/5 text-sm text-brand/60">Kamera belum aktif</div>}
            <div className="flex flex-wrap gap-2">
              {!cameraStream && !photo && <Button variant="outline" loading={cameraBusy} onClick={startCamera}>Aktifkan kamera</Button>}
              {cameraStream && <><Button variant="accent" onClick={capturePhoto}>Ambil foto</Button><Button variant="outline" onClick={stopCamera}>Tutup kamera</Button></>}
              {photo && <Button variant="outline" onClick={retakePhoto}>Ambil ulang</Button>}
            </div>
            <p className="text-xs text-brand/60">Foto diambil saat tombol ambil foto ditekan. Upload dari galeri tidak digunakan untuk absensi.</p>
          </section>
          <section className="space-y-3" aria-label="Lokasi GPS absensi">
            <h3 className="text-sm font-semibold text-brand-ink">Lokasi GPS</h3>
            {location ? (
              <div className="rounded-xl bg-ok-soft p-4 text-sm text-brand-ink" role="status">
                <p className="font-semibold">Lokasi berhasil diperoleh</p>
                <p className="mt-1">Latitude {location.latitude.toFixed(6)}, longitude {location.longitude.toFixed(6)}</p>
                <p className="mt-1">{location.accuracy == null ? 'Browser tidak menyediakan akurasi lokasi.' : `Akurasi ±${Math.round(location.accuracy)} m`}</p>
              </div>
            ) : <p className="rounded-xl bg-brand-ink/5 p-4 text-sm text-brand/70">Lokasi belum diperoleh. Absensi belum dapat dikirim.</p>}
            <Button variant="outline" onClick={obtainLocation}>Ambil lokasi GPS</Button>
            <p className="text-xs text-brand/60">Pastikan izin lokasi diaktifkan pada browser. Akurasi browser tidak menjamin perlindungan dari pemalsuan lokasi.</p>
          </section>
        </div>
        {captureError && !locationSuccess && <p role="alert" className="text-sm text-bad">{captureError}</p>}
        <div className="flex flex-wrap gap-2 border-t border-black/5 pt-4">
          <Button variant="accent" loading={busy} disabled={!canCheckIn || !photo || !location} onClick={() => record('check-in')}>Check-in</Button>
          <Button variant="outline" loading={busy} disabled={!canCheckOut || !photo || !location} onClick={() => record('check-out')}>Check-out</Button>
        </div>
      </Card>

      <Card className="mt-5 p-5">
        <h2 className="mb-4 font-semibold text-brand-ink">Riwayat absensi</h2>
        <AsyncView res={historyRes} emptyTitle="Belum ada riwayat absensi">
          {(rows) => (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-black/10 text-xs uppercase tracking-wide text-brand/60">
                  <tr><th className="pb-3 pr-4">Tanggal</th><th className="pb-3 pr-4">Status</th><th className="pb-3 pr-4">Check-in</th><th className="pb-3">Check-out</th><th className="pb-3">Foto & lokasi</th></tr>
                </thead>
                <tbody className="divide-y divide-black/5">
                  {rows.map((row) => (
                    <tr key={row.id}>
                      <td className="py-3 pr-4">{row.attendance_date}</td>
                      <td className="py-3 pr-4">{row.status?.label || row.status?.code || '—'}</td>
                      <td className="py-3 pr-4">{row.check_in_at ? dateTime(row.check_in_at) : '—'}</td>
                      <td className="py-3">{row.check_out_at ? dateTime(row.check_out_at) : '—'}</td>
                      <td className="py-3"><div className="flex flex-col gap-1">{(row.events || []).map((event) => event.photo_url ? <a key={event.id} className="text-brand underline" href={event.photo_url} target="_blank" rel="noreferrer">{event.event_type === 'check_in' ? 'Check-in' : 'Check-out'} · Foto/GPS · ±{event.accuracy_meters ?? '—'} m</a> : <span key={event.id} className="text-brand/70">{event.event_type === 'check_in' ? 'Check-in' : 'Check-out'} · {event.latitude}, {event.longitude}</span>)}</div></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </AsyncView>
      </Card>
    </>
  )
}
