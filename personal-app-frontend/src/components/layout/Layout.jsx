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
      <Header user={user} onLogout={logout} />
      <div className="layout-content">
        <Sidebar user={user} />
        <main className="main-content">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

export default Layout
