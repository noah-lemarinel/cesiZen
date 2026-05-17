import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { PageShell } from '../components/PageShell';

export function RegisterPage() {
  const navigate = useNavigate();
  const { register } = useAuth();
  const [form, setForm] = useState({ name: '', email: '', password: '', confirmPassword: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    setError('');

    if (form.password !== form.confirmPassword) {
      setError('Les mots de passe ne correspondent pas.');
      setLoading(false);
      return;
    }

    try {
      await register(form);
      navigate('/app', { replace: true });
    } catch (err) {
      setError(err.message || 'Unable to register');
    } finally {
      setLoading(false);
    }
  };

  return (
    <PageShell title="Créer un compte" subtitle="Rejoignez CesiZen en quelques secondes.">
      <form className="card form" onSubmit={handleSubmit}>
        <label>
          Nom
          <input
            type="text"
            autoComplete="name"
            value={form.name}
            onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))}
            placeholder="Votre prénom"
          />
        </label>

        <label>
          Email
          <input
            type="email"
            autoComplete="email"
            value={form.email}
            onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))}
            placeholder="vous@exemple.com"
            required
          />
        </label>

        <label>
          Mot de passe
          <input
            type="password"
            autoComplete="new-password"
            value={form.password}
            onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
            placeholder="Au moins 6 caractères"
            minLength={6}
            required
          />
        </label>

        <label>
          Confirmer le mot de passe
          <input
            type="password"
            autoComplete="new-password"
            value={form.confirmPassword}
            onChange={(event) => setForm((current) => ({ ...current, confirmPassword: event.target.value }))}
            placeholder="Retapez le mot de passe"
            minLength={6}
            required
          />
        </label>

        {error ? <p className="form__error">{error}</p> : null}

        <button type="submit" className="button button--primary" disabled={loading}>
          {loading ? 'Création…' : 'Créer le compte'}
        </button>
      </form>

      <p className="muted center">
        Déjà un compte ? <Link to="/login">Se connecter</Link>
      </p>
    </PageShell>
  );
}

