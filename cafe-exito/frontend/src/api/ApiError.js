/** Galat API yang sudah dinormalisasi: { status, code, message, errors, details }. */
export class ApiError extends Error {
  constructor({ status = 0, code = 'UNKNOWN', message = 'Terjadi kesalahan.', errors = null, details = null } = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
    this.errors = errors
    this.details = details
  }
}
