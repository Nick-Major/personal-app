// src/config/executorMenu.js
export const executorMenu = [
  {
    title: 'Рабочие функции',
    items: [
      { 
        path: '/executor/shifts', 
        label: 'Мои смены', 
        icon: '📅' 
      }
    ]
  },
  {
    title: 'Управление',
    items: [
      { 
        path: '/executor/assignments', 
        label: 'Назначения бригадиром', 
        icon: '👨‍💼' 
      }
    ]
  },
  {
    title: 'Личная информация',
    items: [
      { 
        path: '/executor/profile', 
        label: 'Мой профиль', 
        icon: '👤' 
      }
    ]
  }
];
