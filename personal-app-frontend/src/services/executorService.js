// personal-app-frontend/src/services/executorService.js
import api from './api';

export const executorService = {
  // === ОБЩИЕ МЕТОДЫ ===

  // Получить текущую роль пользователя
  async getCurrentRole() {
    const response = await api.get('/user');
    const user = response.data;
    return user.roles?.[0]?.name || 'executor';
  },

  // === МЕТОДЫ ИСПОЛНИТЕЛЯ ===

  // Получить мои смены
  async getMyShifts() {
    const response = await api.get('/my/shifts');
    return response.data;
  },

  // Получить активные смены
  async getActiveShifts() {
    const response = await api.get('/my/shifts/active');
    return response.data;
  },

  // Начать смену
  async startShift(shiftId, location = null) {
    const data = location ? { location } : {};
    const response = await api.post(`/my/shifts/${shiftId}/start`, data);
    return response.data;
  },

  // Завершить смену
  async endShift(shiftId, data = {}) {
    const response = await api.post(`/my/shifts/${shiftId}/end`, data);
    return response.data;
  },

  // === МЕТОДЫ БРИГАДИРА (ИСПОЛНИТЕЛЬСКАЯ ЧАСТЬ) ===

  // Получить ожидающие подтверждения назначения
  async getPendingAssignments() {
    const response = await api.get('/my/brigadier-assignments/pending');
    return response.data;
  },

  // Получить подтвержденные назначения  
  async getConfirmedAssignments() {
    const response = await api.get('/my/brigadier-assignments/confirmed');
    return response.data;
  },

  // Подтвердить назначение
  async confirmAssignment(assignmentId) {
    const response = await api.post(`/my/brigadier-assignments/${assignmentId}/confirm`);
    return response.data;
  },

  // Отклонить назначение
  async rejectAssignment(assignmentId, reason) {
    const response = await api.post(`/my/brigadier-assignments/${assignmentId}/reject`, {
      rejection_reason: reason
    });
    return response.data;
  },

  // Получить заявки где я бригадир
  async getBrigadierRequests() {
    const response = await api.get('/my/work-requests');
    return response.data;
  },

  // === МЕТОДЫ ДЛЯ ИНИЦИАТОРА (УПРАВЛЕНИЕ БРИГАДИРАМИ) ===

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
      console.info('[executorService.createAssignment] POST /brigadier-assignments payload:', assignmentData);
      const response = await api.post('/brigadier-assignments', assignmentData);
      console.info('[executorService.createAssignment] Response:', {
        status: response.status,
        data: response.data
      });
      return response.data;
    } catch (error) {
      const status = error?.response?.status;
      const data = error?.response?.data;
      const headers = error?.response?.headers;
      const config = error?.config ? {
        url: error.config.url,
        method: error.config.method,
        headers: error.config.headers,
        data: error.config.data
      } : undefined;
      console.error('[executorService.createAssignment] Error:', { status, data, headers, config, message: error?.message });
      throw error;
    }
  },

  // Получить все назначения (для инициатора)
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
