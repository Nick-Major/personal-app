// personal-app-frontend/src/services/brigadierService.js
import api from './api';

export const brigadierService = {
    // Получить всех бригадиров
    async getAllBrigadiers() {
        try {
            const response = await api.get('/api/users/brigadiers');
            return response.data;
        } catch (error) {
            console.error('Error in getAllBrigadiers:', error);
            throw error;
        }
    },

    // Получить доступных бригадиров на дату
    async getAvailableBrigadiers(date) {
        try {
            const response = await api.get('/api/brigadiers/available', {
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
            const response = await api.post('/api/brigadier-assignments', assignmentData);
            return response.data;
        } catch (error) {
            console.error('Error in createAssignment:', error);
            throw error;
        }
    },

    // Получить все назначения
    async getAssignments() {
        try {
            const response = await api.get('/api/brigadier-assignments');
            return response.data;
        } catch (error) {
            console.error('Error in getAssignments:', error);
            throw error;
        }
    },

    // Получить мои назначения (как инициатора)
    async getMyAssignments() {
        try {
            const response = await api.get('/api/my/brigadier-assignments');
            return response.data;
        } catch (error) {
            console.error('Error in getMyAssignments:', error);
            throw error;
        }
    },

    // Подтвердить назначение (для бригадира)
    async confirmAssignment(assignmentId) {
        try {
            const response = await api.post(`/api/brigadier-assignments/${assignmentId}/confirm`);
            return response.data;
        } catch (error) {
            console.error('Error in confirmAssignment:', error);
            throw error;
        }
    },

    // Отклонить назначение (для бригадира)
    async rejectAssignment(assignmentId, reason) {
        try {
            const response = await api.post(`/api/brigadier-assignments/${assignmentId}/reject`, {
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
            const response = await api.delete(`/api/brigadier-assignments/${assignmentId}`);
            return response.data;
        } catch (error) {
            console.error('Error in deleteAssignment:', error);
            throw error;
        }
    }
};
