// personal-app-frontend/src/components/layout/Header.jsx
import React from 'react';
import './Header.css';

const Header = ({ user, onLogout }) => {
  // Функция для красивого отображения роли
  const getRoleDisplayName = () => {
    // Используем executor_role_display из бэкенда если есть
    if (user?.executor_role_display) {
      return user.executor_role_display;
    }
    
    // Или преобразуем системную роль
    const role = user?.roles?.[0]?.name;
    const roleNames = {
      'initiator': 'Инициатор',
      'executor': 'Исполнитель', 
      'brigadier': 'Бригадир',
      'dispatcher': 'Диспетчер',
      'admin': 'Администратор'
    };
    return roleNames[role] || role || 'Не назначена';
  };

  return (
    <header className="header">
      <div className="header-left">
        <h1 className="header-title">🎯 Персонал — Система управления</h1>
      </div>
      
      <div className="header-right">
        {/* Информация о пользователе */}
        <div className="user-info">
          <div className="user-details">
            <span className="user-name">{user?.full_name || user?.name || 'Пользователь'}</span>
            <span className="user-role">
              {getRoleDisplayName()}
            </span>
          </div>
        </div>
        
        {/* Кнопка выхода */}
        <button onClick={onLogout} className="logout-btn">
          <span className="logout-icon">🚪</span>
          <span>Выйти</span>
        </button>
      </div>
    </header>
  );
};

export default Header;
