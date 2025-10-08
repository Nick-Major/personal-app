// personal-app-frontend/src/pages/initiator/Requests.jsx
import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import './Requests.css';

const Requests = () => {
  const [requests, setRequests] = useState([]);
  const [filterMode, setFilterMode] = useState('my'); // 'my' или 'all'
  const [filters, setFilters] = useState({
    status: '',
    specialization: '',
    date: ''
  });
  const [loading, setLoading] = useState(true);

  // Mock данные заявок
  useEffect(() => {
    const mockRequests = [
      {
        id: 1,
        requestNumber: 'САД-001/2025',
        date: '2025-10-10',
        time: '08:00',
        duration: 8,
        workersCount: 3,
        address: 'ул. Центральная, 1',
        brigadier: { name: 'Иван Петров' },
        specialization: 'садовники',
        executorType: 'our_staff',
        workType: 'посадка растений',
        project: 'Озеленение парка',
        purpose: 'Весенняя посадка',
        payerCompany: 'ООО "Городские парки"',
        status: 'published',
        comment: 'Работа на центральной клумбе',
        initiator: { id: 7, name: 'Бобкова Диана' }
      },
      {
        id: 2,
        requestNumber: 'ДЕК-002/2025',
        date: '2025-10-11',
        time: '09:00',
        duration: 6,
        workersCount: 2,
        address: 'ул. Парковая, 15',
        brigadier: { name: 'Мария Сидорова' },
        specialization: 'декораторы',
        executorType: 'our_staff',
        workType: 'оформление входа',
        project: 'Благоустройство территории',
        purpose: 'Декоративное оформление',
        payerCompany: 'ООО "Ландшафт Про"',
        status: 'in_work',
        comment: 'Оформление главного входа',
        initiator: { id: 8, name: 'Другой Инициатор' }
      }
    ];
    
    setRequests(mockRequests);
    setLoading(false);
  }, []);

  // Фильтрация заявок
  const filteredRequests = requests.filter(request => {
    if (filterMode === 'my' && request.initiator.id !== 7) {
      return false;
    }
    
    if (filters.status && request.status !== filters.status) {
      return false;
    }
    
    if (filters.specialization && request.specialization !== filters.specialization) {
      return false;
    }
    
    if (filters.date && request.date !== filters.date) {
      return false;
    }
    
    return true;
  });

  const getStatusDisplay = (status) => {
    const statusMap = {
      'draft': '📝 Черновик',
      'published': '📤 Опубликована',
      'in_work': '🔄 В работе',
      'staffed': '👥 Укомплектована',
      'in_progress': '⚡ Выполняется',
      'completed': '✅ Завершена',
      'cancelled': '❌ Отменена'
    };
    return statusMap[status] || status;
  };

  const getStatusColor = (status) => {
    const colors = {
      'draft': '#6c757d',
      'published': '#17a2b8',
      'in_work': '#ffc107',
      'staffed': '#28a745',
      'in_progress': '#007bff',
      'completed': '#20c997',
      'cancelled': '#dc3545'
    };
    return colors[status] || '#6c757d';
  };

  const handlePublish = (requestId) => {
    // TODO: API call для публикации
    setRequests(prev => prev.map(req => 
      req.id === requestId ? { ...req, status: 'published' } : req
    ));
  };

  if (loading) {
    return <div className="loading">Загрузка заявок...</div>;
  }

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
            <option value="in_work">В работе</option>
            <option value="staffed">Укомплектована</option>
            <option value="completed">Завершена</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Специальность:</label>
          <select 
            value={filters.specialization} 
            onChange={(e) => setFilters(prev => ({ ...prev, specialization: e.target.value }))}
            className="filter-select"
          >
            <option value="">Все специальности</option>
            <option value="садовники">Садовники</option>
            <option value="декораторы">Декораторы</option>
            <option value="администраторы">Администраторы</option>
          </select>
        </div>

        <div className="filter-group">
          <label>Дата:</label>
          <input
            type="date"
            value={filters.date}
            onChange={(e) => setFilters(prev => ({ ...prev, date: e.target.value }))}
            className="date-input"
          />
        </div>

        <button 
          onClick={() => setFilters({ status: '', specialization: '', date: '' })}
          className="btn-clear"
        >
          Сбросить
        </button>
      </div>

      {/* Таблица заявок */}
      <div className="table-container">
        <table className="requests-table">
          <thead>
            <tr>
              <th>№ Заявки</th>
              <th>Дата/Время</th>
              <th>Адрес</th>
              <th>Бригадир</th>
              <th>Специальность</th>
              <th>Кол-во</th>
              <th>Проект</th>
              <th>Статус</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            {filteredRequests.length === 0 ? (
              <tr>
                <td colSpan="9" className="no-data">
                  Заявки не найдены
                </td>
              </tr>
            ) : (
              filteredRequests.map(request => (
                <tr key={request.id}>
                  <td>
                    <strong>{request.requestNumber}</strong>
                  </td>
                  <td>
                    <div>{new Date(request.date).toLocaleDateString('ru-RU')}</div>
                    <div className="time">{request.time}</div>
                  </td>
                  <td>{request.address}</td>
                  <td>{request.brigadier.name}</td>
                  <td>{request.specialization}</td>
                  <td>{request.workersCount} чел.</td>
                  <td>{request.project}</td>
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
                      <button className="action-btn view-btn" title="Просмотр">
                        👁️
                      </button>
                      {request.status === 'draft' && (
                        <button 
                          onClick={() => handlePublish(request.id)}
                          className="action-btn publish-btn"
                          title="Опубликовать"
                        >
                          📤
                        </button>
                      )}
                      {request.initiator.id === 7 && (
                        <button className="action-btn edit-btn" title="Редактировать">
                          ✏️
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
    </div>
  );
};

export default Requests;
