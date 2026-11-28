import { existsSync } from 'node:fs'
import { resolve } from 'node:path'
import { fireEvent, render, screen } from '@testing-library/react'
import { MENU_IMAGE_MAP, getMenuImageUrl, MenuImage } from '../lib/menuImages'

describe('menu image resolution', () => {
  it('prefers a valid backend image URL over the local name mapping', () => {
    expect(getMenuImageUrl({ name: 'Americano', image_url: 'https://cdn.example.test/americano.webp' }))
      .toBe('https://cdn.example.test/americano.webp')
  })

  it('falls back to the local mapping when the backend URL points to broken storage', () => {
    expect(getMenuImageUrl({ name: 'Americano', image_url: 'http://localhost:8000/storage/images/menus/americano.jpg' }))
      .toBe('/menu/americano.jpg')
  })

  it('uses an unmapped valid backend URL and returns null when no image exists', () => {
    expect(getMenuImageUrl({ name: 'Menu Baru', image_url: '/uploads/menu-baru.jpg' })).toBe('/uploads/menu-baru.jpg')
    expect(getMenuImageUrl({ name: 'Menu Baru', image_url: 'http://localhost:8000/storage/images/menus/menu-baru.jpg' })).toBeNull()
  })

  it('has a physical public asset for every local mapping', () => {
    for (const imageUrl of new Set(Object.values(MENU_IMAGE_MAP))) {
      expect(existsSync(resolve(process.cwd(), 'public', imageUrl.slice(1))), imageUrl).toBe(true)
    }
  })

  it('shows the existing fallback if a menu image fails to load', () => {
    render(<MenuImage menu={{ name: 'Menu Baru', image_url: '/uploads/menu-baru.jpg' }} />)
    fireEvent.error(screen.getByRole('img', { name: 'Menu Baru' }))
    expect(screen.getByText('Exito')).toBeInTheDocument()
  })
})
