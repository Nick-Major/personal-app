import api from './api'

export const authService = {
  async login(email, password) {
    // First get CSRF cookie
    await api.get('/sanctum/csrf-cookie')
    
    // Then login
    const response = await api.post('/api/login', { email, password })
    return response.data
  },

  async logout() {
    await api.post('/api/logout')
  },

  async getUser() {
    const response = await api.get('/api/user')
    return response.data
  }
}
