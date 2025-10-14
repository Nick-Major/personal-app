import React, { useState, useEffect } from 'react';
import { executorService } from '../../services/executorService';
import { useAuth } from '../../context/AuthContext';
import './Dashboard.css';

const ExecutorDashboard = () => {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState('shifts');
  const [shifts, setShifts] = useState([]);
  const [activeShifts, setActiveShifts] = useState([]);
  const [pendingAssignments, setPendingAssignments] = useState([]);
  const [brigadierRequests, setBrigadierRequests] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoading(true);
      
      // Загружаем данные в зависимости от роли
      const [shiftsData, activeShiftsData] = await Promise.all([
        executorService.getMyShifts(),
        executorService.getActiveShifts()
      ]);

      setShifts(shiftsData.data || shiftsData);
      setActiveShifts(activeShiftsData);

      // Если пользователь может быть бригадиром, загружаем соответствующие данные
      if (user?.executor_role && user.executor_role !== 'executor') {
        const [assignmentsData, requestsData] = await Promise.all([
          executorService.getPendingAssignments(),
          executorService.getBrigadierRequests()
        ]);

        setPendingAssignments(assignmentsData);
        setBrigadierRequests(requestsData);
      }
    } catch (error) {
      console.error('Ошибка загрузки данных:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleStartShift = async (shiftId) => {
    try {
      await executorService.startShift(shiftId);
      await loadDashboardData();
    } catch (error) {
      console.error('Ошибка начала смены:', error);
      alert('Не удалось начать смену');
    }
  };

  const handleEndShift = async (shiftId) => {
    try {
      await executorService.endShift(shiftId);
      await loadDashboardData();
    } catch (error) {
      console.error('Ошибка завершения смены:', error);
      alert('Не удалось завершить смену');
    }
  };

  const handleConfirmAssignment = async (assignmentId) => {
    try {
      await executorService.confirmAssignment(assignmentId);
      await loadDashboardData();
      alert('Назначение подтверждено');
    } catch (error) {
      console.error('Ошибка подтверждения:', error);
      alert('Не удалось подтвердить назначение');
    }
  };

  const handleRejectAssignment = async (assignmentId) => {
    const reason = prompt('Укажите причину отказа:');
    if (reason) {
      try {
        await executorService.rejectAssignment(assignmentId, reason);
        await loadDashboardData();
        alert('Назначение отклонено');
      } catch (error) {
        console.error('Ошибка отклонения:', error);
        alert('Не удалось отклонить назначение');
      }
    }
  };

  if (loading) {
    return (
      <div className="executor-dashboard">
        <div className="loading">Загрузка...</div>
      </div>
    );
  }

  return (
    <div className="executor-dashboard">
      {/* УБРАЛИ ЗАГОЛОВОК С ИНФОРМАЦИЕЙ О ПОЛЬЗОВАТЕЛЕ - теперь это в Header */}
      
      {/* Навигация по вкладкам */}
      <div className="tabs">
        <button 
          className={activeTab === 'shifts' ? 'active' : ''}
          onClick={() => setActiveTab('shifts')}
        >
          Мои смены
        </button>
        
        {user?.executor_role && user.executor_role !== 'executor' && (
          <>
            <button 
              className={activeTab === 'assignments' ? 'active' : ''}
              onClick={() => setActiveTab('assignments')}
            >
              Назначения {pendingAssignments.length > 0 && `(${pendingAssignments.length})`}
            </button>
            <button 
              className={activeTab === 'brigadier' ? 'active' : ''}
              onClick={() => setActiveTab('brigadier')}
            >
              Мои заявки
            </button>
          </>
        )}
      </div>

      {/* Содержимое вкладок */}
      <div className="tab-content">
        {/* Вкладка Смены */}
        {activeTab === 'shifts' && (
          <div className="shifts-section">
            <h3>Активные смены</h3>
            {activeShifts.length > 0 ? (
              <div className="shifts-list">
                {activeShifts.map(shift => (
                  <div key={shift.id} className="shift-card">
                    <div className="shift-info">
                      <h4>{shift.workRequest?.title || 'Смена'}</h4>
                      <p>Дата: {new Date(shift.work_date).toLocaleDateString('ru-RU')}</p>
                      <p>Специальность: {shift.specialty?.name}</p>
                      <p>Статус: <span className={`status ${shift.status}`}>{shift.status}</span></p>
                    </div>
                    <div className="shift-actions">
                      {shift.status === 'planned' && (
                        <button 
                          onClick={() => handleStartShift(shift.id)}
                          className="btn btn-primary"
                        >
                          Начать смену
                        </button>
                      )}
                      {shift.status === 'active' && (
                        <button 
                          onClick={() => handleEndShift(shift.id)}
                          className="btn btn-secondary"
                        >
                          Завершить смену
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p>Нет активных смен</p>
            )}

            <h3>История смен</h3>
            {shifts.length > 0 ? (
              <div className="shifts-history">
                {shifts.map(shift => (
                  <div key={shift.id} className="shift-history-card">
                    <p><strong>Дата:</strong> {new Date(shift.work_date).toLocaleDateString('ru-RU')}</p>
                    <p><strong>Специальность:</strong> {shift.specialty?.name}</p>
                    <p><strong>Статус:</strong> <span className={`status ${shift.status}`}>{shift.status}</span></p>
                    {shift.shift_started_at && (
                      <p><strong>Начало:</strong> {new Date(shift.shift_started_at).toLocaleTimeString('ru-RU')}</p>
                    )}
                    {shift.shift_ended_at && (
                      <p><strong>Окончание:</strong> {new Date(shift.shift_ended_at).toLocaleTimeString('ru-RU')}</p>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <p>Нет завершенных смен</p>
            )}
          </div>
        )}

        {/* Вкладка Назначения бригадиром */}
        {activeTab === 'assignments' && (
          <div className="assignments-section">
            <h3>Назначения на подтверждение</h3>
            {pendingAssignments.length > 0 ? (
              <div className="assignments-list">
                {pendingAssignments.map(assignment => (
                  <div key={assignment.id} className="assignment-card">
                    <div className="assignment-info">
                      <h4>Назначение от {assignment.initiator?.full_name}</h4>
                      <p>Даты назначения:</p>
                      <ul>
                        {assignment.assignment_dates?.map(date => (
                          <li key={date.id}>
                            {new Date(date.assignment_date).toLocaleDateString('ru-RU')}
                          </li>
                        ))}
                      </ul>
                      {assignment.can_create_requests && (
                        <p className="rights-info">✅ Может создавать заявки</p>
                      )}
                    </div>
                    <div className="assignment-actions">
                      <button 
                        onClick={() => handleConfirmAssignment(assignment.id)}
                        className="btn btn-success"
                      >
                        Подтвердить
                      </button>
                      <button 
                        onClick={() => handleRejectAssignment(assignment.id)}
                        className="btn btn-danger"
                      >
                        Отклонить
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p>Нет назначений на подтверждение</p>
            )}
          </div>
        )}

        {/* Вкладка Заявки бригадира */}
        {activeTab === 'brigadier' && (
          <div className="brigadier-section">
            <h3>Мои заявки как бригадира</h3>
            {brigadierRequests.length > 0 ? (
              <div className="requests-list">
                {brigadierRequests.map(request => (
                  <div key={request.id} className="request-card">
                    <h4>Заявка #{request.id}</h4>
                    <p><strong>Дата работы:</strong> {new Date(request.work_date).toLocaleDateString('ru-RU')}</p>
                    <p><strong>Специальность:</strong> {request.specialty?.name}</p>
                    <p><strong>Тип работ:</strong> {request.work_type?.name}</p>
                    <p><strong>Инициатор:</strong> {request.initiator?.full_name}</p>
                    <p><strong>Количество исполнителей:</strong> {request.workers_count}</p>
                    <div className="shifts-info">
                      <strong>Смены:</strong>
                      {request.shifts?.map(shift => (
                        <div key={shift.id} className="shift-mini">
                          {shift.user?.full_name} - {shift.status}
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p>Нет заявок где вы бригадир</p>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default ExecutorDashboard;
