import { get, post, put, patch } from '../http'
export const usersApi = {
  list: () => get('/admin/users'),
  create: (p) => post('/admin/users', p),
  update: (id, p) => put(`/admin/users/${id}`, p),
  setStatus: (id, isActive) => patch(`/admin/users/${id}/status`, { is_active: isActive }),
  permissions: (id) => get(`/admin/users/${id}/permissions`),
  savePermissions: (id, permissions) => put(`/admin/users/${id}/permissions`, { permissions }),
  tables: () => get('/admin/tables'),
  createTable: (p) => post('/admin/tables', p),
  regenerateQr: (id) => post(`/admin/tables/${id}/regenerate-qr`),
}
