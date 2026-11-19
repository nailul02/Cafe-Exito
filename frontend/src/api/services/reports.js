import { get } from '../http'
export const reportsApi = {
  ownerDashboard: () => get('/dashboard/owner'),
  managerDashboard: () => get('/dashboard/manager'),
  sales: (params) => get('/reports/sales', params),
  stockUsage: (params) => get('/reports/stock-usage', params),
  transactions: (params) => get('/transactions', params),
  orders: (params) => get('/orders', params),
  auditLogs: (params) => get('/audit-logs', params),
  alerts: () => get('/staff/alerts'),
}
