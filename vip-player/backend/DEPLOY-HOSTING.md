# FOX PLAYER — Hosting Setup (foxplayer.app)

## Requirements
- PHP 8.2+ (extensions: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo, intl)
- MySQL 5.7+ or MariaDB 10.3+
- Composer (on server or upload vendor folder included in zip)
- SSL certificate (HTTPS required for Stripe & mobile app)

## 1. Upload & extract
1. Upload `foxplayer-web.zip` to your hosting (cPanel File Manager or FTP).
2. Extract to your home folder, e.g. `/home/username/foxplayer/`

## 2. Point domain to `public` folder
In cPanel → **Domains** → **Document Root**:
```
/home/username/foxplayer/public
```
Or create subdomain and set document root to the `public` folder inside the extracted zip.

## 3. Create database
1. cPanel → **MySQL Databases** → create database + user.
2. Grant **ALL PRIVILEGES** to the user on that database.

## 4. Configure environment
1. Copy `.env.hosting` to `.env` in the project root (same folder as `artisan`).
2. Edit `.env` and set:
   - `APP_KEY` — run `php artisan key:generate` via SSH, or paste your key
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `STRIPE_KEY` and `STRIPE_SECRET` (live keys for production)
3. Set `APP_URL=https://foxplayer.app`

## 5. Install (SSH — recommended)
```bash
cd /home/username/foxplayer
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

If no SSH: use cPanel **Terminal** or ask host to run migrations.

## 6. Default logins (change after first login!)
| Panel | URL | Email | Password |
|-------|-----|-------|----------|
| Admin | `https://foxplayer.app/vip-panel-XXXXX` | admin@foxplayer.app | admin12345 |
| Reseller | `https://foxplayer.app/reseller` | reseller@foxplayer.app | reseller12345 |

Admin panel path is in database `app_settings` key `admin_panel_path` (set during seed).

## 7. Site settings (admin panel)
Go to **Site Settings** and verify:
- **site_url** = `https://foxplayer.app`
- **apk_download_url** = `https://foxplayer.app/download/app`
- Stripe enabled/disabled as needed

## 8. App download
APK is at: `https://foxplayer.app/download/app`  
File location: `public/downloads/FOX-PLAYER.apk`

## 9. Mobile app API
Production app uses: `https://foxplayer.app/api/v1`

## Troubleshooting
- **500 error**: Check `storage/logs/laravel.log`, fix `.env` and folder permissions.
- **CSS/admin broken**: Document root must be `public`, not project root.
- **Upload fails**: `storage` and `bootstrap/cache` must be writable (775).
- **QR shows wrong URL**: Admin → Site Settings → `site_url` = `https://foxplayer.app`
