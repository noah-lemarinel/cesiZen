import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { getEntries } from '../lib/api';
import { PageShell } from '../components/PageShell';

export function DashboardPage() {
  const { user } = useAuth();
  const [entries, setEntries] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      try {
        const data = await getEntries();
        if (active) {
          setEntries(Array.isArray(data) ? data.slice(0, 3) : []);
        }
      } catch (err) {
        if (active) {
          setError(err.message || 'Unable to load your activity');
        }
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }

    load();

    return () => {
      active = false;
    };
  }, []);

  return (
    <PageShell
      title={`Bonjour${user?.name ? `, ${user.name}` : ''} 👋`}
      subtitle="Votre espace personnel pour suivre vos émotions et vos exercices."
    >
      <div className="grid grid-2">
        <Link to="/app/resources" className="card card-link">
          <strong>Ressources</strong>
          <span>Voir les émotions et les exercices disponibles</span>
        </Link>
        <Link to="/app/tracker" className="card card-link">
          <strong>Tracker</strong>
          <span>Ajouter une entrée émotionnelle</span>
        </Link>
      </div>

      <section className="card">
        <div className="section-title">
          <h2>Dernières entrées</h2>
        </div>

        {loading ? <p className="muted">Chargement…</p> : null}
        {error ? <p className="form__error">{error}</p> : null}

        {!loading && entries.length === 0 ? <p className="muted">Aucune entrée pour le moment.</p> : null}

        <ul className="list">
          {entries.map((entry) => (
            <li key={entry.id} className="list__item">
              <div>
                <strong>{entry.emotion}</strong>
                <p>{entry.notes || 'Sans note'}</p>
              </div>
              <time>{entry.created_at ? new Date(entry.created_at).toLocaleString() : '—'}</time>
            </li>
          ))}
        </ul>
      </section>
    </PageShell>
  );
}

