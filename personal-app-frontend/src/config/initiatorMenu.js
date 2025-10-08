// personal-app-frontend/src/config/initiatorMenu.js
export const initiatorMenu = [
  {
    title: "📊 Дашборд",
    items: [
      { path: "/initiator/dashboard", label: "Обзор" }
    ]
  },
  {
    title: "👑 Бригадиры", 
    items: [
      { path: "/initiator/brigadier-management", label: "Планирование" }
    ]
  },
  {
    title: "📋 Заявки",
    items: [
      { path: "/initiator/requests", label: "Мои заявки" },
      { path: "/initiator/create-request", label: "Создать заявку" }
    ]
  }
];
