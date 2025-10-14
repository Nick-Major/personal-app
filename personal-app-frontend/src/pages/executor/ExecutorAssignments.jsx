import React, { useState, useEffect } from 'react';
import { executorService } from '../../services/executorService';
import { useAuth } from '../../context/AuthContext';
import './ExecutorAssignments.css';

const ExecutorAssignments = () => {
  const { user } = useAuth();
  const [pendingAssignments, setPendingAssignments] = useState([]);
  const [confirmedAssignments, setConfirmedAssignments] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadAssignments();
  }, []);

  const loadAssignments = async () => {
    try {
      setLoading(true);
      
      // Загружаем оба типа назначений параллельно
      const [pendingData, confirmedData] = await Promise.all([
        executorService.getPendingAssignments(),
        executorService.getConfirmedAssignments()
      ]);
      
      console.log('Pending assignments:', pendingData);
      console.log('Confirmed assignments:', confirmedData);
      
      setPendingAssignments(pendingData);
      setConfirmedAssignments(confirmedData);
    } catch (error) {
      console.error('Error loading assignments:', error);
      alert('Ошибка при загрузке назначений');
    } finally {
      setLoading(false);
    }
  };

  const handleConfirmAssignment = async (assignmentId) => {
    try {
      await executorService.confirmAssignment(assignmentId);
      await loadAssignments();
      alert('Назначение подтверждено!');
    } catch (error) {
      console.error('Error confirming assignment:', error);
      alert('Ошибка при подтверждении назначения');
    }
  };

  const handleRejectAssignment = async (assignmentId) => {
    const reason = prompt('Укажите причину отказа:');
    if (reason && reason.trim()) {
      try {
        await executorService.rejectAssignment(assignmentId, reason.trim());
        await loadAssignments();
        alert('Назначение отклонено');
      } catch (error) {
        console.error('Error rejecting assignment:', error);
        alert('Ошибка при отклонении назначения');
      }
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('ru-RU');
  };

  const getAssignmentDates = (assignment) => {
    return assignment.assignment_dates || assignment.assignmentDates || [];
  };

  if (loading) {
    return (
      <div className="executor-assignments">
        <div className="loading">Загрузка назначений...</div>
      </div>
    );
  }

  return (
    <div className="executor-assignments">
      <div className="page-header">
        <h2>Назначения бригадиром</h2>
        <p>Подтвердите или отклоните назначения на роль бригадира</p>
      </div>

      {/* Ожидающие подтверждения */}
      <div className="assignments-section">
        <h3>Ожидают подтверждения ({pendingAssignments.length})</h3>
        
        {pendingAssignments.length === 0 ? (
          <div className="no-assignments">
            <p>Нет назначений, ожидающих подтверждения</p>
          </div>
        ) : (
          <div className="assignments-grid">
            {pendingAssignments.map(assignment => (
              <div key={assignment.id} className="assignment-card pending">
                <div className="assignment-header">
                  <h4>Назначение от {assignment.initiator?.full_name}</h4>
                  <span className="assignment-date">
                    Создано: {formatDate(assignment.created_at)}
                  </span>
                </div>

                <div className="assignment-dates">
                  <strong>Даты назначения:</strong>
                  <div className="dates-list">
                    {getAssignmentDates(assignment).map(date => (
                      <span key={date.id} className="date-chip">
                        {formatDate(date.assignment_date)}
                        <span className="date-status">{date.status}</span>
                      </span>
                    ))}
                  </div>
                </div>

                {assignment.can_create_requests && (
                  <div className="assignment-rights">
                    <span className="rights-badge">✅ Может создавать заявки</span>
                  </div>
                )}

                <div className="assignment-actions">
                  <button
                    onClick={() => handleConfirmAssignment(assignment.id)}
                    className="btn btn-success"
                  >
                    ✅ Подтвердить
                  </button>
                  <button
                    onClick={() => handleRejectAssignment(assignment.id)}
                    className="btn btn-danger"
                  >
                    ❌ Отклонить
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Подтвержденные назначения */}
      <div className="assignments-section">
        <h3>Подтвержденные назначения ({confirmedAssignments.length})</h3>
        
        {confirmedAssignments.length === 0 ? (
          <div className="no-assignments">
            <p>Нет подтвержденных назначений</p>
          </div>
        ) : (
          <div className="assignments-grid">
            {confirmedAssignments.map(assignment => (
              <div key={assignment.id} className="assignment-card confirmed">
                <div className="assignment-header">
                  <h4>Назначение от {assignment.initiator?.full_name}</h4>
                  <span className="assignment-date">
                    Подтверждено: {formatDate(assignment.confirmed_at)}
                  </span>
                </div>

                <div className="assignment-dates">
                  <strong>Даты назначения:</strong>
                  <div className="dates-list">
                    {getAssignmentDates(assignment).map(date => (
                      <span key={date.id} className="date-chip confirmed">
                        {formatDate(date.assignment_date)}
                      </span>
                    ))}
                  </div>
                </div>

                {assignment.can_create_requests && (
                  <div className="assignment-rights">
                    <span className="rights-badge">✅ Может создавать заявки</span>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default ExecutorAssignments;