// personal-app-frontend/src/pages/initiator/CreateRequest.jsx
import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './CreateRequest.css';

const CreateRequest = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    // Основная информация
    date: '',
    time: '08:00',
    duration: 8,
    workersCount: 1,
    
    // Организационные данные
    address: '',
    brigadierId: '',
    contactPerson: '',
    comment: '',
    
    // Рабочие параметры
    specialization: '',
    executorType: '',
    workType: '',
    
    // Финансовые атрибуты
    project: '',
    purpose: '',
    payerCompany: ''
  });

  const [availableBrigadiers, setAvailableBrigadiers] = useState([]);
  const [showContactPerson, setShowContactPerson] = useState(false);
  const [availableExecutorTypes, setAvailableExecutorTypes] = useState([]);

  // Списки для select'ов
  const specializations = [
    'администраторы',
    'декораторы', 
    'помощник садовника',
    'садовники',
    'садовники (хим. обработка)',
    'специалисты по озеленению',
    'старшие администраторы',
    'старшие декораторы',
    'старшие садовники',
    'установщик деревьев',
    'штатные специалисты'
  ];

  const workTypes = [
    'высотные работы',
    'демонтажные работы', 
    'другое',
    'монтажные работы',
    'обработка удобрениями',
    'погрузочно-разгрузочные работы',
    'полив растений',
    'посадка растений',
    'работы по уходу за растениями',
    'разгрузка деревьев',
    'установка деревьев',
    'установка заборов'
  ];

  const projects = [
    'Озеленение парка',
    'Благоустройство территории',
    'Сезонные работы',
    'Специальный проект'
  ];

  // Загрузка доступных бригадиров
  useEffect(() => {
    // TODO: API call для получения бригадиров на выбранную дату
    const mockBrigadiers = [
      { id: 1, name: 'Иван Петров', specialization: 'садовник' },
      { id: 2, name: 'Мария Сидорова', specialization: 'декоратор' },
      { id: 3, name: 'Контактное лицо' }
    ];
    setAvailableBrigadiers(mockBrigadiers);
  }, [formData.date]);

  // Автоматическое определение доступных типов исполнителей
  useEffect(() => {
    if (formData.specialization) {
      // TODO: API call для проверки доступности исполнителей
      // Пока mock логика
      const types = ['our_staff'];
      if (formData.specialization.includes('садовник')) {
        types.push('contractor');
      }
      setAvailableExecutorTypes(types);
      
      // Сбрасываем выбранный тип если он стал недоступен
      if (formData.executorType && !types.includes(formData.executorType)) {
        setFormData(prev => ({ ...prev, executorType: '' }));
      }
    } else {
      setAvailableExecutorTypes([]);
    }
  }, [formData.specialization]);

  // Обработка выбора бригадира
  const handleBrigadierChange = (e) => {
    const value = e.target.value;
    setFormData(prev => ({ ...prev, brigadierId: value }));
    setShowContactPerson(value === '3'); // ID контактного лица
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // TODO: API call для сохранения заявки
    console.log('Создание заявки:', formData);
    navigate('/initiator/requests');
  };

  const handleSaveDraft = () => {
    // TODO: API call для сохранения черновика
    console.log('Сохранение черновика:', formData);
    navigate('/initiator/requests');
  };

  const isFormValid = () => {
    return formData.date &&
           formData.time &&
           formData.duration > 0 &&
           formData.workersCount > 0 &&
           formData.address &&
           (formData.brigadierId || formData.contactPerson) &&
           formData.specialization &&
           formData.executorType &&
           formData.workType &&
           formData.project &&
           formData.purpose;
  };

  return (
    <div className="create-request-page">
      <div className="page-header">
        <div className="formation-rules">
            <h3>🔒 Правила формирования заявки</h3>
            <ul>
                <li>Одна заявка = одна специальность + один тип исполнителя + один Бригадир</li>
                <li>Каждая заявка может содержать только одну специальность исполнителя</li>
                <li>Каждая заявка может содержать только один тип исполнителя</li>
                <li>Каждая заявка может содержать только одного Бригадира/Контактное лицо</li>
            </ul>
        </div>
        <div className="header-content">
          <h1>Оформление заявки</h1>
          <Link to="/initiator/requests" className="back-link">
            ← Назад к заявкам
          </Link>
        </div>
        <p>Заполните все поля для создания новой заявки</p>
      </div>

      <form onSubmit={handleSubmit} className="request-form">
        {/* Основная информация */}
        <section className="form-section">
          <h2>📅 Основная информация</h2>
          <div className="form-grid">
            <div className="form-group">
              <label>Дата выполнения работ *</label>
              <input
                type="date"
                name="date"
                value={formData.date}
                onChange={handleInputChange}
                min={new Date().toISOString().split('T')[0]}
                required
              />
            </div>

            <div className="form-group">
              <label>Время начала *</label>
              <input
                type="time"
                name="time"
                value={formData.time}
                onChange={handleInputChange}
                required
              />
            </div>

            <div className="form-group">
              <label>Продолжительность смены (часы) *</label>
              <input
                type="number"
                name="duration"
                value={formData.duration}
                onChange={handleInputChange}
                min="1"
                max="24"
                required
              />
            </div>

            <div className="form-group">
              <label>Количество исполнителей *</label>
              <input
                type="number"
                name="workersCount"
                value={formData.workersCount}
                onChange={handleInputChange}
                min="1"
                max="50"
                required
              />
            </div>
          </div>
        </section>

        {/* Организационные данные */}
        <section className="form-section">
          <h2>🏢 Организационные данные</h2>
          <div className="form-grid">
            <div className="form-group full-width">
              <label>Адрес места работ *</label>
              <input
                type="text"
                name="address"
                value={formData.address}
                onChange={handleInputChange}
                placeholder="ул. Примерная, 123"
                required
              />
            </div>

            <div className="form-group">
              <label>Бригадир/Контактное лицо *</label>
              <select
                name="brigadierId"
                value={formData.brigadierId}
                onChange={handleBrigadierChange}
                required
              >
                <option value="">Выберите...</option>
                {availableBrigadiers.map(brigadier => (
                  <option key={brigadier.id} value={brigadier.id}>
                    {brigadier.name}
                  </option>
                ))}
              </select>
            </div>

            {showContactPerson && (
              <div className="form-group">
                <label>ФИО контактного лица *</label>
                <input
                  type="text"
                  name="contactPerson"
                  value={formData.contactPerson}
                  onChange={handleInputChange}
                  placeholder="Иванов Иван Иванович"
                  required
                />
              </div>
            )}
          </div>

          <div className="form-group">
            <label>Комментарий</label>
            <textarea
              name="comment"
              value={formData.comment}
              onChange={handleInputChange}
              placeholder="ФИО желаемых исполнителей, детали работ, требования по одежде и т.д."
              rows="3"
            />
          </div>
        </section>

        {/* Рабочие параметры */}
        <section className="form-section">
          <h2>🔧 Рабочие параметры</h2>
          <div className="form-grid">
            <div className="form-group">
              <label>Специальность исполнителя *</label>
              <select
                name="specialization"
                value={formData.specialization}
                onChange={handleInputChange}
                required
              >
                <option value="">Выберите специальность</option>
                {specializations.map(spec => (
                  <option key={spec} value={spec}>{spec}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Тип исполнителя *</label>
              <select
                name="executorType"
                value={formData.executorType}
                onChange={handleInputChange}
                required
                disabled={availableExecutorTypes.length === 0}
              >
                <option value="">Выберите тип</option>
                {availableExecutorTypes.map(type => (
                  <option key={type} value={type}>
                    {type === 'our_staff' ? 'Наш сотрудник' : 'От подрядчика'}
                  </option>
                ))}
              </select>
              {availableExecutorTypes.length === 0 && formData.specialization && (
                <div className="info-message">
                  Выберите специальность для определения доступных типов
                </div>
              )}
            </div>

            <div className="form-group">
              <label>Вид работ *</label>
              <select
                name="workType"
                value={formData.workType}
                onChange={handleInputChange}
                required
              >
                <option value="">Выберите вид работ</option>
                {workTypes.map(work => (
                  <option key={work} value={work}>{work}</option>
                ))}
              </select>
            </div>
          </div>
        </section>

        {/* Финансовые атрибуты */}
        <section className="form-section">
          <h2>💰 Финансовые атрибуты</h2>
          <div className="form-grid">
            <div className="form-group">
              <label>Проект *</label>
              <select
                name="project"
                value={formData.project}
                onChange={handleInputChange}
                required
              >
                <option value="">Выберите проект</option>
                {projects.map(project => (
                  <option key={project} value={project}>{project}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Назначение *</label>
              <input
                type="text"
                name="purpose"
                value={formData.purpose}
                onChange={handleInputChange}
                placeholder="Конкретная задача в рамках проекта"
                required
              />
            </div>

            <div className="form-group">
              <label>Компания-плательщик</label>
              <input
                type="text"
                name="payerCompany"
                value={formData.payerCompany}
                onChange={handleInputChange}
                placeholder="Определяется автоматически"
                readOnly
              />
            </div>
          </div>
        </section>

        {/* Кнопки действий */}
        <div className="form-actions">
          <button
            type="button"
            onClick={handleSaveDraft}
            className="btn-secondary"
          >
            Сохранить черновик
          </button>
          <button
            type="submit"
            disabled={!isFormValid()}
            className="btn-primary"
          >
            Опубликовать заявку
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateRequest;
