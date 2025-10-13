// personal-app-frontend/src/pages/initiator/Requests.jsx
import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import './Requests.css';

const Requests = () => {
  const [requests, setRequests] = useState([]);
  const [filterMode, setFilterMode] = useState('my'); // По умолчанию "Ваши заявки"
  const [filters, setFilters] = useState(() => {
    // Восстанавливаем фильтры из localStorage
    const saved = localStorage.getItem('requests_filters');
    return saved ? JSON.parse(saved) : {
      status: '',
      specialty: '',
      dateFrom: '',
      dateTo: ''
    };
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadRequests();
  }, [filterMode]);

  useEffect(() => {
    localStorage.setItem('requests_filters', JSON.stringify(filters));
  }, [filters]);

  const loadRequests = async () => {
    try {
      setLoading(true);
      let endpoint = '/work-requests';
      if (filterMode === 'my') {
        endpoint = '/my/work-requests'; // Убрал /api - уже есть в baseURL
      }
      
      const response = await api.get(endpoint);
      const data = response.data.data || response.data;
      setRequests(Array.isArray(data) ? data : []);
    } catch (err) {
      console.error('Error:', err);
      setRequests([]);
    } finally {
      setLoading(false);
    }
  };

  // Отладка данных
  useEffect(() => {
    if (requests.length > 0) {
      console.log('=== ОТЛАДКА ДАННЫХ ЗАЯВОК ===');
      console.log('Загружено заявок:', requests.length);
      console.log('Первая заявка:', requests[0]);
      console.log('Work_date первой заявки:', requests[0].work_date);
      console.log('Start_time первой заявки:', requests[0].start_time);
      console.log('Initiator первой заявки:', requests[0].initiator);
      console.log('============================');
    }
  }, [requests]);

  // Функция для форматирования даты
  const formatWorkDate = (dateString) => {
    if (!dateString) return 'Не указана';
    
    try {
      const date = new Date(dateString);
      
      if (isNaN(date.getTime())) {
        console.error('Invalid date:', dateString);
        return 'Неверная дата';
      }
      
      return date.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'Europe/Moscow'
      });
    } catch (error) {
      console.error('Date formatting error:', error, dateString);
      return 'Ошибка даты';
    }
  };

  // Функция для форматирования времени
  const formatStartTime = (timeString) => {
    if (!timeString) return 'Не указано';
    
    try {
      // Если время в формате "HH:mm"
      if (typeof timeString === 'string' && timeString.includes(':')) {
        const [hours, minutes] = timeString.split(':');
        return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
      }
      
      // Если время как объект Date
      if (timeString instanceof Date || (typeof timeString === 'string' && timeString.includes('T'))) {
        const time = new Date(timeString);
        return time.toLocaleTimeString('ru-RU', {
          hour: '2-digit',
          minute: '2-digit',
          timeZone: 'Europe/Moscow'
        });
      }
      
      return 'Неверный формат';
    } catch (error) {
      console.error('Time formatting error:', error, timeString);
      return 'Ошибка времени';
    }
  };

  // Функция для форматирования имени инициатора
  const formatInitiatorName = (initiator) => {
    if (!initiator) return 'Не указан';
    
    const name = initiator.name || '';
    const surname = initiator.surname || '';
    
    if (name && surname) {
      return `${name} ${surname}`;
    } else if (name) {
      return name;
    } else if (surname) {
      return surname;
    } else {
      return 'Не указан';
    }
  };

  // Фильтрация
  const filteredRequests = requests.filter(request => {
    if (filters.status && request.status !== filters.status) {
      return false;
    }

    if (filters.specialty && request.specialty?.name !== filters.specialty) {
      return false;
    }

    if (filters.dateFrom || filters.dateTo) {
      if (!request.work_date) return false;
      
      const requestDate = new Date(request.work_date);
      
      if (filters.dateFrom) {
        const fromDate = new Date(filters.dateFrom);
        if (requestDate < fromDate) return false;
      }
      
      if (filters.dateTo) {
        const toDate = new Date(filters.dateTo);
        toDate.setHours(23, 59, 59, 999);
        if (requestDate > toDate) return false;
      }
    }
    
    return true;
  });

  // Уникальные специальности для фильтра
  const uniqueSpecialties = [...new Set(requests
    .filter(req => req.specialty)
    .map(req => req.specialty.name)
  )];

  const getStatusDisplay = (status) => {
    const statusMap = {
      'draft': '📝 Черновик',
      'published': '📤 Опубликована', 
      'in_progress': '🔄 В работе',
      'staffed': '👥 Укомплектована',
      'completed': '✅ Завершена'
    };
    return statusMap[status] || status;
  };

  const getStatusColor = (status) => {
    const colors = {
      'draft': '#6c757d',
      'published': '#17a2b8',
      'in_progress': '#ffc107',
      'staffed': '#28a745',
      'completed': '#20c997'
    };
    return colors[status] || '#6c757d';
  };

  if (loading) return <div className="loading">Загрузка заявок...</div>;

  return (
    <div className="requests-page">
      <div className="page-header">
        <div className="header-content">
          <h1>Заявки</h1>
          <Link to="/initiator/create-request" className="btn-primary">
            + Оформить заявку
          </Link>
        </div>
      </div>

      {/* Фильтры */}
      <div className="filters-panel">
        <div className="filter-group">
          <label>Показать:</label>
          <select 
            value={filterMode} 
            onChange={(e) => setFilterMode(e.target.value)}
            className="filter-select"
          >
            <option value="my">Ваши заявки</option>
            <option value="all">Все заявки</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Статус:</label>
          <select 
            value={filters.status} 
            onChange={(e) => setFilters(prev => ({ ...prev, status: e.target.value }))}
            className="filter-select"
          >
            <option value="">Все статусы</option>
            <option value="draft">Черновик</option>
            <option value="published">Опубликована</option>
            <option value="in_progress">В работе</option>
            <option value="staffed">Укомплектована</option>
            <option value="completed">Завершена</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Специальность:</label>
          <select 
            value={filters.specialty} 
            onChange={(e) => setFilters(prev => ({ ...prev, specialty: e.target.value }))}
            className="filter-select"
          >
            <option value="">Все специальности</option>
            {uniqueSpecialties.map(specialty => (
              <option key={specialty} value={specialty}>
                {specialty}
              </option>
            ))}
          </select>
        </div>

        <div className="filter-group">
          <label>Период с:</label>
          <input
            type="date"
            value={filters.dateFrom}
            onChange={(e) => setFilters(prev => ({ ...prev, dateFrom: e.target.value }))}
            className="date-input"
          />
        </div>

        <div className="filter-group">
          <label>по:</label>
          <input
            type="date"
            value={filters.dateTo}
            onChange={(e) => setFilters(prev => ({ ...prev, dateTo: e.target.value }))}
            className="date-input"
          />
        </div>

        <button 
          onClick={() => setFilters({ status: '', specialty: '', dateFrom: '', dateTo: '' })}
          className="btn-clear"
        >
          Сбросить
        </button>

        <button onClick={loadRequests} className="btn-refresh">
          🔄 Обновить
        </button>
      </div>

      {/* Отладочная информация */}
      <div style={{ background: '#f8f9fa', padding: '10px', marginBottom: '20px', borderRadius: '5px' }}>
        <small>
          <strong>Отладка:</strong> Загружено {requests.length} заявок, отфильтровано {filteredRequests.length}
        </small>
      </div>

      {/* Таблица */}
      <div className="table-container">
        <table className="requests-table">
          <thead>
            <tr>
              <th>№ Заявки</th>
              <th>Дата работ</th>
              <th>Время начала</th>
              <th>Инициатор</th>
              <th>Бригадир</th>
              <th>Специальность</th>
              <th>Вид работ</th>
              <th>Кол-во</th>
              <th>Проект</th>
              <th>Назначение</th>
              <th>Компания-плательщик</th>
              <th>Статус</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            {filteredRequests.length === 0 ? (
              <tr>
                <td colSpan="13" className="no-data">
                  {requests.length === 0 ? 'Нет заявок' : 'Заявки не найдены по фильтру'}
                </td>
              </tr>
            ) : (
              filteredRequests.map(request => (
                <tr key={request.id}>
                  <td>
                    <strong>{request.request_number || `ЗАЯВКА-${request.id}`}</strong>
                  </td>
                  <td>
                    <div style={{fontWeight: 'bold'}}>
                      {formatWorkDate(request.work_date)}
                    </div>
                  </td>
                  <td>
                    <div style={{fontWeight: '500'}}>
                      {formatStartTime(request.start_time)}
                    </div>
                  </td>
                  <td>
                    {formatInitiatorName(request.initiator)}
                  </td>
                  <td>{request.brigadier ? formatInitiatorName(request.brigadier) : 'Не назначен'}</td>
                  <td>{request.specialty?.name || 'Не указана'}</td>
                  <td>{request.work_type?.name || 'Не указан'}</td>
                  <td>{request.workers_count} чел.</td>
                  <td>{request.project || '-'}</td>
                  <td>{request.purpose || '-'}</td>
                  <td>{request.payer_company || '-'}</td>
                  <td>
                    <span 
                      className="status-badge"
                      style={{backgroundColor: getStatusColor(request.status)}}
                    >
                      {getStatusDisplay(request.status)}
                    </span>
                  </td>
                  <td>
                    <div className="actions-cell">
                      <Link 
                        to={`/initiator/requests/${request.id}`}
                        className="action-btn view-btn"
                        title="Просмотр"
                      >
                        👁️
                      </Link>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Requests;
