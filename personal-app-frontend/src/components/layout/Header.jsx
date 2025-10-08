// personal-app-frontend/src/components/layout/Header.jsx
import React from 'react';
import './Header.css';

const Header = ({ user, onLogout }) => {
  // Функция для красивого отображения роли
  const getRoleDisplayName = (role) => {
    const roleNames = {
      'initiator': 'Инициатор',
      'executor': 'Исполнитель', 
      'brigadier': 'Бригадир',
      'dispatcher': 'Диспетчер',
      'admin': 'Администратор'
    };
    return roleNames[role] || role;
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
            <span className="user-name">{user?.name || 'Пользователь'}</span>
            <span className="user-role">
              {user?.roles?.[0]?.name ? 
                getRoleDisplayName(user.roles[0].name) : 
                'Не назначена'}
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
