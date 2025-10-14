import React from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './components/layout/Layout'
import Login from './pages/auth/Login'
import InitiatorRoutes from './pages/initiator/InitiatorRoutes'
import ExecutorDashboard from './pages/executor/Dashboard'
import ExecutorProfile from './pages/executor/Profile' // ← ДОБАВИЛИ
import BrigadierDashboard from './pages/brigadier/Dashboard'
import DispatcherDashboard from './pages/dispatcher/Dashboard'
import ProtectedRoute from './components/ProtectedRoute'
import './App.css'

// Компонент для редиректа на основе роли
const RoleRedirect = () => {
  const { user, loading } = useAuth()
  
  console.log('=== ROLE REDIRECT ===')
  console.log('Loading:', loading)
  console.log('User:', user)
  
  // Пока загружаем, показываем загрузку
  if (loading) {
    return <div>Загрузка...</div>
  }
  
  // Если нет пользователя, на логин
  if (!user) {
    console.log('No user, redirecting to login')
    return <Navigate to="/login" replace />
  }
  
  // Получаем роль
  const role = user?.roles?.[0]?.name || 'initiator'
  console.log('Detected role:', role)
  
  switch(role.toLowerCase()) {
    case 'initiator':
      console.log('Redirecting to initiator dashboard')
      return <Navigate to="/initiator/dashboard" replace />
    case 'executor':
      return <Navigate to="/executor/shifts" replace />
    case 'brigadier':
      return <Navigate to="/brigadier/dashboard" replace />
    case 'dispatcher':
      return <Navigate to="/dispatcher/dashboard" replace />
    default:
      console.log('Unknown role, defaulting to initiator')
      return <Navigate to="/initiator/dashboard" replace />
  }
}

// Компонент для маршрутов исполнителя
const ExecutorRoutes = () => {
  return (
    <Routes>
      <Route index element={<Navigate to="shifts" replace />} /> {/* ← РЕДИРЕКТ на смены */}
      <Route path="shifts" element={<ExecutorDashboard />} />
      <Route path="profile" element={<ExecutorProfile />} /> {/* ← ДОБАВИЛИ профиль */}
    </Routes>
  )
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
            <Route path="initiator/*" element={<InitiatorRoutes />} />
            <Route path="executor/*" element={<ExecutorRoutes />} /> {/* ← ИЗМЕНИЛИ: на ExecutorRoutes */}
            <Route path="brigadier/*" element={<BrigadierDashboard />} />
            <Route path="dispatcher/*" element={<DispatcherDashboard />} />
          </Route>
        </Routes>
      </Router>
    </AuthProvider>
  )
}

export default App
