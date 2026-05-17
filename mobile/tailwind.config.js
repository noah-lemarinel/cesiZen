/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{js,ts,jsx,tsx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#7FD8BE',
        secondary: '#0C7489',
        darkblue: '#0D1321',
        lightgray: '#F5F5F5',
        alert: '#D32F2F',
      },
      fontFamily: {
        sans: ['"Open Sans"', 'ui-sans-serif', 'system-ui'],
      },
    },
  },
  plugins: [],
};

