# NewtonJEE Student & Admin Portal

> AI & Robotics learning portal — PHP 8.2 · MySQL 8 · AWS ap-south-1

## Architecture

| Layer       | Stack                                       |
|-------------|---------------------------------------------|
| Language    | PHP 8.2                                     |
| Database    | MySQL 8 (AWS RDS ap-south-1)                |
| Web server  | Nginx + PHP-FPM                             |
| Auth        | Google OAuth 2.0 (students) · bcrypt (admin/mentor) |
| Email       | PHPMailer + SendGrid SMTP                   |
| PDF         | dompdf (certificates)                       |
| Hosting     | AWS EC2 ap-south-1 (Mumbai)                 |

## Portal Portals

| URL               | Who          | Auth method          |
|-------------------|--------------|----------------------|
| `/`               | Students     | Google OAuth 2.0     |
| `/admin`          | Admins       | Email + password     |
| `/mentor`         | Mentors      | Email + password     |
| `/verify/{token}` | Anyone       | No login required    |

## Directory Structure

```
newtonjee/
├── public/              ← Web root (DocumentRoot)
│   ├── index.php        ← Front controller / router
│   ├── .htaccess        ← Apache rewrite rules (use nginx.conf for Nginx)
│   ├── css/app.css      ← Portal stylesheet (dark/light theme)
│   └── js/app.js        ← Theme toggle, polling, Drive validation
├── app/
│   ├── Database.php     ← PDO singleton with query helpers
│   ├── Router.php       ← Lightweight router
│   ├── Controllers/
│   │   ├── BaseController.php   ← Auth guards, CSRF, flash, audit
│   │   ├── AuthController.php   ← Google OAuth (students)
│   │   ├── Student/             ← Dashboard, Courses, Lessons, Assignments...
│   │   ├── Admin/               ← Users, Courses, Assignments, Analytics...
│   │   └── Mentor/              ← Dashboard, Submissions, Courses
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── app.php          ← Student portal layout
│   │   │   ├── admin.php        ← Admin panel layout
│   │   │   └── mentor.php       ← Mentor panel layout
│   │   ├── auth/login.php       ← Google sign-in page
│   │   ├── student/             ← Dashboard, courses, assignments, certs...
│   │   ├── admin/               ← All admin views
│   │   └── mentor/              ← Mentor views
│   ├── Helpers/
│   │   ├── MailHelper.php       ← PHPMailer wrapper with branded template
│   │   └── CertHelper.php       ← dompdf certificate generation
├── config/app.php       ← .env loader + constants
├── sql/
│   └── 001_initial_schema.sql   ← Complete DB schema (14 tables + views)
├── nginx.conf           ← Production Nginx server block
├── deploy.sh            ← AWS EC2 Ubuntu setup script
├── composer.json        ← Dependencies
└── .env.example         ← Environment variables template
```

## Quick Start

### 1. Clone & install dependencies
```bash
git clone <your-repo> /var/www/newtonjee
cd /var/www/newtonjee
composer install --no-dev --optimize-autoloader
```

### 2. Configure environment
```bash
cp .env.example .env
nano .env   # Fill in DB, Google OAuth, SMTP credentials
```

### 3. Create database
```bash
mysql -u root -p -e "CREATE DATABASE newtonjee CHARACTER SET utf8mb4;"
mysql -u root -p newtonjee < sql/001_initial_schema.sql
```

### 4. Create private storage directories
```bash
mkdir -p /var/www/private/notebooks/starters
mkdir -p /var/www/private/notebooks/submissions
mkdir -p /var/www/private/certificates
chown -R www-data:www-data /var/www/private
chmod -R 750 /var/www/private
```

### 5. Set permissions
```bash
chown -R www-data:www-data /var/www/newtonjee
chmod -R 755 /var/www/newtonjee
```

