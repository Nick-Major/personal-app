// personal-app-frontend/src/pages/initiator/CreateRequest.jsx
import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../../services/api';
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
    // Основная информация (новый формат)
    work_date: '',
    start_time: '08:00',
    shift_duration: 8,
    workers_count: 1,
    
    // Организационные данные
    brigadier_id: '',
    comments: '',
    
    // Рабочие параметры (новый формат)
    specialty_id: '',
    executor_type: 'our_staff',
    work_type_id: '',
    
    // Финансовые атрибуты
    project: '',
    purpose: '',
    payer_company: ''
  });

  const [availableBrigadiers, setAvailableBrigadiers] = useState([]);
  const [specialties, setSpecialties] = useState([]);
  const [workTypes, setWorkTypes] = useState([]);
  const [availableExecutorTypes, setAvailableExecutorTypes] = useState([]);
  const [errors, setErrors] = useState({});
  const [timeOptions] = useState(generateTimeOptions());
  const [loading, setLoading] = useState(false);

  // Списки для select'ов
  const projects = [
    'Озеленение парка', 'Благоустройство территории', 'Сезонные работы', 'Специальный проект'
  ];

  // Загрузка данных при монтировании
  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      // Загружаем специальности
      const specialtiesResponse = await api.get('/api/specialties');
      setSpecialties(specialtiesResponse.data);
      
      // Загружаем виды работ
      const workTypesResponse = await api.get('/api/work-types');
      setWorkTypes(workTypesResponse.data);
      
    } catch (error) {
      console.error('Ошибка загрузки данных:', error);
      // Fallback на mock данные если API недоступно
      setSpecialties([
        { id: 1, name: 'администраторы' },
        { id: 2, name: 'декораторы' },
        { id: 3, name: 'помощник садовника' },
        { id: 4, name: 'садовники' },
        { id: 5, name: 'садовники (хим. обработка)' },
        { id: 6, name: 'специалисты по озеленению' },
        { id: 7, name: 'старшие администраторы' },
        { id: 8, name: 'старшие декораторы' },
        { id: 9, name: 'старшие садовники' },
        { id: 10, name: 'установщик деревьев' },
        { id: 11, name: 'штатные специалисты' }
      ]);
      
      setWorkTypes([
        { id: 1, name: 'высотные работы' },
        { id: 2, name: 'демонтажные работы' },
        { id: 3, name: 'другое' },
        { id: 4, name: 'монтажные работы' },
        { id: 5, name: 'обработка удобрениями' },
        { id: 6, name: 'погрузочно-разгрузочные работы' },
        { id: 7, name: 'полив растений' },
        { id: 8, name: 'посадка растений' },
        { id: 9, name: 'работы по уходу за растениями' },
        { id: 10, name: 'разгрузка деревьев' },
        { id: 11, name: 'установка деревьев' },
        { id: 12, name: 'установка заборов' }
      ]);
    }
  };

  // Загрузка доступных бригадиров на выбранную дату
  useEffect(() => {
    if (formData.work_date) {
      loadAvailableBrigadiers(formData.work_date);
    } else {
      setAvailableBrigadiers([]);
    }
  }, [formData.work_date]);

  const loadAvailableBrigadiers = async (date) => {
    try {
      const response = await api.get('/api/brigadiers/available', {
        params: { date }
      });
      setAvailableBrigadiers(response.data);
    } catch (error) {
      console.error('Ошибка загрузки бригадиров:', error);
      // Fallback на mock данные
      const mockBrigadiers = [
        { id: 1, name: 'Иван Петров', surname: 'Петров', specialization: 'садовник' },
        { id: 2, name: 'Мария', surname: 'Сидорова', specialization: 'декоратор' },
        { id: 9, name: 'Сергей', surname: 'Иванов', specialization: 'администратор' }
      ];
      setAvailableBrigadiers(mockBrigadiers);
    }
  };

  // Автоматическое определение доступных типов исполнителей
  useEffect(() => {
    if (formData.specialty_id) {
      // TODO: API call для проверки доступности исполнителей
      const types = ['our_staff'];
      const selectedSpecialty = specialties.find(s => s.id == formData.specialty_id);
      if (selectedSpecialty && selectedSpecialty.name.includes('садовник')) {
        types.push('contractor');
      }
      setAvailableExecutorTypes(types);
      
      if (formData.executor_type && !types.includes(formData.executor_type)) {
        setFormData(prev => ({ ...prev, executor_type: '' }));
      }
    } else {
      setAvailableExecutorTypes([]);
    }
  }, [formData.specialty_id, specialties]);

  // Автоматическое заполнение компании-плательщика
  useEffect(() => {
    if (formData.project && formData.purpose) {
      setFormData(prev => ({ 
        ...prev, 
        payer_company: `ООО "${formData.project} Финанс"`
      }));
    }
  }, [formData.project, formData.purpose]);

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

    if (!formData.work_date) newErrors.work_date = 'Укажите дату работ';
    if (!formData.start_time) newErrors.start_time = 'Укажите время начала';
    if (!formData.shift_duration || formData.shift_duration < 1) newErrors.shift_duration = 'Укажите продолжительность';
    if (!formData.workers_count || formData.workers_count < 1) newErrors.workers_count = 'Укажите количество исполнителей';
    if (!formData.brigadier_id) newErrors.brigadier_id = 'Выберите бригадира';
    if (!formData.specialty_id) newErrors.specialty_id = 'Выберите специальность';
    if (!formData.executor_type) newErrors.executor_type = 'Выберите тип исполнителя';
    if (!formData.work_type_id) newErrors.work_type_id = 'Выберите вид работ';
    if (!formData.project) newErrors.project = 'Выберите проект';
    if (!formData.purpose) newErrors.purpose = 'Укажите назначение';

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    if (!validateForm()) {
      setLoading(false);
      return;
    }

    try {
      // ОТЛАДКА ДАТЫ ПЕРЕД ОТПРАВКОЙ
      console.log('=== ОТПРАВЛЯЕМЫЕ ДАННЫЕ ===');
      console.log('work_date:', formData.work_date);
      console.log('work_date тип:', typeof formData.work_date);
      console.log('==========================');
      // Добавляем статус по умолчанию
      const submitData = {
        ...formData,
        status: 'published' // или 'draft' для черновика
      };

      await api.post('/api/work-requests', submitData);
      alert('Заявка успешно опубликована!');
      navigate('/initiator/requests');
    } catch (error) {
      console.error('Ошибка создания заявки:', error);
      alert('Не удалось создать заявку: ' + (error.response?.data?.message || 'Ошибка сервера'));
    } finally {
      setLoading(false);
    }
  };

  const handleSaveDraft = async () => {
    setLoading(true);
    
    if (!validateForm()) {
      setLoading(false);
      return;
    }

    try {
      const submitData = {
        ...formData,
        status: 'draft'
      };

      await api.post('/api/work-requests', submitData);
      alert('Черновик сохранен!');
      navigate('/initiator/requests');
    } catch (error) {
      console.error('Ошибка сохранения черновика:', error);
      alert('Не удалось сохранить черновик');
    } finally {
      setLoading(false);
    }
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
                  name="work_date"
                  value={formData.work_date}
                  onChange={handleInputChange}
                  min={new Date().toISOString().split('T')[0]}
                  className={errors.work_date ? 'error' : ''}
                />
                {errors.work_date && <span className="error-message">{errors.work_date}</span>}
              </div>

              <div className="form-group">
                <label>Время начала *</label>
                <select
                  name="start_time"
                  value={formData.start_time}
                  onChange={handleInputChange}
                  className={errors.start_time ? 'error' : ''}
                  required
                >
                  <option value="">Выберите время</option>
                  {timeOptions.map(time => (
                    <option key={time} value={time}>
                      {time}
                    </option>
                  ))}
                </select>
                {errors.start_time && <span className="error-message">{errors.start_time}</span>}
              </div>

              <div className="form-group">
                <label>Продолжительность (часы) *</label>
                <input
                  type="number"
                  name="shift_duration"
                  value={formData.shift_duration}
                  onChange={handleInputChange}
                  min="1"
                  max="24"
                  className={errors.shift_duration ? 'error' : ''}
                />
                {errors.shift_duration && <span className="error-message">{errors.shift_duration}</span>}
              </div>

              <div className="form-group">
                <label>Количество исполнителей *</label>
                <input
                  type="number"
                  name="workers_count"
                  value={formData.workers_count}
                  onChange={handleInputChange}
                  min="1"
                  max="50"
                  className={errors.workers_count ? 'error' : ''}
                />
                {errors.workers_count && <span className="error-message">{errors.workers_count}</span>}
              </div>
            </div>
          </section>

          {/* Организационные данные */}
          <section className="form-section">
            <h2>🏢 Организационные данные</h2>
            <div className="form-grid">
              <div className="form-group">
                <label>Бригадир *</label>
                <select
                  name="brigadier_id"
                  value={formData.brigadier_id}
                  onChange={handleInputChange}
                  className={errors.brigadier_id ? 'error' : ''}
                  disabled={!formData.work_date}
                >
                  <option value="">{formData.work_date ? 'Выберите бригадира' : 'Сначала выберите дату'}</option>
                  {availableBrigadiers.map(brigadier => (
                    <option key={brigadier.id} value={brigadier.id}>
                      {brigadier.surname} {brigadier.name} ({brigadier.specialization})
                    </option>
                  ))}
                </select>
                {errors.brigadier_id && <span className="error-message">{errors.brigadier_id}</span>}
                {!formData.work_date && (
                  <div className="info-message">Выберите дату для просмотра доступных бригадиров</div>
                )}
              </div>
            </div>

            <div className="form-group">
              <label>Комментарий</label>
              <textarea
                name="comments"
                value={formData.comments}
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
                  name="specialty_id"
                  value={formData.specialty_id}
                  onChange={handleInputChange}
                  className={errors.specialty_id ? 'error' : ''}
                >
                  <option value="">Выберите специальность</option>
                  {specialties.map(spec => (
                    <option key={spec.id} value={spec.id}>{spec.name}</option>
                  ))}
                </select>
                {errors.specialty_id && <span className="error-message">{errors.specialty_id}</span>}
              </div>

              <div className="form-group">
                <label>Тип исполнителя *</label>
                <select
                  name="executor_type"
                  value={formData.executor_type}
                  onChange={handleInputChange}
                  className={errors.executor_type ? 'error' : ''}
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
                {errors.executor_type && <span className="error-message">{errors.executor_type}</span>}
              </div>

              <div className="form-group">
                <label>Вид работ *</label>
                <select
                  name="work_type_id"
                  value={formData.work_type_id}
                  onChange={handleInputChange}
                  className={errors.work_type_id ? 'error' : ''}
                >
                  <option value="">Выберите вид работ</option>
                  {workTypes.map(work => (
                    <option key={work.id} value={work.id}>{work.name}</option>
                  ))}
                </select>
                {errors.work_type_id && <span className="error-message">{errors.work_type_id}</span>}
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
                  name="payer_company"
                  value={formData.payer_company}
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
              disabled={loading}
            >
              {loading ? 'Сохранение...' : 'Сохранить черновик'}
            </button>
            <button
              type="submit"
              className="btn-primary"
              disabled={loading}
            >
              {loading ? 'Публикация...' : 'Опубликовать заявку'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CreateRequest;
