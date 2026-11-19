import { get, post, put, del } from '../http'
export const inventoryApi = {
  ingredients: (params) => get('/inventory/ingredients', params),
  createIngredient: (payload) => post('/inventory/ingredients', payload),
  updateIngredient: (id, payload) => put(`/inventory/ingredients/${id}`, payload),
  removeIngredient: (id) => del(`/inventory/ingredients/${id}`),
  summary: () => get('/inventory/summary'),
  movements: (params) => get('/inventory/stock-movements', params),
  adjust: (payload) => post('/inventory/stock-adjustments', payload),
}
