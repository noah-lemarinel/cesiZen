import React, { useEffect, useState } from 'react';
import { getEmotions, getExercises } from '../lib/api';
import { PageShell } from '../components/PageShell';

export function ResourcesPage() {
  const [emotions, setEmotions] = useState([]);
  const [exercises, setExercises] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      try {
        const [emotionData, exerciseData] = await Promise.all([getEmotions(), getExercises()]);
        if (active) {
          setEmotions(Array.isArray(emotionData) ? emotionData : []);
          setExercises(Array.isArray(exerciseData) ? exerciseData : []);
        }
      } catch (err) {
        if (active) {
          setError(err.message || 'Unable to load resources');
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
      title="Ressources"
      subtitle="Les émotions à suivre et les exercices de respiration disponibles dans votre backend."
    >
      {loading ? <p className="muted">Chargement…</p> : null}
      {error ? <p className="form__error">{error}</p> : null}

      <section className="card">
        <div className="section-title">
          <h2>Émotions</h2>
          <span className="badge">{emotions.length}</span>
        </div>
        <div className="chip-list">
          {emotions.map((emotion) => (
            <span key={emotion.id} className="chip">
              {emotion.name}
            </span>
          ))}
        </div>
      </section>

      <section className="card">
        <div className="section-title">
          <h2>Exercices</h2>
          <span className="badge">{exercises.length}</span>
        </div>
        <div className="stack">
          {exercises.map((exercise) => (
            <article key={exercise.id} className="mini-card">
              <strong>{exercise.name || exercise.title}</strong>
              <p>{exercise.description || 'Description indisponible.'}</p>
            </article>
          ))}
        </div>
      </section>

      <section className="card">
        <h2>Astuce rapide</h2>
        <p>
          Prenez 60 secondes, respirez lentement, puis enregistrez votre état dans le tracker.
        </p>
      </section>
    </PageShell>
  );
}
