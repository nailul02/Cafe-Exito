// Titik tunggal akses data. Komponen UI hanya mengimpor dari sini.
// VITE_USE_MOCK=true  -> src/mocks (tanpa backend)
// VITE_USE_MOCK=false -> src/api/services (Axios ke Laravel)
import { env } from '../config/env'
import * as real from './services'
import * as mock from '../mocks'

const impl = env.useMock ? mock : real

export const authApi = impl.authApi
export const customerApi = impl.customerApi
export const cashierApi = impl.cashierApi
export const stationApi = impl.stationApi
export const menusApi = impl.menusApi
export const inventoryApi = impl.inventoryApi
export const purchasingApi = impl.purchasingApi
export const usersApi = impl.usersApi
export const reportsApi = impl.reportsApi
export { ApiError } from './ApiError'
