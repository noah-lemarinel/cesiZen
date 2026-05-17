import React, { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { PageShell } from '../components/PageShell';

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();
  const [form, setForm] = useState({ email: '', password: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setError('');

    try {
      await login(form);
      const target = location.state?.from?.pathname || '/';
      navigate(target, { replace: true });
    } catch (err) {
      setError(err.message || 'Unable to log in');
    } finally {
      setLoading(false);
    }
  };

  return (
    <PageShell title="Bienvenue" subtitle="Connectez-vous pour retrouver vos ressources et votre tracker.">
      <form className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 space-y-4" onSubmit={handleSubmit}>
        <label className="block">
          <span className="block text-sm text-gray-600 dark:text-gray-300 mb-2">Email</span>
          <input
            type="email"
            autoComplete="email"
            value={form.email}
            onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))}
            placeholder="vous@exemple.com"
            className="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary"
            required
          />
        </label>

        <label className="block">
          <span className="block text-sm text-gray-600 dark:text-gray-300 mb-2">Mot de passe</span>
          <input
            type="password"
            autoComplete="current-password"
            value={form.password}
            onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
            placeholder="••••••••"
            className="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-secondary"
            required
          />
        </label>

        {error ? <p className="text-alert text-sm font-medium">{error}</p> : null}

        <button
          type="submit"
          className="w-full bg-secondary text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-darkblue transition-colors duration-200"
          disabled={loading}
        >
          {loading ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>

      <p className="mt-4 text-center text-sm text-gray-600 dark:text-gray-300">
        Pas de compte ?{' '}
        <Link to="/register" className="text-secondary dark:text-primary font-semibold hover:underline">
          Créer un compte
        </Link>
      </p>
    </PageShell>
  );
}
