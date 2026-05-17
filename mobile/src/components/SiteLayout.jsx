import React, { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { Header } from './Header';
import { Footer } from './Footer';
import { BottomNav } from './BottomNav';

export function SiteLayout() {
  const location = useLocation();

  useEffect(() => {
    document.body.className = 'bg-white text-gray-900 dark:bg-gray-900 dark:text-white flex flex-col min-h-screen font-sans';
    return () => {
      document.body.className = '';
    };
  }, []);

  return (
    <div className="flex flex-col min-h-screen">
      <Header />
      <main className="flex-1 container mx-auto w-full px-4 py-6">
        <Outlet />
      </main>
      <Footer />
      <BottomNav />
    </div>
  );
}

