import { get, post } from '../http'
export const cashierApi = {
  dashboard: () => get('/cashier/dashboard'),
  orders: (params) => get('/cashier/orders', params),
  order: (id) => get(`/cashier/orders/${id}`),
  verify: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/verify`, payload),
  reject: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/reject`, payload),
  receipt: (orderId) => get(`/cashier/transactions/${orderId}/receipt`),
}
