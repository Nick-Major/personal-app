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
import { format, parse } from 'date-fns';

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
  const [executorSearch, setExecutorSearch] = useState('');

  useEffect(() => {
    const today = format(new Date(), 'yyyy-MM-dd');
    setSelectedDate(today);

    // Устанавливаем период по умолчанию (текущая неделя)
    const start = new Date();
    const end = new Date();
    end.setDate(end.getDate() + 7);
    setStartDate(format(start, 'yyyy-MM-dd'));
    setEndDate(format(end, 'yyyy-MM-dd'));

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
  const assignmentsSource = Array.isArray(assignments)
    ? assignments
    : (Array.isArray(assignments?.data) ? assignments.data : []);
  const normalizeDateToYmd = (value) => {
    if (!value) return '';
    if (typeof value === 'string') {
      // Часто приходит ISO: 2025-10-09T00:00:00Z → берём первые 10 символов
      if (value.length >= 10 && /\d{4}-\d{2}-\d{2}/.test(value.slice(0, 10))) {
        return value.slice(0, 10);
      }
      const d = new Date(value);
      if (!isNaN(d.getTime())) return format(d, 'yyyy-MM-dd');
      return '';
    }
    if (value instanceof Date && !isNaN(value.getTime())) {
      return format(value, 'yyyy-MM-dd');
    }
    return '';
  };
  const getAssignmentYmd = (assignment) => {
    // пробуем разные возможные поля даты
    const raw = (typeof assignment?.assignment_date !== 'undefined' && assignment.assignment_date !== null)
      ? assignment.assignment_date
      : (typeof assignment?.assignmentDate !== 'undefined' && assignment.assignmentDate !== null)
        ? assignment.assignmentDate
        : (typeof assignment?.date !== 'undefined' && assignment.date !== null)
          ? assignment.date
          : undefined;
    return normalizeDateToYmd(raw);
  };

  const getAssignmentDates = (assignment) => {
    const datesArray = Array.isArray(assignment?.assignment_dates)
      ? assignment.assignment_dates
      : [];
    const single = getAssignmentYmd(assignment);
    const combined = single ? [single, ...datesArray] : datesArray;
    return Array.from(new Set(combined.map(normalizeDateToYmd).filter(Boolean)));
  };
  const isWithinSelectedRange = (assignmentDates) => {
    const dates = assignmentDates;
    if (!dates || dates.length === 0) return false;
    if (viewMode === 'date') {
      return dates.some(d => d === selectedDate);
    }
    // period mode
    if (!startDate || !endDate) return true;
    return dates.some(d => d >= startDate && d <= endDate);
  };

  const filteredAssignments = assignmentsSource.filter(assignment => {
    // Фильтр по инициатору
    if (filterMode === 'my' && (assignment.initiator?.id !== user?.id)) {
      return false;
    }

    // Фильтрация по дате/периоду (учитываем ISO с временем)
    return isWithinSelectedRange(getAssignmentDates(assignment));
  });

  const handleAssignBrigadier = async () => {
    if (!selectedExecutor || selectedDates.length === 0) return;

    try {
      // Предотвращение конфликтов: бригадир не может выходить как Исполнитель и не может быть назначен бригадиром дважды на одну дату
      // Здесь проверяем только конфликты по уже загруженным назначениям
      const buildKey = (brigadierId, initiatorId, dateYmd) => `${brigadierId}|${initiatorId}|${dateYmd}`;
      const alreadyAssignedKeys = new Set(
        assignmentsSource
          .filter(a => a.brigadier?.id != null && a.initiator?.id != null)
          .flatMap(a => {
            const dates = getAssignmentDates(a);
            return dates.map(d => buildKey(a.brigadier.id, a.initiator.id, d));
          })
      );

      const uniqueDates = Array.from(new Set(selectedDates)).sort();
      const candidateKeys = uniqueDates.map(d => buildKey(selectedExecutor.id, user.id, d));
      const datesToCreate = uniqueDates.filter((d, idx) => !alreadyAssignedKeys.has(candidateKeys[idx]));
      const conflictedDates = uniqueDates.filter((d, idx) => alreadyAssignedKeys.has(candidateKeys[idx]));

      // Создаём одно назначение на сразу все выбранные даты
      const payload = {
        brigadier_id: selectedExecutor.id,
        initiator_id: user.id,
        assignment_dates: datesToCreate,
        comment: assignmentComment || undefined,
        status: 'pending'
      };
      console.info('[BrigadierManagement] creating assignment with payload:', payload);
      await brigadierService.createAssignment(payload);

      await loadAssignments();
      
      setShowAssignmentModal(false);
      setSelectedDates([]);
      setAssignmentComment('');
      setSelectedExecutor(null);
      
      if (conflictedDates.length > 0 && datesToCreate.length > 0) {
        alert(`Часть дат пропущена из-за конфликтов: ${conflictedDates.join(', ')}. Остальные назначения созданы и ожидают подтверждения.`);
      } else if (conflictedDates.length > 0 && datesToCreate.length === 0) {
        alert(`Назначения не созданы: выбранные даты уже заняты для этого бригадира (${conflictedDates.join(', ')}).`);
      } else {
        alert('Бригадир успешно назначен на выбранные даты! Ожидает подтверждения.');
      }
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
    setExecutorSearch('');
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
    const dateString = format(date, 'yyyy-MM-dd');
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
    if (!dateString) return '-';
    const ymdRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (ymdRegex.test(dateString)) {
      const parsed = parse(dateString, 'yyyy-MM-dd', new Date());
      return format(parsed, 'dd.MM.yyyy');
    }
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
                      <strong>{assignment.brigadier?.full_name || assignment.brigadier?.name || 'N/A'}</strong>
                    </div>
                  </td>
                  <td>{assignment.brigadier?.specialization || 'N/A'}</td>
                  <td>
                    {(() => {
                      const dates = getAssignmentDates(assignment);
                      if (dates.length === 0) return '-';
                      return (
                        <div className="dates-chips-inline">
                          {dates.map(d => (
                            <span key={d} className="date-chip small">{formatDate(d)}</span>
                          ))}
                        </div>
                      );
                    })()}
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
                      {assignment.initiator?.id === user?.id && (
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
                <input
                  type="text"
                  value={executorSearch}
                  onChange={(e) => setExecutorSearch(e.target.value)}
                  placeholder="Поиск по ФИО или специализации"
                  className="form-input"
                />
                <select
                  value={selectedExecutor?.id || ''}
                  onChange={(e) => {
                    const executor = availableExecutors.find(ex => ex.id === parseInt(e.target.value, 10));
                    setSelectedExecutor(executor);
                  }}
                  className="form-select"
                >
                  <option value="">Выберите исполнителя</option>
                  {(Array.isArray(availableExecutors) ? availableExecutors : []).filter(ex => {
                    if (!executorSearch) return true;
                    const q = executorSearch.toLowerCase().trim();
                    const name = String(ex.full_name || ex.name || '').toLowerCase();
                    const spec = Array.isArray(ex.specialties)
                      ? ex.specialization.join(' ').toLowerCase()
                      : String(ex.specialization || '').toLowerCase();
                    return name.includes(q) || spec.includes(q);
                  }).map(executor => (
                    <option key={executor.id} value={executor.id}>
                      {executor.full_name || executor.name}
                      {executor.specialties && executor.specialties.length > 0 && 
                        ` (${executor.specialties.join(', ')})`}
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
                      const dateString = format(date, 'yyyy-MM-dd');
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
