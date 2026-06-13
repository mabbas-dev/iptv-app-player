# iptv-app-player

FOX PLAYER — IPTV media player platform with Laravel backend, Expo React Native app, and web upload/activation portal.

## Structure

```
vip-player/
  backend/          Laravel API + admin/reseller panels + upload site
  apps/player-app/  Expo React Native Android / Android TV player
```

## Production

- Website: https://foxplayer.app
- API: https://foxplayer.app/api/v1

## Local setup

### Backend

```bash
cd vip-player/backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Player app

```bash
cd vip-player/apps/player-app
npm install
npx expo start
```

Set `EXPO_PUBLIC_API_URL` in `.env` to your backend URL.

## Features

- Device registration with MAC-style device code
- Web upload for Xtream, M3U, and direct streams
- Metadata proxy: app syncs playlists over HTTPS via backend (HTTP IPTV providers supported)
- Direct stream playback from IPTV servers
- Parental lock, MAC lock, reseller panel, Stripe activation
