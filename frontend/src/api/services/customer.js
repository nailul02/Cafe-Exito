import { get, post, postForm } from '../http'
export const customerApi = {
  table: (qrToken) => get(`/customer/tables/${qrToken}`),
  menus: () => get('/customer/menus'),
  validateCart: (items) => post('/customer/cart/validate', { items }),
  createOrder: (payload) => post('/customer/orders', payload),
  order: (publicToken) => get(`/customer/orders/${publicToken}`),
  cancelOrder: (publicToken, payload) => post(`/customer/orders/${publicToken}/cancel`, payload),
  paymentInfo: () => get('/customer/payment-info'),
  uploadPaymentProof: (publicToken, file) => {
    const body = new FormData()
    body.append('proof', file)
    return postForm(`/customer/orders/${publicToken}/payment-proof`, body)
  },
}
