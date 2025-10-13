import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import './Login.css'

const Login = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const { login } = useAuth()
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      console.log('Attempting login with:', email)
      const response = await login(email, password)
      console.log('Login successful, redirecting...')
      console.log('User roles:', response.user.roles)

      // Редирект на основе роли - ИСПРАВЛЕНО: берем name роли
      const role = response.user.roles[0]?.name || 'initiator'
      console.log('Redirecting to role:', role)
      navigate(`/${role}/dashboard`)
    } catch (err) {
      console.log('Login error:', err)
      setError(err.response?.data?.message || 'Неверный email или пароль')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="login-container">
      <form className="login-form" onSubmit={handleSubmit}>
        <h2>Вход в систему</h2>
        {error && <div className="error-message">{error}</div>}
        <div className="form-group">
          <label>Email:</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div className="form-group">
          <label>Пароль:</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button type="submit" disabled={loading}>
          {loading ? 'Вход...' : 'Войти'}
        </button>
      </form>
    </div>
  )
}

export default Login
