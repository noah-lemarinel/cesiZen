import React from 'react';

export function PageShell({ title, subtitle, actions, children, maxWidthClass = 'max-w-2xl' }) {
  return (
    <div className="container mx-auto px-6 py-12">
      <div className={`${maxWidthClass} mx-auto`}>
        <div className="flex items-end justify-between gap-4 mb-8 flex-col sm:flex-row sm:items-end">
          <div>
            <h1 className="text-3xl font-bold mb-2">{title}</h1>
            {subtitle ? <p className="text-gray-600 dark:text-gray-300">{subtitle}</p> : null}
          </div>
          {actions ? <div className="flex gap-2">{actions}</div> : null}
        </div>
        {children}
      </div>
    </div>
  );
}
