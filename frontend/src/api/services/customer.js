import { get, post, postForm } from '../http'
export const customerApi = {
  table: (qrToken) => get(`/customer/tables/${encodeURIComponent(qrToken)}`),
  openSession: (qrToken, customerName) => post('/customer/sessions/open', { qr_token: qrToken, ...(customerName ? { customer_name: customerName } : {}) }),
  currentSession: () => get('/customer/sessions/current'),
  menus: () => get('/customer/menus'),
  validateCart: (items) => post('/customer/cart/validate', { items }),
  createOrder: (payload) => post('/customer/sessions/orders', payload),
  order: (publicToken) => get(`/customer/orders/${publicToken}`),
  cancelOrder: (publicToken, payload) => post(`/customer/orders/${publicToken}/cancel`, payload),
  paymentInfo: () => get('/customer/payment-info'),
  uploadPaymentProof: (publicToken, file) => {
    const body = new FormData()
    body.append('proof', file)
    return postForm(`/customer/orders/${publicToken}/payment-proof`, body)
  },
}
