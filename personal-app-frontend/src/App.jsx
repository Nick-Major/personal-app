import React from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './components/layout/Layout'
import Login from './pages/auth/Login'
import InitiatorRoutes from './pages/initiator/InitiatorRoutes'
import ExecutorDashboard from './pages/executor/Dashboard'
import BrigadierDashboard from './pages/brigadier/Dashboard'
import DispatcherDashboard from './pages/dispatcher/Dashboard'
import ProtectedRoute from './components/ProtectedRoute'
import './App.css'

// Компонент для редиректа на основе роли
const RoleRedirect = () => {
  const { user } = useAuth()
  
  console.log('RoleRedirect - user:', user)
  
  if (!user) {
    console.log('No user, redirecting to login')
    return <Navigate to="/login" replace />
  }
  
  console.log('User roles:', user.roles)
  
  // ФИКС: Правильно получаем роль из объекта
  let role = 'initiator'; // значение по умолчанию
  
  if (user.roles && user.roles.length > 0) {
    // Роли приходят как массив объектов: [{id: 1, name: 'initiator', ...}]
    role = user.roles[0].name;
  }
  
  console.log('Detected role:', role)

  // ФИКС: Убедимся что role - строка
  const roleString = String(role).toLowerCase();
  console.log('Role string:', roleString);

  switch(roleString) {
    case 'initiator':
      return <Navigate to="/initiator/dashboard" replace />
    case 'executor':
      return <Navigate to="/executor/dashboard" replace />
    case 'brigadier':
      return <Navigate to="/brigadier/dashboard" replace />
    case 'dispatcher':
      return <Navigate to="/dispatcher/dashboard" replace />
    default:
      console.log('Unknown role, redirecting to initiator dashboard')
      return <Navigate to="/initiator/dashboard" replace />
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
            <Route path="initiator/*" element={<InitiatorRoutes />} />
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
