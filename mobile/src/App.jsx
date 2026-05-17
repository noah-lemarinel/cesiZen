import React from 'react';
import { Route, Routes, Navigate } from 'react-router-dom';
import { ProtectedRoute } from './components/ProtectedRoute';
import { SiteLayout } from './components/SiteLayout';
import { AccountPage } from './pages/AccountPage';
import { DashboardPage } from './pages/DashboardPage';
import { ExercisesPage } from './pages/ExercisesPage';
import { HomePage } from './pages/HomePage';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { ResourcesPage } from './pages/ResourcesPage';
import { TrackerPage } from './pages/TrackerPage';

export default function App() {
  return (
    <Routes>
      <Route element={<SiteLayout />}>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/resources" element={<ResourcesPage />} />
        <Route path="/exercises" element={<ExercisesPage />} />

        <Route element={<ProtectedRoute />}>
          <Route path="/emotion/tracker" element={<TrackerPage />} />
          <Route path="/account" element={<AccountPage />} />
        </Route>

        {/* Backward-compatible redirects for the previous SPA routes */}
        <Route path="/app" element={<Navigate to="/" replace />} />
        <Route path="/app/resources" element={<Navigate to="/resources" replace />} />
        <Route path="/app/tracker" element={<Navigate to="/emotion/tracker" replace />} />
        <Route path="/app/exercises" element={<Navigate to="/exercises" replace />} />
        <Route path="/app/account" element={<Navigate to="/account" replace />} />
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
