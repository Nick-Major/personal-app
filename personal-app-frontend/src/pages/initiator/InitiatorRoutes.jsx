// personal-app-frontend/src/pages/initiator/InitiatorRoutes.jsx
import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Dashboard from './Dashboard';
import BrigadierManagement from './BrigadierManagement';

const InitiatorRoutes = () => {
  return (
    <Routes>
      <Route path="dashboard" element={<Dashboard />} />
      <Route path="brigadier-management" element={<BrigadierManagement />} />
      {/* Добавим другие роуты позже */}
    </Routes>
  );
};

export default InitiatorRoutes;
