import { Navigate, Route, Routes } from 'react-router-dom'
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
import ProductionDashboardPage from './pages/station/ProductionDashboardPage'
import ManagerDashboardPage from './pages/manager/ManagerDashboardPage'
import MenusPage from './pages/manager/MenusPage'
import UsersPage from './pages/manager/UsersPage'
import ManagerReportsPage from './pages/manager/ManagerReportsPage'
import ManagerAttendancePage from './pages/manager/ManagerAttendancePage'
import OwnerDashboardPage from './pages/owner/OwnerDashboardPage'
import OwnerReportsPage from './pages/owner/OwnerReportsPage'
import OwnerAttendancePage from './pages/owner/OwnerAttendancePage'
import EmployeeAccountsPage from './pages/owner/EmployeeAccountsPage'
import StaffAttendancePage from './pages/attendance/StaffAttendancePage'
import RefundsPage from './pages/shared/RefundsPage'
import OwnerOrderCancellationPage from './pages/owner/OwnerOrderCancellationPage'

export default function App() {
  return (
    <Routes>
      {/* Login mandiri staf */}
      <Route path="/login" element={<LoginPage />} />

      {/* Halaman menu pelanggan juga menjadi beranda utama dan tidak memerlukan login. */}
      <Route path="/" element={<CustomerLayout />}>
        <Route index element={<CatalogPage allowNoTable />} />
        <Route path="order">
          <Route index element={<QrEntryPage />} />
          <Route path="table" element={<TableNumberPage />} />
          <Route path="menu" element={<CatalogPage />} />
          <Route path="cart" element={<CartPage />} />
          <Route path="payment" element={<PaymentMethodPage />} />
          <Route path="done" element={<OrderConfirmationPage />} />
          <Route path="status/:token" element={<OrderStatusPage />} />
        </Route>
      </Route>

      {/* Kasir */}
      <Route element={<ProtectedRoute roles={['kasir']} />}>
        <Route element={<StationLayout />}>
          <Route path="/cashier" element={<CashierDashboardPage />} />
          <Route path="/cashier/orders" element={<CashierOrdersPage />} />
          <Route path="/cashier/orders/:id" element={<TransactionDetailPage />} />
          <Route path="/cashier/refunds" element={<RefundsPage />} />
          <Route path="/cashier/profile" element={<ProfilePage />} />
        </Route>
      </Route>

      {/* Barista & Kitchen: unified production route */}
      <Route element={<ProtectedRoute roles={['barista_kitchen', 'barista', 'kitchen']} />}>
        <Route element={<StationLayout />}>
          <Route path="/production" element={<ProductionDashboardPage />} />
          <Route path="/production/profile" element={<ProfilePage />} />
        </Route>
      </Route>

      {/* Absensi (kasir, barista_kitchen, manager) */}
      <Route element={<ProtectedRoute roles={['kasir', 'barista_kitchen', 'barista', 'kitchen', 'manager']} />}>
        <Route element={<StationLayout />}>
          <Route path="/attendance" element={<StaffAttendancePage />} />
        </Route>
      </Route>

      {/* Manager */}
      <Route element={<ProtectedRoute roles={['manager']} />}>
        <Route element={<ManagementLayout />}>
          <Route path="/manager" element={<ManagerDashboardPage />} />
          <Route path="/manager/production" element={<ProductionDashboardPage />} />
          <Route path="/manager/menus" element={<MenusPage />} />
          <Route path="/manager/users" element={<UsersPage />} />
          <Route path="/manager/reports" element={<ManagerReportsPage />} />
          <Route path="/manager/attendance" element={<ManagerAttendancePage />} />
          <Route path="/manager/refund-conflicts" element={<RefundsPage />} />
          <Route path="/manager/profile" element={<ProfilePage surface="white" />} />
        </Route>
      </Route>

      {/* Owner */}
      <Route element={<ProtectedRoute roles={['owner']} />}>
        <Route element={<ManagementLayout />}>
          <Route path="/owner" element={<OwnerDashboardPage />} />
          <Route path="/owner/production" element={<ProductionDashboardPage />} />
          <Route path="/owner/orders/cancellations" element={<OwnerOrderCancellationPage />} />
          <Route path="/owner/refunds" element={<RefundsPage />} />
          <Route path="/owner/reports" element={<OwnerReportsPage />} />
          <Route path="/owner/attendance" element={<OwnerAttendancePage />} />
          <Route path="/owner/users" element={<EmployeeAccountsPage />} />
          <Route path="/owner/profile" element={<ProfilePage surface="white" />} />
        </Route>
      </Route>

      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  )
}
