import { describe, expect, it, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import { createElement } from 'react'
import { TABLES } from '../mocks/seed'
import { buildSeed, load, save } from '../mocks/db'
import TableQrCode from '../components/tables/TableQrCode'

vi.mock('qrcode', () => ({
  default: { toDataURL: vi.fn(async (url) => `data:image/png;base64,${btoa(url)}`) },
}))

describe('nomor meja mock', () => {
  it('menyediakan nomor 1 sampai 15 tanpa duplikasi dan token QR unik', () => {
    expect(TABLES).toHaveLength(15)
    expect(TABLES.map((table) => table.table_number)).toEqual(Array.from({ length: 15 }, (_, i) => String(i + 1)))
    expect(new Set(TABLES.map((table) => table.table_number)).size).toBe(15)
    expect(new Set(TABLES.map((table) => table.qr_token)).size).toBe(15)
  })

  it('melengkapi cache mock lama tanpa mengganti ID dan token QR meja yang sudah tersimpan', () => {
    const previous = buildSeed()
    previous.tables = previous.tables.slice(0, 12)
    previous.seq.table = 12
    const oldIdentity = previous.tables.map(({ id, qr_token }) => [id, qr_token])
    save(previous)

    const migrated = load()

    expect(migrated.tables).toHaveLength(15)
    expect(migrated.tables.slice(0, 12).map(({ id, qr_token }) => [id, qr_token])).toEqual(oldIdentity)
    expect(migrated.tables.map((table) => table.table_number)).toEqual(Array.from({ length: 15 }, (_, i) => String(i + 1)))
  })

  it('merender 15 QR image dengan payload URL yang masing-masing berbeda', async () => {
    render(createElement('div', null, TABLES.map((table) => createElement(TableQrCode, {
      key: table.id,
      table: { ...table, qr_url: `https://cafe.test/order?t=${table.qr_token}` },
    }))))
    const qrImages = await screen.findAllByRole('img')

    expect(qrImages).toHaveLength(15)
    expect(new Set(qrImages.map((image) => image.getAttribute('src'))).size).toBe(15)
    expect(qrImages.map((image) => image.getAttribute('alt'))).toEqual(TABLES.map((table) => `QR Code Meja ${table.table_number}`))
  })
})
