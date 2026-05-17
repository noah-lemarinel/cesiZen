# CesiZen Mobile

A mobile-first React + Vite SPA that consumes the Symfony API.

## Features

- Login and register with JWT
- Fetch emotions and breathing exercises
- Create emotion entries (tracker)
- Uses `credentials: 'include'` on every API request
- Adds `Authorization: Bearer <token>` automatically when a token is available

## Setup

```bash
cd /home/oceane/src/cesiZen/mobile
cp .env.example .env.local
npm install
npm run dev
```

By default the Vite dev server proxies API requests to `http://localhost:8000`.
If your Symfony app runs elsewhere, edit `VITE_API_TARGET` in `.env.local`.

## API endpoints used

- `POST /api/login`
- `POST /api/register`
- `GET /api/emotions`
- `GET /api/exercises`
- `GET /api/entries`
- `POST /api/entries`

## Production / deployment notes

If the SPA is deployed behind the same domain as Symfony, you can leave `VITE_API_BASE_URL` empty.
If it is hosted separately, set `VITE_API_BASE_URL=https://your-api.example`.

