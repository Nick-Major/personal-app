import React from 'react'
import { NavLink } from 'react-router-dom'
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
        return [
          { path: '/dispatcher/dashboard', label: 'Дашборд' },
          { path: '/dispatcher/requests', label: 'Обработка заявок' },
          { path: '/dispatcher/personnel', label: 'Учет персонала' }
        ]
      default:
        return []
    }
  }

  const routes = getRoleRoutes()

  return (
    <nav className="sidebar">
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
