import { get, post } from '../http'
export const stationApi = {
  dashboard: (station) => get(`/station/${station}/dashboard`),
  tickets: (station, params) => get(`/station/${station}/tickets`, params),
  ticket: (station, id) => get(`/station/${station}/tickets/${id}`),
  start: (station, id) => post(`/station/${station}/tickets/${id}/start`),
  complete: (station, id) => post(`/station/${station}/tickets/${id}/complete`),
}
