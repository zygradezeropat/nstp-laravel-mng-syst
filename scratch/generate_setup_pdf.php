<?php

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NSTP Management System - Developer Setup Guide</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 11pt;
            margin: 20px;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .title {
            font-size: 22pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 6px 0;
        }
        .subtitle {
            font-size: 12pt;
            color: #475569;
            margin: 0;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            padding: 10px 14px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        h2 {
            font-size: 14pt;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        p {
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .code-box {
            background-color: #0f172a;
            color: #38bdf8;
            font-family: "Courier New", Courier, monospace;
            padding: 10px;
            border-radius: 5px;
            font-size: 9.5pt;
            margin-bottom: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .cmd-label {
            font-weight: bold;
            color: #334155;
            font-size: 10.5pt;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
        }
        .note {
            background-color: #fffbebf8;
            border-left: 4px solid #f59e0b;
            padding: 8px 12px;
            font-size: 10pt;
            color: #78350f;
            margin-bottom: 12px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">🚀 Developer Setup & Onboarding Guide</div>
        <div class="subtitle">NSTP Management System — Laravel 11 / 13 + Vite + Tailwind CSS</div>
    </div>

    <div class="meta-box">
        <strong>Repository:</strong> <code>https://github.com/zygradezeropat/nstp-laravel-mng-syst.git</code><br>
        <strong>Subfolder:</strong> <code>nstp-lara</code><br>
        <strong>Target Stack:</strong> PHP 8.2+, Composer 2.x, Node.js 18+, MySQL/MariaDB (XAMPP/phpMyAdmin)
    </div>

    <h2>1. 📋 System Prerequisites</h2>
    <table>
        <thead>
            <tr>
                <th>Software</th>
                <th>Required Version</th>
                <th>Description / Purpose</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Git</strong></td>
                <td>Latest</td>
                <td>Version control to clone and push updates</td>
            </tr>
            <tr>
                <td><strong>PHP</strong></td>
                <td>^8.2 / 8.3+</td>
                <td>Laravel backend runtime (ext: pdo, mbstring, openssl, gd, fileinfo)</td>
            </tr>
            <tr>
                <td><strong>Composer</strong></td>
                <td>2.x+</td>
                <td>PHP Dependency package manager</td>
            </tr>
            <tr>
                <td><strong>Node.js & npm</strong></td>
                <td>v18+ / v20+</td>
                <td>JavaScript runtime & asset bundler (Vite + Tailwind)</td>
            </tr>
            <tr>
                <td><strong>Database</strong></td>
                <td>MySQL / MariaDB</td>
                <td>Local relational database via XAMPP, Herd, or DBngin</td>
            </tr>
        </tbody>
    </table>

    <h2>2. 🛠️ Step-by-Step Installation Guide</h2>

    <div class="cmd-label">Step 1: Clone Repository & Navigate to App Directory</div>
    <div class="code-box">git clone https://github.com/zygradezeropat/nstp-laravel-mng-syst.git
cd nstp-laravel-mng-syst/nstp-lara</div>

    <div class="cmd-label">Step 2: Install PHP Backend Dependencies</div>
    <div class="code-box">composer install</div>

    <div class="cmd-label">Step 3: Install Frontend NPM Packages</div>
    <div class="code-box">npm install</div>

    <div class="cmd-label">Step 4: Configure Environment File (.env)</div>
    <p>Copy <code>.env.example</code> to <code>.env</code> and generate the app key:</p>
    <div class="code-box">copy .env.example .env
php artisan key:generate</div>

    <p>Verify your <code>.env</code> database settings match your local MySQL configuration:</p>
    <div class="code-box">DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nstp_db
DB_USERNAME=root
DB_PASSWORD=</div>

    <div class="note">
        💡 <strong>Database Setup:</strong> Open phpMyAdmin (or your SQL tool) and create an empty database named <code>nstp_db</code> before running migrations.
    </div>

    <div class="cmd-label">Step 5: Run Database Migrations & Seed Default Data</div>
    <div class="code-box">php artisan migrate:fresh --seed</div>

    <div class="cmd-label">Step 6: Link Public Storage for Uploads</div>
    <div class="code-box">php artisan storage:link</div>

    <h2>3. 🔐 Default Login Accounts</h2>
    <p>All seeded test accounts use the default password: <strong><code>password</code></strong></p>
    <table>
        <thead>
            <tr>
                <th>Role</th>
                <th>Email Address</th>
                <th>Password</th>
                <th>Access Scope</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Administrator</strong></td>
                <td><code>admin123@dnsc.edu.ph</code></td>
                <td><code>password</code></td>
                <td>User Accounts, System Logs, Settings</td>
            </tr>
            <tr>
                <td><strong>Coordinator</strong></td>
                <td><code>coordinator@dnsc.edu.ph</code></td>
                <td><code>password</code></td>
                <td>Sections, OCR Engine, Reports, Templates</td>
            </tr>
            <tr>
                <td><strong>Instructor</strong></td>
                <td><code>instructor@dnsc.edu.ph</code></td>
                <td><code>password</code></td>
                <td>Section Rosters, Lesson Plans, Attendance</td>
            </tr>
            <tr>
                <td><strong>ROTC Officer</strong></td>
                <td><code>rotc@dnsc.edu.ph</code></td>
                <td><code>password</code></td>
                <td>Cadets, Platoons, Merits/Demerits, Designs</td>
            </tr>
        </tbody>
    </table>

    <h2>4. 🏃 How to Run the App Locally</h2>

    <div class="cmd-label">Option A: Single Command Concurrent Runner (Recommended)</div>
    <div class="code-box">npm run dev</div>
    <p>Access the web application in browser at: <strong><a href="http://127.0.0.1:8000">http://127.0.0.1:8000</a></strong></p>

    <div class="cmd-label">Option B: Two Separate Terminals</div>
    <div class="code-box"># Terminal 1 - Backend Server
php artisan serve

# Terminal 2 - Asset Bundler
npm run dev</div>

    <h2>5. 🔄 Git Collaboration Workflow</h2>
    <div class="code-box"># Always pull latest code before editing
git pull origin main

# Make changes, then stage and commit
git add .
git commit -m "feat: description of work done"

# Push to repository
git push origin main</div>

    <div class="footer">
        Generated automatically for NSTP Management System Team &bull; September 26, 2026
    </div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outputPath = __DIR__ . '/../DEVELOPER_SETUP_GUIDE.pdf';
file_put_contents($outputPath, $dompdf->output());

echo "PDF successfully generated at: " . realpath($outputPath) . "\n";
