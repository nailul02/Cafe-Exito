import { get, post, put } from '../http'
export const authApi = {
  login: (login, password) => post('/auth/login', { login, password }),
  logout: () => post('/auth/logout'),
  me: () => get('/auth/me'),
  updateProfile: (payload) => put('/auth/me', payload),
  changePassword: (payload) => post('/auth/change-password', payload),
}
