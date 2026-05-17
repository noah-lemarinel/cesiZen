import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';
import { PageShell } from '../components/PageShell';

export function HomePage() {
  const { isAuthenticated } = useAuth();

  return (
    <PageShell title="CesiZen" subtitle="Santé mentale - Bienvenue">
      {/* Hero section */}
      <section className="card card-highlight">
        <div className="grid-2">
          <div>
            <h2 className="text-2xl font-bold mb-4">Mieux comprendre et gérer votre stress</h2>
            <p className="text-gray-600 dark:text-gray-300 mb-6">
              CesiZen vous propose des outils simples et éprouvés pour suivre vos émotions, pratiquer la respiration et
              accéder à des ressources pédagogiques.
            </p>
            <div className="button-row">
              {!isAuthenticated ? (
                <Link to="/register" className="button button--primary">
                  Créer un Compte
                </Link>
              ) : null}
              <Link to={isAuthenticated ? '/emotion/tracker' : '/resources'} className="button button--secondary">
                Découvrir
              </Link>
            </div>
          </div>
          <div className="hidden md:block">
            <div className="bg-gradient-to-br from-primary to-transparent rounded-lg p-6 text-center">
              <svg className="w-24 h-24 mx-auto mb-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" strokeWidth="2" />
                <path
                  d="M8 13c1.2 1 2.8 1 4 0 1.2-1 2-3 2-3s-1.6-1-3-1c-1.4 0-3 1-3 1s-.2 2 .0 3z"
                  fill="currentColor"
                />
              </svg>
              <h3 className="font-semibold text-darkblue dark:text-white">Tracker d'Émotions</h3>
              <p className="text-sm text-darkblue/80 dark:text-gray-300 mt-2">
                Notez vos émotions quotidiennement et visualisez vos tendances
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section>
        <h2 className="text-xl font-bold mb-4">Nos fonctionnalités</h2>
        <div className="stack">
          <article className="card">
            <h3 className="font-semibold mb-2">📊 Tracker d'Émotions</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300">
              Suivez vos humeurs et identifiez les déclencheurs grâce à un journal simple et rapide.
            </p>
          </article>

          <article className="card">
            <h3 className="font-semibold mb-2">🧘 Exercices de Respiration</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300">
              Exercices guidés basés sur la cohérence cardiaque pour réduire le stress en quelques minutes.
            </p>
          </article>

          <article className="card">
            <h3 className="font-semibold mb-2">📚 Ressources Pédagogiques</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300">
              Articles, guides et astuces pour mieux comprendre la santé mentale et les bonnes pratiques.
            </p>
          </article>
        </div>
      </section>

      {!isAuthenticated ? (
        <section className="card bg-gradient-to-r from-primary to-secondary dark:from-secondary dark:to-primary text-white">
          <h3 className="text-lg font-bold mb-2">Commencez dès maintenant</h3>
          <p className="text-sm opacity-90 mb-4">
            Créez un compte gratuit et commencez à suivre votre bien-être aujourd'hui.
          </p>
          <div className="button-row">
            <Link to="/register" className="button button--ghost">
              Créer un Compte
            </Link>
            <Link to="/login" className="button button--ghost">
              Se Connecter
            </Link>
          </div>
        </section>
      ) : null}

      {/* Additional info */}
      <section>
        <div className="grid-2">
          <div className="card">
            <h3 className="font-semibold mb-2">📈 Rapports & Tendances</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-3">
              Visualisez vos progrès au fil du temps avec des graphiques clairs et des résumés intelligents.
            </p>
            <Link to="/resources" className="text-primary dark:text-secondary text-sm font-medium hover:underline">
              Voir un exemple →
            </Link>
          </div>

          <div className="card">
            <h3 className="font-semibold mb-2">🔒 Support & Confidentialité</h3>
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-3">
              Vos données sont privées et stockées en toute sécurité. Accédez au support à tout moment.
            </p>
            <Link to="/resources" className="text-primary dark:text-secondary text-sm font-medium hover:underline">
              En savoir plus →
            </Link>
          </div>
        </div>
      </section>
    </PageShell>
  );
}

