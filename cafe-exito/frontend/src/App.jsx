import { Navigate, Route, Routes } from 'react-router-dom'
import { useAuth } from './context/AuthContext'
import { HOME_BY_ROLE } from './lib/status'
import CustomerLayout from './layouts/CustomerLayout'
import StationLayout from './layouts/StationLayout'
import ManagementLayout from './layouts/ManagementLayout'
import ProtectedRoute from './layouts/ProtectedRoute'
import LoginPage from './pages/auth/LoginPage'
import NotFoundPage from './pages/shared/NotFoundPage'
import ProfilePage from './pages/shared/ProfilePage'
import QrEntryPage from './pages/customer/QrEntryPage'
import TableNumberPage from './pages/customer/TableNumberPage'
import CatalogPage from './pages/customer/CatalogPage'
import CartPage from './pages/customer/CartPage'
import PaymentMethodPage from './pages/customer/PaymentMethodPage'
import OrderConfirmationPage from './pages/customer/OrderConfirmationPage'
import OrderStatusPage from './pages/customer/OrderStatusPage'
import CashierDashboardPage from './pages/cashier/CashierDashboardPage'
import CashierOrdersPage from './pages/cashier/CashierOrdersPage'
import TransactionDetailPage from './pages/cashier/TransactionDetailPage'
import StationDashboardPage from './pages/station/StationDashboardPage'
import StationQueuePage from './pages/station/StationQueuePage'
import ManagerDashboardPage from './pages/manager/ManagerDashboardPage'
import MenusPage from './pages/manager/MenusPage'
import InventoryPage from './pages/manager/InventoryPage'
import PurchasingPage from './pages/manager/PurchasingPage'
import UsersPage from './pages/manager/UsersPage'
import ManagerReportsPage from './pages/manager/ManagerReportsPage'
import OwnerDashboardPage from './pages/owner/OwnerDashboardPage'
import OwnerReportsPage from './pages/owner/OwnerReportsPage'

function Home() {
  const { user, booting } = useAuth()
  if (booting) return null
  return <Navigate to={user ? HOME_BY_ROLE[user.role.code] : '/order'} replace />
}

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/login" element={<LoginPage />} />

      {/* Pelanggan: tanpa login */}
      <Route path="/order" element={<CustomerLayout />}>
        <Route index element={<QrEntryPage />} />
        <Route path="table" element={<TableNumberPage />} />
        <Route path="menu" element={<CatalogPage />} />
        <Route path="cart" element={<CartPage />} />
        <Route path="payment" element={<PaymentMethodPage />} />
        <Route path="done" element={<OrderConfirmationPage />} />
        <Route path="status/:token" element={<OrderStatusPage />} />
      </Route>

      {/* Kasir */}
      <Route element={<ProtectedRoute roles={['kasir']} />}>
        <Route element={<StationLayout />}>
          <Route path="/cashier" element={<CashierDashboardPage />} />
          <Route path="/cashier/orders" element={<CashierOrdersPage />} />
          <Route path="/cashier/orders/:id" element={<TransactionDetailPage />} />
          <Route path="/cashier/profile" element={<ProfilePage />} />
        </Route>
      </Route>

      {/* Barista & Kitchen: komponen yang sama, stasiun sebagai parameter */}
      {['barista', 'kitchen'].map((station) => (
        <Route key={station} element={<ProtectedRoute roles={[station]} />}>
          <Route element={<StationLayout />}>
            <Route path={`/${station}`} element={<StationDashboardPage station={station} />} />
            <Route path={`/${station}/queue`} element={<StationQueuePage station={station} />} />
            <Route path={`/${station}/profile`} element={<ProfilePage />} />
          </Route>
        </Route>
      ))}

      {/* Manager (role API: admin) */}
      <Route element={<ProtectedRoute roles={['admin']} />}>
        <Route element={<ManagementLayout />}>
          <Route path="/manager" element={<ManagerDashboardPage />} />
          <Route path="/manager/menus" element={<MenusPage />} />
          <Route path="/manager/inventory" element={<InventoryPage />} />
          <Route path="/manager/purchasing" element={<PurchasingPage />} />
          <Route path="/manager/users" element={<UsersPage />} />
          <Route path="/manager/reports" element={<ManagerReportsPage />} />
          <Route path="/manager/profile" element={<ProfilePage surface="white" />} />
        </Route>
      </Route>

      {/* Owner */}
      <Route element={<ProtectedRoute roles={['owner']} />}>
        <Route element={<ManagementLayout />}>
          <Route path="/owner" element={<OwnerDashboardPage />} />
          <Route path="/owner/reports" element={<OwnerReportsPage />} />
          <Route path="/owner/profile" element={<ProfilePage surface="white" />} />
        </Route>
      </Route>

      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  )
}
