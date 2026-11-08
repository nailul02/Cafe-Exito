import { get, post } from '../http'
export const customerApi = {
  table: (qrToken) => get(`/customer/tables/${qrToken}`),
  menus: () => get('/customer/menus'),
  validateCart: (items) => post('/customer/cart/validate', { items }),
  createOrder: (payload) => post('/customer/orders', payload),
  order: (publicToken) => get(`/customer/orders/${publicToken}`),
  cancelOrder: (publicToken) => post(`/customer/orders/${publicToken}/cancel`),
  paymentInfo: () => get('/customer/payment-info'),
}
