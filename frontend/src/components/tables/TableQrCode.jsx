import { useEffect, useState } from 'react'
import QRCode from 'qrcode'

export default function TableQrCode({ table }) {
  const [image, setImage] = useState('')

  useEffect(() => {
    let active = true
    setImage('')
    QRCode.toDataURL(table.qr_url, { errorCorrectionLevel: 'M', margin: 2, width: 176 })
      .then((dataUrl) => { if (active) setImage(dataUrl) })
      .catch(() => { if (active) setImage('error') })

    return () => { active = false }
  }, [table.qr_url])

  return (
    <div className="flex min-w-36 flex-col items-center gap-1">
      {image && image !== 'error'
        ? <img src={image} alt={`QR Code Meja ${table.table_number}`} className="h-32 w-32 rounded bg-white p-1" />
        : <span className="grid h-32 w-32 place-items-center rounded bg-white/10 p-2 text-center text-xs">{image === 'error' ? 'QR gagal dibuat' : 'Membuat QR…'}</span>}
      <a href={table.qr_url} target="_blank" rel="noreferrer" className="text-xs underline">Buka tautan meja</a>
    </div>
  )
}
