import React from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './components/layout/Layout'
import Login from './pages/auth/Login'
import InitiatorDashboard from './pages/initiator/Dashboard'
import ExecutorDashboard from './pages/executor/Dashboard'
import BrigadierDashboard from './pages/brigadier/Dashboard'
import DispatcherDashboard from './pages/dispatcher/Dashboard'
import ProtectedRoute from './components/ProtectedRoute'
import './App.css'

// Компонент для редиректа на основе роли
const RoleRedirect = () => {
  const { user } = useAuth()
  
  if (!user) return <Navigate to="/login" replace />
  
  const role = user.roles[0]
  switch(role) {
    case 'initiator':
      return <Navigate to="/initiator/dashboard" replace />
    case 'executor':
      return <Navigate to="/executor/dashboard" replace />
    case 'brigadier':
      return <Navigate to="/brigadier/dashboard" replace />
    case 'dispatcher':
      return <Navigate to="/dispatcher/dashboard" replace />
    default:
      return <Navigate to="/login" replace />
  }
}

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/" element={
            <ProtectedRoute>
              <Layout />
            </ProtectedRoute>
          }>
            <Route index element={<RoleRedirect />} />
            <Route path="initiator/*" element={<InitiatorDashboard />} />
            <Route path="executor/*" element={<ExecutorDashboard />} />
            <Route path="brigadier/*" element={<BrigadierDashboard />} />
            <Route path="dispatcher/*" element={<DispatcherDashboard />} />
          </Route>
        </Routes>
      </Router>
    </AuthProvider>
  )
}

export default App
