import React, { useEffect, useMemo, useState } from 'react';
import { createEntry, getEntries, getEmotions } from '../lib/api';
import { PageShell } from '../components/PageShell';

export function TrackerPage() {
  const [emotions, setEmotions] = useState([]);
  const [entries, setEntries] = useState([]);
  const [form, setForm] = useState({ emotion_id: '', notes: '' });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const selectedEmotion = useMemo(
    () => emotions.find((emotion) => String(emotion.id) === String(form.emotion_id)),
    [emotions, form.emotion_id],
  );

  const refresh = async () => {
    const [emotionData, entryData] = await Promise.all([getEmotions(), getEntries()]);
    setEmotions(Array.isArray(emotionData) ? emotionData : []);
    setEntries(Array.isArray(entryData) ? entryData : []);
    setForm((current) => ({
      ...current,
      emotion_id: current.emotion_id || String(emotionData?.[0]?.id || ''),
    }));
  };

  useEffect(() => {
    let active = true;

    async function load() {
      try {
        await refresh();
      } catch (err) {
        if (active) {
          setError(err.message || 'Unable to load tracker data');
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

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError('');
    setSuccess('');

    try {
      await createEntry({ emotion_id: Number(form.emotion_id), notes: form.notes });
      await refresh();
      setSuccess('Entrée enregistrée.');
      setForm((current) => ({ ...current, notes: '' }));
    } catch (err) {
      setError(err.message || 'Unable to save entry');
    } finally {
      setSaving(false);
    }
  };

  return (
    <PageShell title="Tracker" subtitle="Enregistrez votre humeur et suivez votre évolution dans le temps.">
      {loading ? <p className="muted">Chargement…</p> : null}
      {error ? <p className="form__error">{error}</p> : null}
      {success ? <p className="form__success">{success}</p> : null}

      <form className="card form" onSubmit={handleSubmit}>
        <label>
          Émotion
          <select
            value={form.emotion_id}
            onChange={(event) => setForm((current) => ({ ...current, emotion_id: event.target.value }))}
            required
          >
            <option value="" disabled>
              Choisissez une émotion
            </option>
            {emotions.map((emotion) => (
              <option key={emotion.id} value={emotion.id}>
                {emotion.name}
              </option>
            ))}
          </select>
        </label>

        {selectedEmotion ? <p className="muted">Sélection : {selectedEmotion.name}</p> : null}

        <label>
          Notes
          <textarea
            rows="4"
            value={form.notes}
            onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
            placeholder="Décrivez ce que vous ressentez, ce qui s'est passé, ce qui vous aiderait…"
          />
        </label>

        <button type="submit" className="button button--primary" disabled={saving || !form.emotion_id}>
          {saving ? 'Enregistrement…' : 'Enregistrer'}
        </button>
      </form>

      <section className="card">
        <div className="section-title">
          <h2>Historique</h2>
          <span className="badge">{entries.length}</span>
        </div>
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

