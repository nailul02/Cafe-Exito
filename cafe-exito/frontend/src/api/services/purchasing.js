import { get, post, put, del } from '../http'
export const purchasingApi = {
  suppliers: () => get('/purchasing/suppliers'),
  createSupplier: (p) => post('/purchasing/suppliers', p),
  updateSupplier: (id, p) => put(`/purchasing/suppliers/${id}`, p),
  removeSupplier: (id) => del(`/purchasing/suppliers/${id}`),
  purchases: (params) => get('/purchasing/purchases', params),
  purchase: (id) => get(`/purchasing/purchases/${id}`),
  createPurchase: (p) => post('/purchasing/purchases', p),
  updatePurchase: (id, p) => put(`/purchasing/purchases/${id}`, p),
  placePurchase: (id) => post(`/purchasing/purchases/${id}/place`),
  cancelPurchase: (id) => post(`/purchasing/purchases/${id}/cancel`),
  createReceipt: (purchaseId, p) => post(`/purchasing/purchases/${purchaseId}/receipts`, p),
  confirmReceipt: (receiptId) => post(`/purchasing/receipts/${receiptId}/confirm`),
}
