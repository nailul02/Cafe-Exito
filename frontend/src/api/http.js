import axios from 'axios'
import { env } from '../config/env'
import { tokenStorage } from './tokenStorage'
import { ApiError } from './ApiError'

export const http = axios.create({
  baseURL: env.apiBaseUrl,
  timeout: 30000,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = tokenStorage.get()
  if (token && !config.url?.startsWith('/customer/')) config.headers.Authorization = `Bearer ${token}`
  const sessionToken = tokenStorage.getTableSession()
  if (sessionToken && config.url?.startsWith('/customer/') && !config.url.startsWith('/customer/sessions/open')) {
    config.headers['X-Table-Session'] = sessionToken
  }
  return config
})

http.interceptors.response.use(
  (res) => res,
  (err) => {
    const res = err.response
    const body = res?.data || {}
    if (res?.status === 401 && tokenStorage.get()) {
      tokenStorage.clear()
      window.dispatchEvent(new Event('auth:expired'))
    }
    if (res?.status === 401 && err.config?.url?.startsWith('/customer/')) tokenStorage.setTableSession(null)
    throw new ApiError({
      status: res?.status ?? 0,
      code: body.error?.code || (res ? 'HTTP_' + res.status : 'NETWORK_ERROR'),
      message: body.message || (res ? 'Permintaan gagal.' : 'Tidak dapat terhubung ke server.'),
      errors: body.errors || null,
      details: body.error?.details || null,
    })
  },
)

// Envelope sukses: { success, message, data, meta }. Service hanya menerima `data` (atau {data, meta}).
export const get = (url, params) => http.get(url, { params }).then((r) => r.data.data)
export const getPage = (url, params) => http.get(url, { params }).then((r) => ({ data: r.data.data, meta: r.data.meta }))
export const post = (url, body) => http.post(url, body).then((r) => r.data.data)
export const postForm = (url, body) => http.post(url, body, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data.data)
export const getBlob = (url) => http.get(url, { responseType: 'blob' }).then((r) => r.data)
export const put = (url, body) => http.put(url, body).then((r) => r.data.data)
export const patch = (url, body) => http.patch(url, body).then((r) => r.data.data)
export const del = (url) => http.delete(url).then((r) => r.data.data)
