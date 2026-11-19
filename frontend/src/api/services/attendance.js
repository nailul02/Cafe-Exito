import { get, postForm } from '../http'

export const attendanceApi = {
  todayStatus: () => get('/attendance/today-status'),
  checkIn: (payload) => postForm('/attendance/check-in', payload),
  checkOut: (payload) => postForm('/attendance/check-out', payload),
  myHistory: (params) => get('/attendance/my-history', params),
  list: (params) => get('/attendance/list', params),
}
