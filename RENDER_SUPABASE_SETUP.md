# 🚀 Step-by-Step Guide: Deploying Laravel on Render with Supabase (PostgreSQL)

This guide walks you through setting up a **free, non-expiring PostgreSQL database on Supabase** and connecting it to your **Laravel application hosted on Render.com**.

---

## 📌 Summary Strategy
- **Local Computer (`.env`)**: Keep `DB_CONNECTION=mysql` for XAMPP & phpMyAdmin.
- **Render (`Environment Variables`)**: Set `DB_CONNECTION=pgsql` to point to Supabase.
- **Result**: No PHP code changes required. Laravel handles database translation automatically!

---

## Step 1: Create a Free Database on Supabase

1. Go to **[supabase.com](https://supabase.com)** and log in (or sign up with GitHub).
2. Click **+ New Project**.
3. Fill in the details:
   - **Name**: `nstp-db` (or your project name)
   - **Database Password**: *(Create a strong password and save it somewhere!)*
   - **Region**: Choose the region closest to you (e.g., *Southeast Asia / Singapore*).
4. Click **Create new project** and wait ~2 minutes for initialization.

---

## Step 2: Get your Database Credentials from Supabase

1. In your Supabase dashboard, click **Project Settings** (gear icon at the bottom left).
2. Go to **Database** -> scroll down to **Connection String**.
3. Select the **Transaction Pooler** (or **URI**):
   - **Host**: e.g., `aws-0-ap-southeast-1.pooler.supabase.com`
   - **Port**: `6543` (or `5432` for direct connection)
   - **Database Name**: `postgres`
   - **User**: `postgres.[YOUR-PROJECT-REF]` (e.g., `postgres.abcdefghijklm`)
   - **Password**: Your database password created in Step 1.

---

## Step 3: Configure Environment Variables in Render

1. Log into your **[Render.com Dashboard](https://dashboard.render.com)**.
2. Select your Laravel Web Service.
3. Click **Environment** on the left menu.
4. Add or update the following key-value pairs:

| Key | Recommended Value | Notes |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Enables production mode |
| `APP_DEBUG` | `false` | Set to `true` temporarily if debugging errors |
| `APP_KEY` | `base64:...` | Copy exact key from your local `.env` |
| `APP_URL` | `https://your-app-name.onrender.com` | Your Render URL |
| **`DB_CONNECTION`** | **`pgsql`** | Tells Laravel to use PostgreSQL |
| **`DB_HOST`** | `aws-0-ap-southeast-1.pooler.supabase.com` | Your Supabase Host |
| **`DB_PORT`** | `6543` | Use `6543` (Pooler) or `5432` |
| **`DB_DATABASE`** | `postgres` | Default Supabase DB name |
| **`DB_USERNAME`** | `postgres.[YOUR-PROJECT-REF]` | Your Supabase Username |
| **`DB_PASSWORD`** | `YourSupabasePassword` | Your Supabase Password |
| `QUEUE_CONNECTION` | `sync` | Recommended for Render free tier |
| `SESSION_DRIVER` | `database` | Stores sessions in Supabase |
| `FILESYSTEM_DISK` | `local` | Uploads will be stored locally |

5. Click **Save Changes**.

---

## Step 4: Configure Build & Start Commands on Render

In Render, navigate to **Settings** and ensure your commands are configured as follows:

### **Build Command:**
```bash
composer install --no-dev --optimize-autoloader && npm install && npm run build
```

### **Start Command:**
```bash
php artisan migrate --force && php artisan db:seed --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT
```

> **Note:** The `php artisan migrate --force` automatically runs your migrations on Supabase whenever Render deploys!

---

## Step 5: Verify Deployment

1. Watch the **Logs** tab in Render during deploy.
2. Look for the line: `Running migrations... DONE`.
3. Open your **Supabase Dashboard** -> **Table Editor** tab. You will see all your Laravel tables (`users`, `appointments`, `migrations`, etc.) created automatically!
4. Open your Render Web URL in the browser and test logging in/creating records.

---

## ⚡ Troubleshooting Quick Reference

- **Issue: `Connection refused` or `SQLSTATE[08006]`**
  - Verify `DB_PORT` is set to `6543` (if using Transaction Pooler) or `5432`.
  - Check that `DB_USERNAME` includes your project reference string (`postgres.abcdefg`).
- **Issue: Render Free Tier Sleep (Cold Start)**
  - Remember Render free tier sleeps after 15 minutes of no visits. Open your web app URL 2 minutes before presenting to wake it up!
- **Issue: Data Wiped on Redeploy**
  - Database data in Supabase will **NOT** be wiped. 
  - If you run `php artisan migrate:fresh` on deploy, it will wipe tables; make sure your start command uses `php artisan migrate --force` (without `:fresh`).
