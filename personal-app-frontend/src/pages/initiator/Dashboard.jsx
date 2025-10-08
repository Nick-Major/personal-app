// personal-app-frontend/src/pages/initiator/Dashboard.jsx
import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { workRequestService } from '../../services/workRequestService';
import './Dashboard.css';

const InitiatorDashboard = () => {
  const [stats, setStats] = useState({
    total: 0,
    draft: 0,
    published: 0,
    inProgress: 0,
    completed: 0
  });
  const [recentRequests, setRecentRequests] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      console.log('Loading dashboard data...');
      const response = await workRequestService.getMyRequests();
      console.log('API Response:', response);
      
      // Убедимся, что response - массив
      const requests = Array.isArray(response) ? response : response.data || [];
      console.log('Requests array:', requests);
      
      // Calculate stats
      const statsData = {
        total: requests.length,
        draft: requests.filter(r => r.status === 'draft').length,
        published: requests.filter(r => r.status === 'published').length,
        inProgress: requests.filter(r => r.status === 'in_progress').length,
        completed: requests.filter(r => r.status === 'completed').length
      };
      
      setStats(statsData);
      setRecentRequests(requests.slice(0, 5)); // Last 5 requests
    } catch (error) {
      console.error('Error loading dashboard:', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (status) => {
    const colors = {
      draft: '#6c757d',
      published: '#17a2b8',
      in_progress: '#ffc107',
      completed: '#28a745',
      cancelled: '#dc3545'
    };
    return colors[status] || '#6c757d';
  };

  if (loading) {
    return (
      <div className="dashboard-loading">
        <div className="loading-spinner"></div>
        <p>Загрузка данных...</p>
      </div>
    );
  }

  return (
    <div className="initiator-dashboard">
      {/* Header */}
      <div className="dashboard-header">
        <h1>Личный кабинет Инициатора</h1>
        <Link to="/initiator/create-request" className="btn-primary">
          + Создать заявку
        </Link>
      </div>

      {/* Stats Cards */}
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-value">{stats.total}</div>
          <div className="stat-label">Всего заявок</div>
        </div>
        <div className="stat-card">
          <div className="stat-value" style={{color: '#6c757d'}}>{stats.draft}</div>
          <div className="stat-label">Черновики</div>
        </div>
        <div className="stat-card">
          <div className="stat-value" style={{color: '#17a2b8'}}>{stats.published}</div>
          <div className="stat-label">Опубликовано</div>
        </div>
        <div className="stat-card">
          <div className="stat-value" style={{color: '#28a745'}}>{stats.completed}</div>
          <div className="stat-label">Завершено</div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="quick-actions">
        <h2>Быстрые действия</h2>
        <div className="actions-grid">
          <Link to="/initiator/create-request" className="action-card">
            <div className="action-icon">📋</div>
            <div className="action-text">Создать заявку</div>
          </Link>
          <Link to="/initiator/brigadier-management" className="action-card">
            <div className="action-icon">👑</div>
            <div className="action-text">Планирование бригадиров</div>
          </Link>
          <Link to="/initiator/requests" className="action-card">
            <div className="action-icon">📊</div>
            <div className="action-text">Мои заявки</div>
          </Link>
        </div>
      </div>

      {/* Recent Requests */}
      <div className="recent-requests">
        <h2>Последние заявки</h2>
        {recentRequests.length === 0 ? (
          <div className="empty-state">
            <p>У вас пока нет заявок</p>
            <Link to="/initiator/create-request" className="btn-secondary">
              Создать первую заявку
            </Link>
          </div>
        ) : (
          <div className="requests-list">
            {recentRequests.map(request => (
              <div key={request.id} className="request-item">
                <div className="request-main">
                  <div className="request-title">
                    <strong>Заявка #{request.request_number}</strong>
                    <span 
                      className="status-badge"
                      style={{backgroundColor: getStatusColor(request.status)}}
                    >
                      {request.status}
                    </span>
                  </div>
                  <div className="request-details">
                    <span>Специализация: {request.specialization}</span>
                    <span>Кол-во: {request.workers_count} чел.</span>
                    <span>Проект: {request.project}</span>
                  </div>
                </div>
                <div className="request-actions">
                  <Link 
                    to={`/initiator/requests/${request.id}`}
                    className="btn-link"
                  >
                    Подробнее
                  </Link>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default InitiatorDashboard;
