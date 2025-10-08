// personal-app-frontend/src/services/workRequestService.js
import api from './api';

export const workRequestService = {
    async getMyRequests() {
        try {
            const response = await api.get('/api/my/work-requests');
            console.log('getMyRequests response:', response);
            return response.data;
        } catch (error) {
            console.error('Error in getMyRequests:', error);
            throw error;
        }
    },

    // Получить все заявки текущего пользователя (инициатора)
    async getMyRequests() {
        const response = await api.get('/api/my/work-requests');
        return response.data;
    },

    // Создать новую заявку (черновик)
    async createRequest(requestData) {
        const response = await api.post('/api/work-requests', requestData);
        return response.data;
    },

    // Обновить заявку
    async updateRequest(id, requestData) {
        const response = await api.put(`/api/work-requests/${id}`, requestData);
        return response.data;
    },

    // Опубликовать заявку (меняет статус на "published")
    async publishRequest(id) {
        const response = await api.post(`/api/work-requests/${id}/publish`);
        return response.data;
    },

    // Получить заявки по статусу
    async getRequestsByStatus(status) {
        const response = await api.get(`/api/work-requests/status/${status}`);
        return response.data;
    },

    // Получить конкретную заявку
    async getRequest(id) {
        const response = await api.get(`/api/work-requests/${id}`);
        return response.data;
    },

    // Удалить заявку
    async deleteRequest(id) {
        const response = await api.delete(`/api/work-requests/${id}`);
        return response.data;
    }
};
