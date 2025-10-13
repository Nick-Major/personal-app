// personal-app-frontend/src/services/brigadierService.js
import api from './api';

export const brigadierService = {
    // Получить всех бригадиров
    async getAllBrigadiers() {
        try {
            const response = await api.get('/users/brigadiers');
            return response.data;
        } catch (error) {
            console.error('Error in getAllBrigadiers:', error);
            throw error;
        }
    },

    // Получить доступных бригадиров на дату
    async getAvailableBrigadiers(date) {
        try {
            const response = await api.get('/brigadiers/available', {
                params: { date }
            });
            return response.data;
        } catch (error) {
            console.error('Error in getAvailableBrigadiers:', error);
            throw error;
        }
    },

    // Создать назначение бригадира
    async createAssignment(assignmentData) {
        try {
            // Диагностическое логирование запроса
            console.info('[brigadierService.createAssignment] POST /brigadier-assignments payload:', assignmentData);
            const response = await api.post('/brigadier-assignments', assignmentData);
            console.info('[brigadierService.createAssignment] Response:', {
                status: response.status,
                data: response.data
            });
            return response.data;
        } catch (error) {
            // Расширенное диагностическое логирование ошибок
            const status = error?.response?.status;
            const data = error?.response?.data;
            const headers = error?.response?.headers;
            const config = error?.config ? {
                url: error.config.url,
                method: error.config.method,
                headers: error.config.headers,
                data: error.config.data
            } : undefined;
            console.error('[brigadierService.createAssignment] Error:', { status, data, headers, config, message: error?.message });
            throw error;
        }
    },

    // Получить все назначения
    async getAssignments() {
        try {
            const response = await api.get('/brigadier-assignments');
            return response.data;
        } catch (error) {
            console.error('Error in getAssignments:', error);
            throw error;
        }
    },

    // Получить мои назначения (как инициатора)
    async getMyAssignments() {
        try {
            const response = await api.get('/my/brigadier-assignments');
            return response.data;
        } catch (error) {
            console.error('Error in getMyAssignments:', error);
            throw error;
        }
    },

    // Подтвердить назначение (для бригадира)
    async confirmAssignment(assignmentId) {
        try {
            const response = await api.post(`/brigadier-assignments/${assignmentId}/confirm`);
            return response.data;
        } catch (error) {
            console.error('Error in confirmAssignment:', error);
            throw error;
        }
    },

    // Отклонить назначение (для бригадира)
    async rejectAssignment(assignmentId, reason) {
        try {
            const response = await api.post(`/brigadier-assignments/${assignmentId}/reject`, {
                rejection_reason: reason
            });
            return response.data;
        } catch (error) {
            console.error('Error in rejectAssignment:', error);
            throw error;
        }
    },

    // Удалить назначение
    async deleteAssignment(assignmentId) {
        try {
            const response = await api.delete(`/brigadier-assignments/${assignmentId}`);
            return response.data;
        } catch (error) {
            console.error('Error in deleteAssignment:', error);
            throw error;
        }
    }
};
