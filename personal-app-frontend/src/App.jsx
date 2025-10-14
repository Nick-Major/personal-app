import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Layout from './components/layout/Layout'
import Login from './pages/auth/Login'
import InitiatorRoutes from './pages/initiator/InitiatorRoutes'
import ExecutorAssignments from './pages/executor/ExecutorAssignments'
import ExecutorDashboard from './pages/executor/ExecutorDashboard'
import ExecutorProfile from './pages/executor/ExecutorProfile'
import DispatcherDashboard from './pages/dispatcher/Dashboard'
import ProtectedRoute from './components/ProtectedRoute'
import './App.css'

// Компонент для редиректа на основе роли
const RoleRedirect = () => {
  const { user, loading } = useAuth()

  console.log('=== ROLE REDIRECT ===')
  console.log('Loading:', loading)
  console.log('User:', user)

  if (loading) {
    return <div>Загрузка...</div>
  }

  if (!user) {
    return <Navigate to="/login" replace />
  }

  // Редирект на основе роли
  const userRole = user.roles?.[0]?.name

  switch (userRole) {
    case 'initiator':
      return <Navigate to="/initiator" replace />
    case 'executor':
      return <Navigate to="/executor" replace />
    case 'dispatcher':
      return <Navigate to="/dispatcher" replace />
    default:
      return <Navigate to="/login" replace />
  }
}

const ExecutorRoutes = () => {
  return (
    <Routes>
      <Route index element={<Navigate to="shifts" replace />} />
      <Route path="shifts" element={<ExecutorDashboard />} />
      <Route path="assignments" element={<ExecutorAssignments />} />
      <Route path="profile" element={<ExecutorProfile />} />
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
            <Route path="executor/*" element={<ExecutorRoutes />} />
            <Route path="dispatcher/*" element={<DispatcherDashboard />} />
          </Route>
        </Routes>
      </Router>
    </AuthProvider>
  )
}

export default App
