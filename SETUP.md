# RestoCRM Setup Guide

## Step 1 — Import Database
```sql
CREATE DATABASE restoCRM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Then import `database_schema.sql`

## Step 2 — Configure .env
Copy `.env.example` to `.env` and set:
```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.DBDriver = PDO
database.default.DSN = mysql:host=localhost;dbname=restoCRM;charset=utf8mb4
database.default.hostname = localhost
database.default.username = root
database.default.password = YOUR_PASSWORD
database.default.database = restoCRM
```

## Step 3 — Seed Demo Data
```bash
php spark db:seed AdminSeeder   # Creates super admin
php spark db:seed DemoSeeder    # Creates demo restaurant + full data
```

## Step 4 — Run
```bash
php spark serve
```
Open: http://localhost:8080

## Login Accounts (password: admin@123)

| Role | Email |
|------|-------|
| Super Admin | superadmin@restoCRM.com |
| Restaurant Admin | owner@spicegarden.com |
| Branch Manager | manager@spicegarden.com |
| Cashier | cashier@spicegarden.com |
| Waiter | waiter@spicegarden.com |
| Kitchen | kitchen@spicegarden.com |
