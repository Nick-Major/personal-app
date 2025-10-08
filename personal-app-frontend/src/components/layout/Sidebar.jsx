// personal-app-frontend/src/components/layout/Sidebar.jsx
import React from 'react'
import { NavLink } from 'react-router-dom'
import { dispatcherMenu } from '../../config/dispatcherMenu'
import { initiatorMenu } from '../../config/initiatorMenu' // ← ДОБАВИЛИ
import './Sidebar.css'

const Sidebar = ({ user }) => {
  const getRoleMenu = () => {
    const role = user?.roles?.[0]?.name

    switch(role) {
      case 'initiator':
        return initiatorMenu // ← ИСПОЛЬЗУЕМ initiatorMenu
      case 'executor':
        return [
          { path: '/executor/dashboard', label: 'Дашборд' },
          { path: '/executor/shifts', label: 'Мои смены' }
        ]
      case 'brigadier':
        return [
          { path: '/brigadier/dashboard', label: 'Дашборд' },
          { path: '/brigadier/team', label: 'Управление командой' },
          { path: '/brigadier/requests', label: 'Заявки' }
        ]
      case 'dispatcher':
        return dispatcherMenu.flatMap(section => section.items)
      default:
        return []
    }
  }

  const renderMenuSections = (menu) => {
    return menu.map((section, index) => (
      <div key={index} className="sidebar-section">
        <h3 className="sidebar-section-title">{section.title}</h3>
        <ul className="sidebar-nav-list">
          {section.items.map(item => (
            <li key={item.path}>
              <NavLink
                to={item.path}
                className={({ isActive }) =>
                  `nav-link ${isActive ? 'active' : ''}`
                }
              >
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

  const role = user?.roles?.[0]?.name

  if (role === 'dispatcher' || role === 'initiator') {
    const menu = role === 'dispatcher' ? dispatcherMenu : initiatorMenu
    return (
      <nav className="sidebar">
        <div className="sidebar-header">
          <h3 className="sidebar-title">Навигация</h3>
        </div>
        <div className="sidebar-content">
          {renderMenuSections(menu)}
        </div>
      </nav>
    )
  }

  // Старый вариант для других ролей
  const routes = getRoleRoutes()
  return (
    <nav className="sidebar">
      <div className="sidebar-header">
        <h3 className="sidebar-title">Навигация</h3>
      </div>
      <ul className="sidebar-nav">
        {routes.map(route => (
          <li key={route.path}>
            <NavLink
              to={route.path}
              className={({ isActive }) =>
                `nav-link ${isActive ? 'active' : ''}`
              }
            >
              {route.label}
            </NavLink>
          </li>
        ))}
      </ul>
    </nav>
  )
}

export default Sidebar
