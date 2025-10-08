// personal-app-frontend/src/services/authService.js
import api from './api';

export const authService = {
    async login(email, password) {
        // Sanctum требует сначала получить CSRF cookie
        await api.get('/sanctum/csrf-cookie');
        
        const response = await api.post('/api/login', { email, password });
        return response.data;
    },

    async logout() {
        await api.post('/api/logout');
    },

    async getUser() {
        const response = await api.get('/api/user');
        return response.data;
    }
};
