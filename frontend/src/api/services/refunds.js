import { get, post, postForm, http } from '../http'
export const refundsApi = {
  list: () => get('/refunds'),
  request: (payload) => post('/refunds', payload),
  decide: (id, decision, reason) => post(`/refunds/${id}/decision`, { decision, reason }),
  withdraw: (id, reason) => post(`/refunds/${id}/withdraw`, { reason }),
  revoke: (id, reason) => post(`/refunds/${id}/revoke`, { reason }),
  execute: (id, data) => postForm(`/refunds/${id}/execute`, data),
  notifications: () => get('/notifications'),
  readNotification: (id) => post(`/notifications/${id}/read`),
  conflictNote: (id, data) => postForm(`/refunds/${id}/conflict-notes`, data),
  verifyConflict: (id) => post(`/refund-conflict-notes/${id}/verify`),
  downloadProof: async (id) => { const r=await http.get(`/refunds/${id}/proof`,{responseType:'blob'}); const url=URL.createObjectURL(r.data); window.open(url,'_blank','noopener'); setTimeout(()=>URL.revokeObjectURL(url),60000) },
  downloadConflictProof: async (id) => { const r=await http.get(`/refund-conflict-notes/${id}/proof`,{responseType:'blob'}); const url=URL.createObjectURL(r.data); window.open(url,'_blank','noopener'); setTimeout(()=>URL.revokeObjectURL(url),60000) },
}
