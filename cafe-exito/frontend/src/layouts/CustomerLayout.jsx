import { Outlet } from 'react-router-dom'

/** Layout pelanggan: satu kolom mobile (412 px di Figma), tanpa sidebar. */
export default function CustomerLayout() {
  return (
    <div className="mx-auto flex min-h-full max-w-md flex-col bg-white shadow-[0_0_40px_rgba(52,33,30,.08)]">
      <Outlet />
    </div>
  )
}
