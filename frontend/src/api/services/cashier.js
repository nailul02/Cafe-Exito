import { get, post, http } from '../http'
export const cashierApi = {
  dashboard: () => get('/cashier/dashboard'),
  orders: (params) => get('/cashier/orders', params),
  order: (id) => get(`/cashier/orders/${id}`),
  completeOrder: (id) => post(`/cashier/orders/${id}/complete`),
  verify: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/verify`, payload),
  reject: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/reject`, payload),
  receipt: (orderId) => get(`/cashier/transactions/${orderId}/receipt`),
  createPosOrder: (payload) => post('/cashier/pos/orders', payload),
  tables: () => get('/cashier/tables'),
  paymentProof: (paymentId) => http.get(`/cashier/payments/${paymentId}/proof`, { responseType: 'blob' }).then((r) => r.data),
}
