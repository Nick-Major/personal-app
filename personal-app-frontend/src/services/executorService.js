// personal-app-frontend/src/services/executorService.js
import api from './api';

export const executorService = {
  // === ОБЩИЕ МЕТОДЫ ===
  
  // Получить текущую роль пользователя
  async getCurrentRole() {
    const response = await api.get('/user');
    const user = response.data;
    
    // Здесь будет логика определения роли на основе данных пользователя
    // Пока используем базовую роль из permissions
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
  
  // === МЕТОДЫ БРИГАДИРА ===
  
  // Получить назначения на подтверждение
  async getPendingAssignments() {
    const response = await api.get('/my/brigadier-assignments');
    return response.data;
  },
  
  // Подтвердить назначение
  async confirmAssignment(assignmentId) {
    const response = await api.post(`/brigadier-assignments/${assignmentId}/confirm`);
    return response.data;
  },
  
  // Отклонить назначение
  async rejectAssignment(assignmentId, reason) {
    const response = await api.post(`/brigadier-assignments/${assignmentId}/reject`, {
      rejection_reason: reason
    });
    return response.data;
  },
  
  // Получить заявки где я бригадир
  async getBrigadierRequests() {
    const response = await api.get('/my/work-requests?role=brigadier');
    return response.data;
  }
};
