# RBLMHS Digi Archive

Digital Research Portal and Repository for Rafael B. Lacson Memorial High School.

**Stack:** Laravel 12, Vue 3 (SPA), MySQL, Laravel Sanctum.

## Setup

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Install Node dependencies

```bash
npm install
```

### 3. Environment

Copy `.env.example` to `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rblmhs_digiarchive
DB_USERNAME=root
DB_PASSWORD=
```

Generate app key:

```bash
php artisan key:generate
```

### 4. Database

```bash
php artisan migrate
php artisan db:seed
```

This seeds a default **admin** account:

- **Username:** `admin`
- **Password:** `password`

### 5. Storage link (for research PDFs)

```bash
php artisan storage:link
```

### 6. Run the application

**Option A – single server (Vite build):**

```bash
npm run build
php artisan serve
```

Then open http://localhost:8000

**Option B – dev with hot reload:**

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Open http://localhost:8000 (ensure `APP_URL` in `.env` is correct).

## Roles & Permissions

| Role    | Capabilities |
|---------|--------------|
| **Admin**  | Approve/reject users and research, manage users, grant/revoke download permission, view download logs, dashboard stats |
| **Student** | View approved research; download only if admin granted permission (request first) |
| **Faculty** | Upload research (pending until admin approves), download approved research without permission, view own submissions |

## API (REST)

- **Auth:** `POST /api/login`, `POST /api/register`, `POST /api/logout`, `GET /api/user`
- **Research:** `GET /api/research`, `GET /api/research/{id}`, `GET /api/research/{id}/download`, `POST /api/research` (faculty), `GET /api/my-submissions` (faculty)
- **Download request (student):** `POST /api/research/{id}/request-download`
- **Admin:** `GET /api/admin/dashboard`, `GET|PUT|DELETE /api/admin/users/*`, `GET /api/admin/research-pending`, `POST /api/admin/research/{id}/approve|reject`, `GET /api/admin/download-requests`, `POST /api/admin/download-permissions/{id}/approve|reject`, `GET /api/admin/download-logs`, `GET /api/admin/download-logs/stats`

All authenticated endpoints use `Authorization: Bearer {token}` (Sanctum).

## Default Admin

After seeding:

- **Username:** admin  
- **Password:** password  

Change the password after first login in production.
