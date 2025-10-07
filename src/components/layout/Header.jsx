import React from 'react'
import { useAuth } from '../../context/AuthContext'

const Header = () => {
  const { user, logout } = useAuth()

  return (
    <header className="header">
      <div className="header-content">
        <h1>Система управления персоналом</h1>
        {user && (
          <div className="header-user">
            <span>{user.name}</span>
            <button onClick={logout} className="logout-btn">
              Выйти
            </button>
          </div>
        )}
      </div>
    </header>
  )
}

export default Header
