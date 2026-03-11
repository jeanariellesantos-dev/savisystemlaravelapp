# SAVI Approval System

A web-based **Approval and Request Management System** built with
**Laravel** to streamline internal request workflows, approvals,
notifications, and tracking within the SAVI organization.

---

## 📌 Overview

The **SAVI Approval System** digitizes and automates approval processes
across departments. Users can submit requests, route them through
approval hierarchies, monitor statuses, and maintain accountability
through structured workflows.

---

## 🚀 Features

- Request Submission Management
- Multi-Level Approval Workflow
- Status Tracking (Pending, Approved, Rejected)
- Role-Based Access Control
- Notification System
- Dashboard Analytics & Statistics
- Inventory & Accounting Approval Flow
- Activity Logging
- Secure Authentication

---

## 🏗️ Tech Stack

- Laravel (PHP Framework)
- PHP
- MySQL
- REST API
- Laravel Eloquent ORM
- Laravel Notifications
- Blade / React (Optional)
- Tailwind CSS

---

## 📂 Project Structure

    savi-approval-system/
    │
    ├── app/
    │   ├── Models/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   └── Middleware/
    │   └── Services/
    │
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    │
    ├── routes/
    │   ├── web.php
    │   └── api.php
    │
    ├── resources/
    │   ├── views/
    │   └── js/
    │
    ├── config/
    └── public/

---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/your-repository/savi-approval-system.git
cd savi-approval-system
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database

Update `.env`:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=savi_approval
    DB_USERNAME=root
    DB_PASSWORD=

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

### 6. Start Development Server

```bash
php artisan serve
```

Application URL:

    http://127.0.0.1:8000

---

## 🔐 User Roles

- Requester --- Creates requests
- Supervisor --- First-level approval
- Cluster Head --- Secondary approval
- Accounting --- Financial validation
- Inventory --- Processing approval
- Administrator --- System management

---

## 🔄 Approval Workflow

    Requester
       ↓
    Supervisor Approval
       ↓
    Cluster Head Approval
       ↓
    Accounting Approval
       ↓
    Inventory Processing
       ↓
    Completed

Statuses:

- PENDING_SUPERVISOR
- PENDING_CLUSTER_HEAD
- PENDING_ACCOUNTING
- PENDING_INVENTORY
- APPROVED
- REJECTED

---

## 🔔 Notifications

- In-app notifications
- Approval alerts
- Status updates
- Request activity notifications

---

## 🧪 Running Tests

```bash
php artisan test
```

---

## 🧹 Useful Artisan Commands

```bash
php artisan migrate
php artisan db:seed
php artisan cache:clear
php artisan config:clear
php artisan queue:work
```

---

## 📊 API Endpoints (Sample)

Method Endpoint Description

---

GET /api/requests List requests
POST /api/requests Create request
PUT /api/requests/{id}/approve Approve request
PUT /api/requests/{id}/reject Reject request

---

## 🔒 Security

- CSRF Protection
- Authentication Middleware
- Role-based Authorization
- Input Validation
- Secure Database Queries via Eloquent ORM

---

## 📦 Deployment

Recommended:

- Nginx / Apache
- PHP ≥ 8.1
- MySQL ≥ 5.7
- Supervisor (Queues)
- SSL Enabled

Optimize:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📜 Version History

Version Date Description

---

1.0.0 03/03/2026 Initial release of SAVI Approval System
1.1.0 03/11/2026 
    -> Change username from email address to employee number (new users table attribute)
    -> Integrate edit orders for all the approvers [ACCOUNTING, SUPERVISOR, CLUSTER HEAD]
    -> Add new status [ON_HOLD, CANCELLED] for Administrator
    -> Modify Administrator page eg. Dashboard and Manage Requests
    
---

## 🤝 Contributing

1.  Create feature branch
2.  Commit changes
3.  Push branch
4.  Open Pull Request

---

## 📄 License

Proprietary software owned by SAVI.

---

## 📞 Support

**SAVI Development Team**\
Email: support@savi-system.com
