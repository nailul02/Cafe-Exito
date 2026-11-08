import { get, post, put, del, patch } from '../http'
export const menusApi = {
  list: (params) => get('/admin/menus', params),
  show: (id) => get(`/admin/menus/${id}`),
  create: (payload) => post('/admin/menus', payload),
  update: (id, payload) => put(`/admin/menus/${id}`, payload),
  remove: (id) => del(`/admin/menus/${id}`),
  setAvailability: (id, isAvailable) => patch(`/admin/menus/${id}/availability`, { is_available: isAvailable }),
  recipe: (id) => get(`/admin/menus/${id}/recipe`),
  saveRecipe: (id, items) => put(`/admin/menus/${id}/recipe`, { items }),
  categories: () => get('/admin/menu-categories'),
}
