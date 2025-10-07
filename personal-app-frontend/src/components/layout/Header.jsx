import React from 'react'
import './Header.css'

const Header = ({ user, onLogout }) => {
  return (
    <header className="header">
      <div className="header-left">
        <h1>Система управления персоналом</h1>
      </div>
      <div className="header-right">
        <span className="user-info">
          {user?.name} ({user?.roles?.[0]?.name || 'Пользователь'})
        </span>
        <button onClick={onLogout} className="logout-btn">
          Выйти
        </button>
      </div>
    </header>
  )
}

export default Header
