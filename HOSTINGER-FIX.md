# FOX PLAYER — Hostinger 404 Fix

Your hosting: **Hostinger** (user `u139837297`)

A **404** after upload almost always means the domain is not pointing to Laravel's `public` folder.

---

## Step 1 — Extract the zip (File Manager)

1. Log in to **hPanel** → **Files** → **File Manager**
2. Open folder **`/home/u139837297/`** (home — NOT inside `public_html` yet)
3. If `foxplayer-web.zip` is there → right-click → **Extract**
4. After extract you should see folders like:
   - `app`
   - `public`
   - `vendor`
   - `artisan`
   - `.env.hosting`

If files are inside a subfolder like `foxplayer-web-deploy`, move everything **up** one level so `artisan` is directly in `/home/u139837297/foxplayer/` (recommended: create folder `foxplayer` and put all files there).

**Best structure:**
```
/home/u139837297/foxplayer/
    artisan
    app/
    bootstrap/
    public/        ← website must point HERE
    vendor/
    storage/
    .env
```

---

## Step 2 — Fix document root (MOST IMPORTANT)

1. hPanel → **Websites** → **foxplayer.app** → **Manage**
2. Go to **Advanced** or **Domain settings**
3. Find **Document root** / **Website root**
4. Change from:
   ```
   /home/u139837297/domains/foxplayer.app/public_html
   ```
   To:
   ```
   /home/u139837297/foxplayer/public
   ```
5. **Save** and wait 2–5 minutes

> If you cannot change document root: use **Plan B** at the bottom of this file.

---

## Step 3 — Create `.env` file

1. In File Manager go to `/home/u139837297/foxplayer/`
2. Copy `.env.hosting` → rename to `.env`
3. Or upload `foxplayer-hostinger.env` from your PC and rename to `.env`
4. Edit `.env` — database is already set for your Hostinger DB

---

## Step 4 — Run setup (SSH or Terminal)

### Option A — SSH (from your PC)
```bash
ssh -p 65002 u139837297@194.164.64.35
```
(Enter your Hostinger SSH password when asked)

Then run:
```bash
cd ~/foxplayer
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
```

### Option B — hPanel Terminal
hPanel → **Advanced** → **SSH Access** or **Terminal** → same commands above.

---

## Step 5 — Test

| URL | Should show |
|-----|-------------|
| https://foxplayer.app | FOX PLAYER homepage |
| https://foxplayer.app/upload | Upload page |
| https://foxplayer.app/api/v1 | JSON or route response |

---

## Plan B — If you CANNOT change document root

Keep Laravel in `/home/u139837297/foxplayer/` and only use `public_html` as the web entry:

1. Delete everything inside `domains/foxplayer.app/public_html/` (backup first)
2. Copy **all files inside** `foxplayer/public/` → into `public_html/`
3. Edit `public_html/index.php` — change these two lines:

```php
require __DIR__.'/../foxplayer/vendor/autoload.php';
$app = require_once __DIR__.'/../foxplayer/bootstrap/app.php';
```

(Adjust `foxplayer` if your folder name is different.)

4. Create `.env` in `/home/u139837297/foxplayer/` (not in public_html)

---

## Still 404?

Check in File Manager:
- Does `/home/u139837297/foxplayer/public/index.php` exist?
- Does `/home/u139837297/foxplayer/public/.htaccess` exist?

Enable **Show hidden files** in File Manager (settings gear icon).

---

## Default admin login (after migrate + seed)

- Admin: check DB table `app_settings` key `admin_panel_path` → URL is `https://foxplayer.app/vip-panel-xxxxx`
- Email: `admin@foxplayer.app` / Password: `admin12345`

Change passwords immediately after first login.
