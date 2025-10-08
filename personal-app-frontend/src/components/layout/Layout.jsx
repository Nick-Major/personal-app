// personal-app-frontend/src/components/layout/Layout.jsx
import React from 'react'
import { Outlet } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import Header from './Header'
import Sidebar from './Sidebar'
import './Layout.css'

const Layout = () => {
  const { user, logout } = useAuth()

  return (
    <div className="layout">
      <Sidebar user={user} />
      <div className="main-content-wrapper">
        <Header user={user} onLogout={logout} />
        <main className="layout-content">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

export default Layout
