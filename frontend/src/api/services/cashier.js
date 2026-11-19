import { get, post, getBlob } from '../http'
export const cashierApi = {
  dashboard: () => get('/cashier/dashboard'),
  orders: (params) => get('/cashier/orders', params),
  order: (id) => get(`/cashier/orders/${id}`),
  completeOrder: (id) => post(`/cashier/orders/${id}/complete`),
  verify: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/verify`, payload),
  reject: (paymentId, payload = {}) => post(`/cashier/payments/${paymentId}/reject`, payload),
  paymentProof: (paymentId) => getBlob(`/cashier/payments/${paymentId}/proof`),
  receipt: (orderId) => get(`/cashier/transactions/${orderId}/receipt`),
  createPosOrder: (payload) => post('/cashier/pos/orders', payload),
  tables: () => get('/cashier/tables'),
}
