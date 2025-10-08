// personal-app-frontend/src/pages/initiator/InitiatorRoutes.jsx
import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Dashboard from './Dashboard';
import BrigadierManagement from './BrigadierManagement';
import Requests from './Requests';
import CreateRequest from './CreateRequest';

const InitiatorRoutes = () => {
  return (
    <Routes>
      <Route path="dashboard" element={<Dashboard />} />
      <Route path="brigadier-management" element={<BrigadierManagement />} />
      <Route path="requests" element={<Requests />} />
      <Route path="create-request" element={<CreateRequest />} />
    </Routes>
  );
};

export default InitiatorRoutes;