### 6. Configure Nginx
```bash
cp nginx.conf /etc/nginx/sites-available/newtonjee.com
ln -s /etc/nginx/sites-available/newtonjee.com /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

## Google OAuth Setup

1. Go to [console.cloud.google.com](https://console.cloud.google.com)
2. Create a new project: `NewtonJEE Portal`
3. APIs & Services → Credentials → Create OAuth 2.0 Client ID
4. Application type: Web application
5. Authorised redirect URI: `https://newtonjee.com/auth/google/callback`
6. Copy Client ID and Secret to your `.env`

## Default Admin Login

| Field    | Value                      |
|----------|----------------------------|
| URL      | https://newtonjee.com/admin |
| Email    | admin@newtonjee.com        |
| Password | `Admin@123` (**change immediately!**) |

Change password at: Admin → Settings → My Account

## Key Features

### Students
- Google OAuth 2.0 only (no username/password)
- Course catalog with enrollment
- YouTube video lessons (unlisted embeds)
- Jupyter notebook download + Open in Colab
- Submit assignments via Google Drive link (share with mentor@newtonjee.com)
- Progress tracking per lesson and course
- Exams (MCQ)
- Certificate download (dompdf PDF)
- Announcements with unread tracking
- 30-second notification polling
- Dark / light theme

### Admins
- Email + password login with rate limiting
- Full user management (create student/mentor/admin)
- Course builder (modules + lessons + YouTube embed normalisation)
- Assignment creation with notebook upload
- Grade submissions (open Drive URL, score + feedback)
- Issue / revoke certificates
- Analytics dashboard
- Announcements with optional bulk email
- Audit log for all admin actions

### Mentors
- Separate `/mentor` portal with email + password
- View only their assigned courses and students
- Grade submissions (open Drive link)
- Create course-scoped announcements
- Cannot access admin panel

## Notebook Workflow

```
Admin uploads .ipynb (stored at /var/www/private/notebooks/starters/)
    ↓
Student visits /assignments/{id}
    ↓
Student clicks "Download .ipynb" → PHP auth-gated readfile()
Student clicks "Open in Colab" → Colab URL (optional)
    ↓
Student completes notebook in Google Colab
    ↓
Student shares with mentor@newtonjee.com (Viewer access)
Student pastes Drive URL + ticks confirmation checkbox
    ↓
Mentor/Admin visits /admin/submissions/{id}
Clicks "Open Notebook ↗" → opens student's Drive
Enters score + feedback
```

## Environment Variables

| Key                  | Description                                        |
|----------------------|----------------------------------------------------|
| `DB_HOST`            | RDS endpoint                                       |
| `DB_NAME`            | Database name (`newtonjee`)                        |
| `DB_USER` / `DB_PASS`| Database credentials                               |
| `GOOGLE_CLIENT_ID`   | From Google Cloud Console                          |
| `GOOGLE_CLIENT_SECRET` | From Google Cloud Console                        |
| `GOOGLE_REDIRECT_URI`| `https://newtonjee.com/auth/google/callback`       |
| `SMTP_HOST/PORT/USER/PASS` | SendGrid (or other) SMTP for notifications   |
| `MENTOR_DRIVE_EMAIL` | `mentor@newtonjee.com` — shown in submission form  |
| `NOTEBOOK_UPLOAD_PATH` | `/var/www/private/notebooks` (outside web root)  |

## Pending Items (before Sprint 0)

- [ ] SMTP credentials (SendGrid API key)
- [ ] Starter .ipynb notebooks for first batch
- [ ] Sprint 0 start date
- [ ] Lead developer assigned

## Sprint Plan

| Sprint | Focus                            | Duration |
|--------|----------------------------------|----------|
| 0      | Server setup, env, DB            | 1 week   |
| 1      | Auth (Google OAuth) + Dashboard + Catalog | 2 weeks |
| 2      | Content delivery + progress      | 2 weeks  |
| 3      | Assignments + notebooks          | 2 weeks  |
| 4      | Exams + certificates             | 2 weeks  |
| 5      | Admin panel + analytics          | 2 weeks  |
| 6      | P2: video resume, polish         | 2 weeks  |
