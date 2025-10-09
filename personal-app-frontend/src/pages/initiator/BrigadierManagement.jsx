// personal-app-frontend/src/pages/initiator/BrigadierManagement.jsx
import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import './BrigadierManagement.css';
import DatePicker from 'react-datepicker';
import { registerLocale, setDefaultLocale } from 'react-datepicker';
import { ru } from 'date-fns/locale/ru';
import 'react-datepicker/dist/react-datepicker.css';
import { brigadierService } from '../../services/brigadierService';
import { useAuth } from '../../context/AuthContext';

// Регистрируем русскую локаль
registerLocale('ru', ru);
setDefaultLocale('ru');

const BrigadierManagement = () => {
  const { user } = useAuth();
  const [viewMode, setViewMode] = useState('date'); // 'date' или 'period'
  const [selectedDate, setSelectedDate] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [filterMode, setFilterMode] = useState('my'); // 'my' или 'all'
  const [showAssignmentModal, setShowAssignmentModal] = useState(false);
  const [selectedExecutor, setSelectedExecutor] = useState(null);
  const [assignmentComment, setAssignmentComment] = useState('');
  const [selectedDates, setSelectedDates] = useState([]);
  const [loading, setLoading] = useState(true);
  const [availableExecutors, setAvailableExecutors] = useState([]);
  const [assignments, setAssignments] = useState([]);

  useEffect(() => {
    const today = new Date().toISOString().split('T')[0];
    setSelectedDate(today);

    // Устанавливаем период по умолчанию (текущая неделя)
    const start = new Date();
    const end = new Date();
    end.setDate(end.getDate() + 7);
    setStartDate(start.toISOString().split('T')[0]);
    setEndDate(end.toISOString().split('T')[0]);

    loadAssignments();
    loadAvailableExecutors();
  }, []);

  const loadAssignments = async () => {
    try {
      setLoading(true);
      const data = await brigadierService.getAssignments();
      console.log('Loaded assignments:', data);
      
      // Правильно обрабатываем структуру ответа
      let assignmentsArray = [];
      if (Array.isArray(data)) {
        assignmentsArray = data;
      } else if (data && Array.isArray(data.data)) {
        assignmentsArray = data.data;
      }
      
      console.log('Assignments array:', assignmentsArray);
      setAssignments(assignmentsArray);
    } catch (error) {
      console.error('Error loading assignments:', error);
      alert('Ошибка при загрузке назначений');
      setAssignments([]);
    } finally {
      setLoading(false);
    }
  };

  const loadAvailableExecutors = async () => {
    try {
      const data = await brigadierService.getAllBrigadiers();
      console.log('Loaded brigadiers:', data);
      setAvailableExecutors(data);
    } catch (error) {
      console.error('Error loading executors:', error);
      alert('Ошибка при загрузке списка бригадиров');
    }
  };

  // Фильтрация назначений
  const filteredAssignments = (Array.isArray(assignments) ? assignments : assignments.data || 
  []).filter(assignment => {
    // Фильтр по инициатору
    if (filterMode === 'my' && assignment.initiator.id !== user.id) {
      return false;
    }

    // TODO: Добавить фильтрацию по дате после обновления структуры БД
    // Сейчас используем mock логику
    return true;
  });

  const handleAssignBrigadier = async () => {
    if (!selectedExecutor || selectedDates.length === 0) return;

    try {
      // Создаем ОДНО назначение с ПЕРВОЙ выбранной датой
      // TODO: Позже добавить поддержку множественных дат
      const response = await brigadierService.createAssignment({
        brigadier_id: selectedExecutor.id,
        initiator_id: user.id,
        assignment_date: selectedDates[0], // Берем первую дату
        status: 'pending'
      });

      console.log('Assignment created:', response);
      await loadAssignments(); // Перезагружаем список
      
      setShowAssignmentModal(false);
      setSelectedDates([]);
      setAssignmentComment('');
      setSelectedExecutor(null);
      
      alert('Бригадир успешно назначен! Ожидает подтверждения.');
    } catch (error) {
      console.error('Error assigning brigadier:', error);
      alert('Ошибка при назначении бригадира: ' + (error.response?.data?.error || error.message));
    }
  };

  const handleOpenAssignmentModal = (executor = null, assignment = null) => {
    if (assignment) {
      // Режим редактирования
      setSelectedExecutor(assignment.brigadier);
      // TODO: Загрузить даты из assignment.assignmentDates
      setAssignmentComment(assignment.comment);
    } else {
      // Режим создания
      setSelectedExecutor(executor);
      setSelectedDates([]);
      setAssignmentComment('');
    }
    setShowAssignmentModal(true);
  };

  const handleUnassignBrigadier = async (assignmentId) => {
    if (window.confirm('Вы уверены, что хотите отменить назначение?')) {
      try {
        await brigadierService.deleteAssignment(assignmentId);
        await loadAssignments(); // Перезагружаем список
        alert('Назначение отменено');
      } catch (error) {
        console.error('Error deleting assignment:', error);
        alert('Ошибка при отмене назначения');
      }
    }
  };

  // Функция для выбора даты
  const handleDateClick = (date) => {
    const dateString = date.toISOString().split('T')[0];
    setSelectedDates(prev => {
      const isSelected = prev.includes(dateString);
      if (isSelected) {
        return prev.filter(d => d !== dateString);
      } else {
        return [...prev, dateString].sort();
      }
    });
  };

  const getStatusDisplay = (status) => {
    const statusMap = {
      'pending': '⏳ Ожидает',
      'confirmed': '✅ Подтверждено',
      'rejected': '❌ Отклонено'
    };
    return statusMap[status] || status;
  };

  const getStatusColor = (status) => {
    const colors = {
      'pending': '#f39c12',
      'confirmed': '#27ae60',
      'rejected': '#e74c3c'
    };
    return colors[status] || '#7f8c8d';
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU');
  };

  if (loading) {
    return (
      <div className="brigadier-management">
        <div className="loading">Загрузка назначений...</div>
      </div>
    );
  }

  return (
    <div className="brigadier-management">
      <div className="page-header">
        <div className="header-content">
          <h1>Планирование бригадиров</h1>
          <Link to="/initiator/dashboard" className="back-link">← Назад к дашборду</Link>
        </div>
      </div>

      {/* Controls */}
      <div className="controls-panel">
        <div className="view-controls">
          <div className="control-group">
            <label>Режим просмотра:</label>
            <div className="radio-group">
              <label>
                <input
                  type="radio"
                  value="date"
                  checked={viewMode === 'date'}
                  onChange={(e) => setViewMode(e.target.value)}
                />
                По дате
              </label>
              <label>
                <input
                  type="radio"
                  value="period"
                  checked={viewMode === 'period'}
                  onChange={(e) => setViewMode(e.target.value)}
                />
                По периоду
              </label>
            </div>
          </div>

          <div className="control-group">
            {viewMode === 'date' ? (
              <>
                <label>Дата:</label>
                <input
                  type="date"
                  value={selectedDate}
                  onChange={(e) => setSelectedDate(e.target.value)}
                  className="date-input"
                />
              </>
            ) : (
              <>
                <label>Период:</label>
                <div className="date-range">
                  <input
                    type="date"
                    value={startDate}
                    onChange={(e) => setStartDate(e.target.value)}
                    className="date-input"
                  />
                  <span>—</span>
                  <input
                    type="date"
                    value={endDate}
                    onChange={(e) => setEndDate(e.target.value)}
                    className="date-input"
                  />
                </div>
              </>
            )}
          </div>

          <div className="control-group">
            <label>Фильтр:</label>
            <select
              value={filterMode}
              onChange={(e) => setFilterMode(e.target.value)}
              className="filter-select"
            >
              <option value="my">Мои назначения</option>
              <option value="all">Все назначения</option>
            </select>
          </div>
        </div>

        <button
          className="assign-new-btn"
          onClick={() => handleOpenAssignmentModal()}
        >
          + Новое назначение
        </button>
      </div>

      {/* Statistics */}
      <div className="stats-panel">
        <div className="stat-item">
          <span className="stat-number">
            {filteredAssignments.length}
          </span>
          <span className="stat-label">Всего назначений</span>
        </div>
        <div className="stat-item">
          <span className="stat-number" style={{color: '#f39c12'}}>
            {filteredAssignments.filter(a => a.status === 'pending').length}
          </span>
          <span className="stat-label">Ожидают подтверждения</span>
        </div>
        <div className="stat-item">
          <span className="stat-number" style={{color: '#27ae60'}}>
            {filteredAssignments.filter(a => a.status === 'confirmed').length}
          </span>
          <span className="stat-label">Подтверждено</span>
        </div>
        <div className="stat-item">
          <span className="stat-number" style={{color: '#e74c3c'}}>
            {filteredAssignments.filter(a => a.status === 'rejected').length}
          </span>
          <span className="stat-label">Отклонено</span>
        </div>
      </div>

      {/* Table */}
      <div className="table-container">
        <table className="assignments-table">
          <thead>
            <tr>
              <th>Бригадир</th>
              <th>Специализация</th>
              <th>Дата назначения</th>
              <th>Инициатор</th>
              <th>Статус</th>
              <th>Комментарий</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            {filteredAssignments.length === 0 ? (
              <tr>
                <td colSpan="7" className="no-data">
                  На выбранный период назначений не найдено
                </td>
              </tr>
            ) : (
              filteredAssignments.map(assignment => (
                <tr key={assignment.id}>
                  <td>
                    <div className="brigadier-info">
                      <strong>{assignment.brigadier?.name || 'N/A'}</strong>
                    </div>
                  </td>
                  <td>{assignment.brigadier?.specialization || 'N/A'}</td>
                  <td>
                    {formatDate(assignment.assignment_date)}
                  </td>
                  <td>{assignment.initiator?.name || 'N/A'}</td>
                  <td>
                    <span
                      className="status-badge"
                      style={{backgroundColor: getStatusColor(assignment.status)}}
                    >
                      {getStatusDisplay(assignment.status)}
                    </span>
                  </td>
                  <td>
                    <div className="comment-cell">
                      {assignment.rejection_reason || assignment.comment || '-'}
                    </div>
                  </td>
                  <td>
                    <div className="actions-cell">
                      {assignment.initiator?.id === user.id && (
                        <button
                          onClick={() => handleUnassignBrigadier(assignment.id)}
                          className="action-btn delete-btn"
                          title="Отменить назначение"
                        >
                          🗑️
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Assignment Modal */}
      {showAssignmentModal && (
        <div className="modal-overlay">
          <div className="modal">
            <div className="modal-header">
              <h3>
                {selectedExecutor ?
                  (selectedDates.length > 0 ? 'Редактирование назначения' : 'Новое назначение')
                  : 'Новое назначение'
                }
              </h3>
              <button
                onClick={() => setShowAssignmentModal(false)}
                className="close-btn"
              >
                ×
              </button>
            </div>

            <div className="modal-body">
              <div className="form-group">
                <label>Исполнитель:</label>
                <select
                  value={selectedExecutor?.id || ''}
                  onChange={(e) => {
                    const executor = availableExecutors.find(ex => ex.id === parseInt(e.target.value));
                    setSelectedExecutor(executor);
                  }}
                  className="form-select"
                >
                  <option value="">Выберите исполнителя</option>
                  {availableExecutors.map(executor => (
                    <option key={executor.id} value={executor.id}>
                      {executor.name} ({executor.specialization})
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Выберите даты:</label>
                <div className="calendar-container">
                  <DatePicker
                    selected={null}
                    onChange={handleDateClick}
                    inline
                    locale="ru"
                    monthsShown={2}
                    dayClassName={(date) => {
                      const dateString = date.toISOString().split('T')[0];
                      return selectedDates.includes(dateString) ? 'selected-date' : '';
                    }}
                  />
                </div>
                <div className="selected-dates-list">
                  <p>Выбранные даты ({selectedDates.length}):</p>
                  {selectedDates.length === 0 ? (
                    <span className="no-dates">Даты не выбраны</span>
                  ) : (
                    <div className="dates-chips">
                      {selectedDates.map(date => (
                        <span key={date} className="date-chip">
                          {formatDate(date)}
                          <button
                            onClick={() => setSelectedDates(prev => prev.filter(d => d !== date))}
                            className="remove-date"
                          >
                            ×
                          </button>
                        </span>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              <div className="form-group">
                <label>Комментарий:</label>
                <textarea
                  value={assignmentComment}
                  onChange={(e) => setAssignmentComment(e.target.value)}
                  placeholder="Укажите дополнительную информацию для бригадира..."
                  className="comment-textarea"
                  rows="3"
                />
              </div>
            </div>

            <div className="modal-footer">
              <button
                onClick={() => setShowAssignmentModal(false)}
                className="btn-cancel"
              >
                Отмена
              </button>
              <button
                onClick={handleAssignBrigadier}
                disabled={!selectedExecutor || selectedDates.length === 0}
                className="btn-confirm"
              >
                {selectedExecutor && selectedDates.length > 0 ? 'Сохранить' : 'Назначить'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BrigadierManagement;
