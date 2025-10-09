// personal-app-frontend/src/pages/initiator/CreateRequest.jsx
import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './CreateRequest.css';

// Добавим функцию для генерации времени с шагом 5 минут
  const generateTimeOptions = () => {
    const times = [];
    for (let hour = 0; hour < 24; hour++) {
      for (let minute = 0; minute < 60; minute += 5) {
        const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        times.push(timeString);
      }
    }
    return times;
  };

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
  const [errors, setErrors] = useState({});
  const [timeOptions] = useState(generateTimeOptions());

  // Списки для select'ов
  const specializations = [
    'администраторы', 'декораторы', 'помощник садовника', 'садовники',
    'садовники (хим. обработка)', 'специалисты по озеленению', 'старшие администраторы',
    'старшие декораторы', 'старшие садовники', 'установщик деревьев', 'штатные специалисты'
  ];

  const workTypes = [
    'высотные работы', 'демонтажные работы', 'другое', 'монтажные работы',
    'обработка удобрениями', 'погрузочно-разгрузочные работы', 'полив растений',
    'посадка растений', 'работы по уходу за растениями', 'разгрузка деревьев',
    'установка деревьев', 'установка заборов'
  ];

  const projects = [
    'Озеленение парка', 'Благоустройство территории', 'Сезонные работы', 'Специальный проект'
  ];

  // Загрузка доступных бригадиров на выбранную дату
  useEffect(() => {
    if (formData.date) {
      // TODO: API call для получения подтверждённых бригадиров на дату
      const mockBrigadiers = [
        { id: 1, name: 'Иван Петров (садовник)', specialization: 'садовник', status: 'confirmed' },
        { id: 2, name: 'Мария Сидорова (декоратор)', specialization: 'декоратор', status: 'confirmed' },
        { id: 3, name: 'Контактное лицо' }
      ];
      
      // Фильтруем только подтверждённых бригадиров
      const confirmedBrigadiers = mockBrigadiers.filter(b => 
        b.id === 3 || b.status === 'confirmed'
      );
      
      setAvailableBrigadiers(confirmedBrigadiers);
    } else {
      setAvailableBrigadiers([]);
    }
  }, [formData.date]);

  // Автоматическое определение доступных типов исполнителей
  useEffect(() => {
    if (formData.specialization) {
      // TODO: API call для проверки доступности исполнителей
      const types = ['our_staff'];
      if (formData.specialization.includes('садовник')) {
        types.push('contractor');
      }
      setAvailableExecutorTypes(types);
      
      if (formData.executorType && !types.includes(formData.executorType)) {
        setFormData(prev => ({ ...prev, executorType: '' }));
      }
    } else {
      setAvailableExecutorTypes([]);
    }
  }, [formData.specialization]);

  // Автоматическое заполнение компании-плательщика
  useEffect(() => {
    if (formData.project && formData.purpose) {
      // TODO: Логика определения компании-плательщика
      setFormData(prev => ({ 
        ...prev, 
        payerCompany: `ООО "${formData.project} Финанс"`
      }));
    }
  }, [formData.project, formData.purpose]);

  const handleBrigadierChange = (e) => {
    const value = e.target.value;
    setFormData(prev => ({ ...prev, brigadierId: value }));
    setShowContactPerson(value === '3');
    if (value === '3') {
      setFormData(prev => ({ ...prev, contactPerson: '' }));
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    // Очищаем ошибку при изменении поля
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.date) newErrors.date = 'Укажите дату работ';
    if (!formData.time) newErrors.time = 'Укажите время начала';
    if (!formData.duration || formData.duration < 1) newErrors.duration = 'Укажите продолжительность';
    if (!formData.workersCount || formData.workersCount < 1) newErrors.workersCount = 'Укажите количество исполнителей';
    if (!formData.address) newErrors.address = 'Укажите адрес';
    if (!formData.brigadierId && !formData.contactPerson) newErrors.brigadierId = 'Выберите бригадира или укажите контактное лицо';
    if (!formData.specialization) newErrors.specialization = 'Выберите специальность';
    if (!formData.executorType) newErrors.executorType = 'Выберите тип исполнителя';
    if (!formData.workType) newErrors.workType = 'Выберите вид работ';
    if (!formData.project) newErrors.project = 'Выберите проект';
    if (!formData.purpose) newErrors.purpose = 'Укажите назначение';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    // TODO: API call для публикации заявки
    console.log('Публикация заявки:', formData);
    
    // Показываем успешное сообщение
    alert('Заявка успешно опубликована!');
    navigate('/initiator/requests');
  };

  const handleSaveDraft = () => {
    if (!validateForm()) {
      return;
    }

    // TODO: API call для сохранения черновика
    console.log('Сохранение черновика:', formData);
    
    alert('Черновик сохранен!');
    navigate('/initiator/requests');
  };

  return (
    <div className="create-request-page">
      <div className="page-header">
        <div className="header-content">
          <h1>Оформление заявки</h1>
          <Link to="/initiator/requests" className="back-link">
            ← Назад к заявкам
          </Link>
        </div>
      </div>

      <div className="request-form-container">
        <div className="formation-rules">
          <h3>🔒 Правила формирования заявки</h3>
          <ul>
            <li>Одна заявка = одна специальность + один тип исполнителя + один Бригадир</li>
            <li>Бригадир должен быть подтверждён на выбранную дату</li>
            <li>После публикации заявка становится видимой для диспетчеров</li>
          </ul>
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
                  className={errors.date ? 'error' : ''}
                />
                {errors.date && <span className="error-message">{errors.date}</span>}
              </div>

              <div className="form-group">
                <label>Время начала *</label>
                <select
                    name="time"
                    value={formData.time}
                    onChange={handleInputChange}
                    className={errors.time ? 'error' : ''}
                    required
                  >
                    <option value="">Выберите время</option>
                    {timeOptions.map(time => (
                      <option key={time} value={time}>
                        {time}
                      </option>
                    ))}
                  </select>
                {errors.time && <span className="error-message">{errors.time}</span>}
              </div>

              <div className="form-group">
                <label>Продолжительность (часы) *</label>
                <input
                  type="number"
                  name="duration"
                  value={formData.duration}
                  onChange={handleInputChange}
                  min="1"
                  max="24"
                  className={errors.duration ? 'error' : ''}
                />
                {errors.duration && <span className="error-message">{errors.duration}</span>}
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
                  className={errors.workersCount ? 'error' : ''}
                />
                {errors.workersCount && <span className="error-message">{errors.workersCount}</span>}
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
                  className={errors.address ? 'error' : ''}
                />
                {errors.address && <span className="error-message">{errors.address}</span>}
              </div>

              <div className="form-group">
                <label>Бригадир/Контактное лицо *</label>
                <select
                  name="brigadierId"
                  value={formData.brigadierId}
                  onChange={handleBrigadierChange}
                  className={errors.brigadierId ? 'error' : ''}
                  disabled={!formData.date}
                >
                  <option value="">{formData.date ? 'Выберите...' : 'Сначала выберите дату'}</option>
                  {availableBrigadiers.map(brigadier => (
                    <option key={brigadier.id} value={brigadier.id}>
                      {brigadier.name}
                    </option>
                  ))}
                </select>
                {errors.brigadierId && <span className="error-message">{errors.brigadierId}</span>}
                {!formData.date && (
                  <div className="info-message">Выберите дату для просмотра доступных бригадиров</div>
                )}
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
                  className={errors.specialization ? 'error' : ''}
                >
                  <option value="">Выберите специальность</option>
                  {specializations.map(spec => (
                    <option key={spec} value={spec}>{spec}</option>
                  ))}
                </select>
                {errors.specialization && <span className="error-message">{errors.specialization}</span>}
              </div>

              <div className="form-group">
                <label>Тип исполнителя *</label>
                <select
                  name="executorType"
                  value={formData.executorType}
                  onChange={handleInputChange}
                  className={errors.executorType ? 'error' : ''}
                  disabled={availableExecutorTypes.length === 0}
                >
                  <option value="">
                    {availableExecutorTypes.length === 0 ? 'Сначала выберите специальность' : 'Выберите тип'}
                  </option>
                  {availableExecutorTypes.map(type => (
                    <option key={type} value={type}>
                      {type === 'our_staff' ? 'Наш сотрудник' : 'От подрядчика'}
                    </option>
                  ))}
                </select>
                {errors.executorType && <span className="error-message">{errors.executorType}</span>}
              </div>

              <div className="form-group">
                <label>Вид работ *</label>
                <select
                  name="workType"
                  value={formData.workType}
                  onChange={handleInputChange}
                  className={errors.workType ? 'error' : ''}
                >
                  <option value="">Выберите вид работ</option>
                  {workTypes.map(work => (
                    <option key={work} value={work}>{work}</option>
                  ))}
                </select>
                {errors.workType && <span className="error-message">{errors.workType}</span>}
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
                  className={errors.project ? 'error' : ''}
                >
                  <option value="">Выберите проект</option>
                  {projects.map(project => (
                    <option key={project} value={project}>{project}</option>
                  ))}
                </select>
                {errors.project && <span className="error-message">{errors.project}</span>}
              </div>

              <div className="form-group">
                <label>Назначение *</label>
                <input
                  type="text"
                  name="purpose"
                  value={formData.purpose}
                  onChange={handleInputChange}
                  placeholder="Конкретная задача в рамках проекта"
                  className={errors.purpose ? 'error' : ''}
                />
                {errors.purpose && <span className="error-message">{errors.purpose}</span>}
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
              className="btn-primary"
            >
              Опубликовать заявку
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CreateRequest;
