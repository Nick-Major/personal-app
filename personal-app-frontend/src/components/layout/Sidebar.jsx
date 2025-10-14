// personal-app-frontend/src/components/layout/Sidebar.jsx
import React from 'react'
import { NavLink } from 'react-router-dom'
import { dispatcherMenu } from '../../config/dispatcherMenu'
import { initiatorMenu } from '../../config/initiatorMenu'
import { executorMenu } from '../../config/executorMenu'
import './Sidebar.css'

const Sidebar = ({ user }) => {
  // Функция для получения меню по роли
  const getRoleMenu = () => {
    const role = user?.roles?.[0]?.name

    switch(role) {
      case 'initiator':
        return initiatorMenu
      case 'executor':
        return executorMenu
      case 'dispatcher':
        return dispatcherMenu
      default:
        // По умолчанию для исполнителей
        return executorMenu
    }
  }

  // УДАЛИТЬ всю секцию для 'brigadier' - этой роли больше нет

  // Рендер секций меню
  const renderMenuSections = (menu) => {
    return menu.map((section, index) => (
      <div key={index} className="sidebar-section">
        {section.title && (
          <h3 className="sidebar-section-title">{section.title}</h3>
        )}
        <ul className="sidebar-nav-list">
          {section.items.map(item => (
            <li key={item.path}>
              <NavLink
                to={item.path}
                className={({ isActive }) =>
                  `nav-link ${isActive ? 'active' : ''}`
                }
              >
                {item.icon && <span className="nav-icon">{item.icon}</span>}
                <span className="nav-link-text">{item.label}</span>
                {item.badge && (
                  <span className="nav-badge">{item.badge}</span>
                )}
              </NavLink>
            </li>
          ))}
        </ul>
      </div>
    ))
  }

  const menu = getRoleMenu()
  const role = user?.roles?.[0]?.name

  return (
    <nav className="sidebar">
      <div className="sidebar-header">
        <h3 className="sidebar-title">Навигация</h3>
        {user?.executor_role_display && (
          <div className="user-role-badge">
            {user.executor_role_display}
          </div>
        )}
      </div>
      <div className="sidebar-content">
        {renderMenuSections(menu)}
      </div>
    </nav>
  )
}

export default Sidebar
