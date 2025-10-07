export const dispatcherMenu = [
  {
    title: "📋 Заявки",
    items: [
      { path: "/dispatcher/requests", label: "Все заявки", badge: "all" },
      { path: "/dispatcher/requests/published", label: "Опубликованные", badge: "published" },
      { path: "/dispatcher/requests/in-progress", label: "В работе", badge: "inProgress" },
      { path: "/dispatcher/requests/staffed", label: "Укомплектованные", badge: "staffed" },
      { path: "/dispatcher/requests/completed", label: "Завершенные", badge: "completed" }
    ]
  },
  {
    title: "👥 Персонал",
    items: [
      { path: "/dispatcher/personnel", label: "Учет персонала" },
      { path: "/dispatcher/assignments", label: "Назначения" },
      { path: "/dispatcher/performers", label: "Исполнители" },
      { path: "/dispatcher/mass-personnel", label: "Массовый персонал" }
    ]
  },
  {
    title: "🤝 Подрядчики",
    items: [
      { path: "/dispatcher/contractors", label: "Список подрядчиков" },
      { path: "/dispatcher/contractor-requests", label: "Заявки подрядчикам" }
    ]
  },
  {
    title: "⏱️ Контроль смен",
    items: [
      { path: "/dispatcher/shift-opening", label: "Открытие смен" },
      { path: "/dispatcher/shift-closing", label: "Закрытие смен" },
      { path: "/dispatcher/active-shifts", label: "Текущие смены" }
    ]
  },
  {
    title: "📊 Отчеты",
    items: [
      { path: "/dispatcher/timesheets", label: "Рабочие табели" },
      { path: "/dispatcher/statistics", label: "Статистика" },
      { path: "/dispatcher/performance", label: "Выполнение заявок" }
    ]
  }
]
