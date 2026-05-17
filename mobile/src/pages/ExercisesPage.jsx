import React, { useEffect, useMemo, useState } from 'react';
import { getExercises } from '../lib/api';
import { PageShell } from '../components/PageShell';

const DEFAULT_SESSION_SECONDS = 120;

export function ExercisesPage() {
  const [exercises, setExercises] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeExerciseId, setActiveExerciseId] = useState(null);
  const [secondsLeft, setSecondsLeft] = useState(DEFAULT_SESSION_SECONDS);

  const activeExercise = useMemo(
    () => exercises.find((exercise) => exercise.id === activeExerciseId) || null,
    [exercises, activeExerciseId],
  );

  useEffect(() => {
    let active = true;

    async function load() {
      try {
        const data = await getExercises();
        if (active) {
          setExercises(Array.isArray(data) ? data : []);
        }
      } catch (err) {
        if (active) {
          setError(err.message || 'Unable to load exercises');
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

  useEffect(() => {
    if (!activeExerciseId) {
      return undefined;
    }

    if (secondsLeft <= 0) {
      return undefined;
    }

    const timer = window.setInterval(() => {
      setSecondsLeft((current) => Math.max(0, current - 1));
    }, 1000);

    return () => window.clearInterval(timer);
  }, [activeExerciseId, secondsLeft]);

  const startSession = (exerciseId) => {
    setActiveExerciseId(exerciseId);
    setSecondsLeft(DEFAULT_SESSION_SECONDS);
  };

  const stopSession = () => {
    setActiveExerciseId(null);
    setSecondsLeft(DEFAULT_SESSION_SECONDS);
  };

  return (
    <PageShell
      title="Exercices"
      subtitle="Choisissez une respiration guidée et lancez un mini minuteur pour vous recentrer."
    >
      {loading ? <p className="muted">Chargement…</p> : null}
      {error ? <p className="form__error">{error}</p> : null}

      {activeExercise ? (
        <section className="card card-highlight">
          <div className="section-title">
            <h2>{activeExercise.name || activeExercise.title}</h2>
            <span className="badge">{Math.floor(secondsLeft / 60)}:{String(secondsLeft % 60).padStart(2, '0')}</span>
          </div>
          <p>{activeExercise.description || 'Suivez le rythme de votre respiration.'}</p>
          <div className="button-row">
            <button className="button button--secondary" type="button" onClick={stopSession}>
              Arrêter
            </button>
            <button className="button button--primary" type="button" onClick={() => setSecondsLeft(DEFAULT_SESSION_SECONDS)}>
              Réinitialiser
            </button>
          </div>
        </section>
      ) : null}

      <div className="stack">
        {exercises.map((exercise) => (
          <article key={exercise.id} className="card">
            <div className="section-title">
              <h2>{exercise.name || exercise.title}</h2>
              <span className="badge">Respiration</span>
            </div>
            <p>{exercise.description || 'Pas de description disponible.'}</p>
            <button className="button button--primary" type="button" onClick={() => startSession(exercise.id)}>
              Lancer 2 minutes
            </button>
          </article>
        ))}
      </div>
    </PageShell>
  );
}
