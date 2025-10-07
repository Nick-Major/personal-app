import React from 'react'
import { NavLink } from 'react-router-dom'
import { dispatcherMenu } from '../../config/dispatcherMenu'
import './Sidebar.css'

const Sidebar = ({ user }) => {
  const getRoleRoutes = () => {
    const role = user?.roles?.[0]?.name

    switch(role) {
      case 'initiator':
        return [
          { path: '/initiator/dashboard', label: 'Дашборд' },
          { path: '/initiator/brigadiers', label: 'Назначение бригадиров' },
          { path: '/initiator/requests', label: 'Мои заявки' },
          { path: '/initiator/create-request', label: 'Создать заявку' }
        ]
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
        // Для диспетчера используем расширенное меню
        return dispatcherMenu.flatMap(section => section.items)
      default:
        return []
    }
  }

  const renderDispatcherMenu = () => {
    return dispatcherMenu.map((section, index) => (
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

  if (role === 'dispatcher') {
    return (
      <nav className="sidebar">
        <div className="sidebar-header">
          <h3 className="sidebar-title">Навигация</h3>
        </div>
        <div className="sidebar-content">
          {renderDispatcherMenu()}
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
