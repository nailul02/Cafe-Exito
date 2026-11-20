import { get, post } from '../http'
export const ordersApi = { list: (params) => get('/orders', params), cancelItem: (orderId,payload) => post(`/orders/${orderId}/items/cancel`,payload) }
