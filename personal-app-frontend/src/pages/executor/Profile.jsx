import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import './Profile.css';

const ExecutorProfile = () => {
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState('personal');

  if (!user) {
    return <div className="profile-loading">Загрузка профиля...</div>;
  }

  return (
    <div className="executor-profile">
      <div className="profile-header">
        <h2>Мой профиль</h2>
        <div className="user-role-display">
          {user.executor_role_display || 'Исполнитель'}
        </div>
      </div>

      <div className="profile-tabs">
        <button 
          className={activeTab === 'personal' ? 'active' : ''}
          onClick={() => setActiveTab('personal')}
        >
          Личные данные
        </button>
        <button 
          className={activeTab === 'specialties' ? 'active' : ''}
          onClick={() => setActiveTab('specialties')}
        >
          Специальности и ставки
        </button>
        <button 
          className={activeTab === 'work' ? 'active' : ''}
          onClick={() => setActiveTab('work')}
        >
          Рабочая информация
        </button>
      </div>

      <div className="profile-content">
        {/* Вкладка Личные данные */}
        {activeTab === 'personal' && (
          <div className="profile-section">
            <h3>Личные данные</h3>
            <div className="personal-info-grid">
              <div className="info-item">
                <label>ФИО:</label>
                <span className="info-value">{user.full_name || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Имя:</label>
                <span className="info-value">{user.name || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Фамилия:</label>
                <span className="info-value">{user.surname || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Отчество:</label>
                <span className="info-value">{user.patronymic || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Email:</label>
                <span className="info-value">{user.email || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Телефон:</label>
                <span className="info-value">{user.phone || 'Не указано'}</span>
              </div>
              <div className="info-item">
                <label>Telegram ID:</label>
                <span className="info-value">{user.telegram_id || 'Не указано'}</span>
              </div>
            </div>
          </div>
        )}

        {/* Вкладка Специальности и ставки */}
        {activeTab === 'specialties' && (
          <div className="profile-section">
            <h3>Мои специальности и ставки</h3>
            {user.specialties && user.specialties.length > 0 ? (
              <div className="specialties-list">
                {user.specialties.map(specialty => (
                  <div key={specialty.id} className="specialty-card">
                    <div className="specialty-name">{specialty.name}</div>
                    <div className="specialty-rate">
                      <span className="rate-label">Базовая ставка:</span>
                      <span className="rate-value">
                        {specialty.pivot?.base_hourly_rate 
                          ? `${specialty.pivot.base_hourly_rate} ₽/час`
                          : 'Не установлена'
                        }
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="no-specialties">
                <p>Специальности не назначены</p>
                <small>Обратитесь к администратору для назначения специальностей</small>
              </div>
            )}
          </div>
        )}

        {/* Вкладка Рабочая информация */}
        {activeTab === 'work' && (
          <div className="profile-section">
            <h3>Рабочая информация</h3>
            <div className="work-info-grid">
              <div className="info-item">
                <label>Системная роль:</label>
                <span className="info-value">
                  {user.roles?.[0]?.name || 'Не назначена'}
                </span>
              </div>
              <div className="info-item">
                <label>Роль в системе:</label>
                <span className="info-value">
                  {user.executor_role_display || 'Исполнитель'}
                </span>
              </div>
              <div className="info-item">
                <label>Статус бригадира:</label>
                <span className="info-value">
                  {user.is_always_brigadier ? 'Постоянный бригадир' : 'Обычный исполнитель'}
                </span>
              </div>
              <div className="info-item">
                <label>ID пользователя:</label>
                <span className="info-value">{user.id}</span>
              </div>
            </div>

            {/* Дополнительная информация для бригадиров */}
            {user.executor_role && user.executor_role !== 'executor' && (
              <div className="brigadier-info">
                <h4>Возможности бригадира</h4>
                <ul>
                  <li>✅ Подтверждение назначений на бригадира</li>
                  <li>✅ Просмотр заявок где вы назначены бригадиром</li>
                  {user.executor_role === 'brigadier_with_rights' && (
                    <li>✅ Создание заявок (права инициатора)</li>
                  )}
                </ul>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default ExecutorProfile;
