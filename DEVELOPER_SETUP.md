# 🚀 Developer Onboarding & Project Setup Guide
**NSTP Management System — Laravel + Vite App**

This guide provides step-by-step instructions for getting the repository up and running on a developer machine.

---

## 📋 System Prerequisites

Before setting up the project, make sure the following software is installed on your system:

| Software | Required Version | Purpose / Notes |
| :--- | :--- | :--- |
| **Git** | Latest | Source control & repository cloning |
| **PHP** | `^8.2` or `8.3+` | Laravel core runtime (Extensions needed: `pdo_mysql` / `pdo_pgsql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `zip`, `curl`) |
| **Composer** | `2.x+` | Dependency manager for PHP |
| **Node.js** | `18.x` or `20.x+` | JavaScript runtime & Vite bundling |
| **npm** | `9.x+` or `10.x+` | Package manager for frontend libraries |
| **Database** | MySQL / MariaDB (XAMPP/Herd) or PostgreSQL | Relational database (Default: MySQL via XAMPP/phpMyAdmin) |

---

## 🛠️ Step-by-Step Installation & Setup

### 1. Clone the Repository
Open your terminal (PowerShell, Git Bash, or Command Prompt) and run:
```bash
git clone https://github.com/zygradezeropat/nstp-laravel-mng-syst.git
cd nstp-laravel-mng-syst/nstp-lara
```
*(Make sure you are in the directory containing `composer.json` and `artisan`)*

---

### 2. Install PHP Dependencies
Run Composer to download and install backend packages (Laravel core, Dompdf, PhpSpreadsheet, etc.):
```bash
composer install
```

---

### 3. Install Node.js Frontend Dependencies
Run npm to install frontend tools (Vite, TailwindCSS, Concurrently, etc.):
```bash
npm install
```

---

### 4. Environment Configuration (`.env`)
Create a copy of `.env.example` as `.env`:

**On Windows (PowerShell / CMD):**
```powershell
copy .env.example .env
```

**On Linux / macOS / Git Bash:**
```bash
cp .env.example .env
```

Next, generate the application encryption key:
```bash
php artisan key:generate
```

Open `.env` in your editor (VS Code, Notepad, etc.) and configure your local database credentials:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nstp_db
DB_USERNAME=root
DB_PASSWORD=
```
> 💡 **Database Note**: Make sure your local MySQL server (e.g., XAMPP Apache & MySQL) is running. Create a new empty database named `nstp_db` in phpMyAdmin or via MySQL terminal:
> `CREATE DATABASE nstp_db;`

---

### 5. Run Database Migrations & Seed Default Data
Populate the database tables and default user credentials:
```bash
php artisan migrate:fresh --seed
```

---

### 6. Create Storage Symlink
Link the `storage/app/public` folder to `public/storage` so user uploads, activity designs, reports, and generated PDFs display properly in the browser:
```bash
php artisan storage:link
```

---

## 🔐 Default Test Login Credentials

All seeded test accounts use the default password: **`password`**

| Role | Email Address | Password | Portal Features / Access |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin123@dnsc.edu.ph` | `password` | User Accounts, System Settings, Audit Logs |
| **NSTP Coordinator** | `coordinator@dnsc.edu.ph` | `password` | Program Management, Sections, OCR Engine, Reports, Certificates |
| **CWTS/LTS Instructor** | `instructor@dnsc.edu.ph` | `password` | Section Roster, Class Announcements, Lesson Plans, Attendance |
| **ROTC Officer** | `rotc@dnsc.edu.ph` | `password` | ROTC Cadets, Platoons, Merit/Demerit Roster, Activity Designs |

---

## 🏃 Running the Application

### Option A: Standard Single Command (Recommended)
Run both the Laravel backend server and Vite frontend compiler together:
```bash
npm run dev
```
- App will be accessible at: **`http://127.0.0.1:8000`** (or `http://localhost:8000`)
- Vite dev server runs automatically for Hot Module Replacement (HMR).

### Option B: Two Independent Terminals
If you prefer separate processes:
- **Terminal 1 (Backend):**
  ```bash
  php artisan serve
  ```
- **Terminal 2 (Frontend Assets):**
  ```bash
  npm run dev
  ```

---

## 🔄 Git Workflow & Collaboration Guidelines

1. **Pull Latest Changes Before Starting Work**:
   ```bash
   git pull origin main
   ```
2. **Create a Feature Branch (Recommended)**:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Staging & Committing**:
   ```bash
   git add .
   git commit -m "feat: description of work done"
   ```
4. **Pushing Changes**:
   ```bash
   git push origin main
   ```

---

## 🛠️ Helpful Troubleshooting Commands

- **Clear All Application Caches**:
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  ```
- **Re-Seed Database**:
  ```bash
  php artisan migrate:fresh --seed
  ```
- **Fix Asset Permissions / Re-link Storage**:
  ```bash
  php artisan storage:unlink
  php artisan storage:link
  ```

---
*Created on September 26, 2026 for NSTP Management System Development Team.*
