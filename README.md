# Salon & Spa Management System

A production-ready, enterprise-grade Salon and Spa Management Web Application built with **Laravel 13** and optimized for **PostgreSQL 18**. Designed with an elegant, responsive luxury interface using **Bootstrap 5.3**, **Bootstrap Icons**, and **Chart.js** — downloaded and bundled locally for 100% offline and air-gapped internal network operation.

---

## Table of Contents
1. [Core Features & Capabilities](#core-features--capabilities)
2. [Technology Stack & Offline Design](#technology-stack--offline-design)
3. [System Architecture & Modules](#system-architecture--modules)
4. [Prerequisites & System Requirements](#prerequisites--system-requirements)
5. [Installation & Setup Guide (Laragon / PostgreSQL 18)](#installation--setup-guide-laragon--postgresql-18)
6. [Pre-Seeded Demo Accounts & Role Switcher](#pre-seeded-demo-accounts--role-switcher)
7. [Admin Bulk Data Ingestion Hub](#admin-bulk-data-ingestion-hub)
8. [Database Schema Overview](#database-schema-overview)
9. [Thermal Receipt & Invoice Printing](#thermal-receipt--invoice-printing)
10. [Internal Network Deployment Notes](#internal-network-deployment-notes)

---

## Core Features & Capabilities

- **Front Desk Point of Sale (POS)**: Real-time checkout for appointments and walk-in clients with instant line-item additions, split payment modes, custom discounts, automated 10% tax, tip calculation, therapist commission tracking, and printable 80mm thermal receipts.
- **Appointments & Smart Scheduling**: Interactive calendar and agenda view with date filters, status tracking (`pending`, `confirmed`, `in_service`, `completed`, `cancelled`, `no_show`), automated duration and buffer-time calculations, and 1-click POS checkout transition.
- **Customer CRM with Diagnostics**: Comprehensive client 360° profiles with skin type (Dry, Sensitive, Oily, Combination), hair texture, medical allergy alerts, visit history, lifetime spend metrics, and automated loyalty reward points (1 point per $10 spent).
- **Service & Treatment Menu**: Categorized catalog (Hair, Facials, Massages, Nails, Signature Spa Packages) with buffer intervals, consumables cost tracking, and treatment room/suite assignment.
- **Inventory & Consumables Control**: Retail merchandise and in-salon backbar consumables tracking, low-stock threshold triggers, stock adjustment logs (`restock`, `usage`, `damage`, `adjustment`), and restocking modals.
- **Staff & Stylist Rostering**: Role-Based Access Control (Admin, Manager, Receptionist, Therapist), weekly shift hours (Monday–Saturday), and customizable commission percentages.
- **Commission Ledger & Settlements**: Itemized therapist earnings per invoice item, pending commission ledgers, and 1-click bulk payroll settlement.
- **Admin Bulk Data Ingestion Hub**: Native atomic CSV import engine for Customers, Services, Products, and Staff with transaction rollback, row-by-row error auditing, deduplication, and downloadable sample templates.
- **Offline / Air-Gapped Ready**: All CSS, JavaScript, font files (`.woff`, `.woff2`), and icons are hosted locally in `public/vendor/`. No external CDN or internet connection is required.

---

## Technology Stack & Offline Design

| Component | Specification | Description |
|---|---|---|
| **Backend Framework** | Laravel 13.x (Latest) | Modern streamlined kernel, PSR-4 autoloading, Eloquent ORM |
| **PHP Runtime** | PHP 8.3+ / PHP 8.4 (Laragon) | Typed properties, attributes, match expressions |
| **Database** | PostgreSQL 18 (Target Production) | Schema configured for `pgsql`, JSONB audit logging, indexed foreign keys |
| **UI Framework** | Bootstrap 5.3.3 (Local) | Responsive grid, interactive modals, dropdowns, luxury custom styling |
| **Iconography** | Bootstrap Icons 1.11.3 (Local) | Self-hosted `.woff2` and `.woff` font files in `public/vendor/bootstrap-icons/` |
| **Data Analytics** | Chart.js 4.4+ (Local) | 7-day revenue trend analytics bundled in `public/vendor/chartjs/` |
| **Theme Design** | `public/css/luxury-theme.css` | Custom warm champagne, gold, onyx slate, and soft badge luxury palette |

---

## System Architecture & Modules

### 1. Operations & Financial Dashboard
- **KPI Cards**: Today's Gross Revenue, Month-to-date Revenue, Today's Booking Count (with In-Service & Completed breakdown), Total Active Clients, and Low-Stock Alert Count.
- **Revenue Trajectory Chart**: 7-day real-time revenue line chart rendered via local Chart.js.
- **Live Agenda Timeline**: Today's scheduled appointments with client names, services, therapist assignments, status pills, and 1-click POS checkout buttons.
- **Top Specialist Leaderboard**: Ranking therapists by completed treatments and earned commissions.
- **Low Stock Watchlist**: Products currently below reorder thresholds.

### 2. Front Desk POS & Billing
- **Interactive Terminal**: Fast service and product selection tabs, search filter, and instant cart updates.
- **Staff Attribution**: Assign specific therapists to line items so commissions are calculated automatically.
- **Discounts & Taxes**: Real-time recalculation of discounts ($), sales tax (%), tips ($), and change due.
- **Flexible Payments**: Cash, Credit Card, Debit Card, Bank Transfer / QRIS, Loyalty Points, and Split payments.
- **Receipts**: Dual formats: standard full-page A4 invoice and 80mm thermal receipt formatted via `@media print`.

### 3. Customer CRM & Medical Alerts
- Profile tracking with unique customer code (`CUST-XXXXXX`).
- Skin type diagnostic (Sensitive, Dry, Oily, Combination, Normal).
- Hair texture (Straight, Wavy, Curly, Coily, Color-Treated).
- Highlighted **Allergy Alert Banner** (e.g. nut oils, latex, eucalyptus contraindications) displayed on bookings and invoices.
- Lifetime spend history, total visits completed, and loyalty balance.

### 4. Admin Bulk Data Import Hub
- Dedicated administrative control center (`/admin/import`) restricted to `admin` role.
- Support for **Customers**, **Services**, **Products/Inventory**, and **Staff/Therapists**.
- One-click **Download Sample Template (.csv)** for each entity type.
- Atomic execution: if any system failure occurs, changes rollback to prevent database corruption.
- Persistent audit logs recording file name, admin ID, processed count, success count, failure count, and downloadable row-by-row error logs.

---

## Prerequisites & System Requirements

- **PHP 8.3** or **PHP 8.4** (Installed in Laragon at `C:\laragon\bin\php\php-8.3.x...`)
- **PostgreSQL 18** (or PostgreSQL 15/16/17/18 via Laragon or standard PostgreSQL service)
- **Composer 2.x**
- Enabled PHP Extensions:
  - `pdo_pgsql`
  - `pgsql`
  - `mbstring`
  - `openssl`
  - `fileinfo`
  - `gd`
  - `intl`
  - `zip`
  - `curl`

---

## Installation & Setup Guide (Laragon / PostgreSQL 18)

### Step 1: Open Terminal in Project Directory
Navigate to the project root:
```powershell
cd c:\xampp\htdocs\salon-and-spa-management-system
```

### Step 2: Configure Environment for PostgreSQL 18
The `.env` file is already configured for PostgreSQL 18:
```env
APP_NAME="Salon & Spa Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=salon_spa_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
DB_SCHEMA=public
DB_SSLMODE=prefer

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Step 3: Create PostgreSQL Database
In your PostgreSQL 18 client (e.g. `psql`, pgAdmin 4, or Laragon Database tool):
```sql
CREATE DATABASE salon_spa_db;
```

### Step 4: Run Migrations and Seed Sample Data
Execute the migrations and seeders using Laragon's PHP 8.3/8.4:
```powershell
& "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe" artisan migrate --seed
```
*Note: If `php` in your system `PATH` is already PHP 8.3+, you can simply run:*
```bash
php artisan migrate --seed
```

### Step 5: Start the Development Server
Launch the application:
```powershell
& "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe" artisan serve
```
The application will be accessible at:
```
http://127.0.0.1:8000
```
*(Or via Laragon's automatic virtual host if placed in `C:\laragon\www\salon-and-spa-management-system`)*

---

## Pre-Seeded Demo Accounts & Role Switcher

The database seeder automatically creates pre-configured accounts representing each operational role in the salon. Default password for all demo accounts is: `password`

| Name | Role | Email | Specialization | Default Comm. |
|---|---|---|---|---|
| **Victoria Sterling** | `admin` | `admin@salonspa.local` | Executive Spa Director | 0% |
| **Alexander Hayes** | `manager` | `manager@salonspa.local` | Operations & Guest Relations | 5% |
| **Grace Montgomery** | `receptionist` | `reception@salonspa.local` | Front Desk Concierge & POS | 0% |
| **Elena Vance** | `therapist` | `elena.hair@salonspa.local` | Master Hair Stylist & Balayage | 20% |
| **Chloe Bennett** | `therapist` | `chloe.facial@salonspa.local` | Aesthetician & Facialist | 18% |
| **Marcus Reed** | `therapist` | `marcus.body@salonspa.local` | Deep Tissue & Hot Stone Massage | 20% |
| **Sophia Tanaka** | `therapist` | `sophia.nails@salonspa.local` | Nail Artist & Lash Designer | 15% |

### 1-Click Demo Quick-Switcher
You do not need to repeatedly log out and type credentials during testing:
1. **On Login Page**: Click any of the 4 quick role badges (Admin, Manager, Front Desk, Stylist).
2. **Inside Application**: Open the **"Switch Role (Demo)"** dropdown in the top navigation bar to toggle roles on the fly.

---

## Admin Bulk Data Ingestion Hub

Located at **Administration &rarr; Bulk Data Import** (`/admin/import`).

### 1. Customer Import Specifications (`customers`)
- **Download Template Route**: `GET /admin/import/sample/customers`
- **CSV Columns**:
  ```csv
  name,phone,email,gender,date_of_birth,address,skin_type,hair_type,allergies,loyalty_points,notes,status
  ```
- **Sample Row**:
  ```csv
  "Camilla Thorne","+1 (555) 789-0123","camilla.thorne@example.com","female","1992-05-14","402 Oak Ridge Ave, CA","Sensitive","Wavy","Aloe Vera sensitivity",150,"Prefers unscented lotion","active"
  ```

### 2. Services Import Specifications (`services`)
- **Download Template Route**: `GET /admin/import/sample/services`
- **CSV Columns**:
  ```csv
  code,name,category,duration_minutes,buffer_time_minutes,price,cost_price,requires_room,description
  ```
- **Sample Row**:
  ```csv
  "SRV-MASS-05","Hot Herbal Compress Massage","Massage & Body Rituals",75,15,145.00,20.00,true,"Warm botanical poultices steamed with lemongrass and ginger."
  ```

### 3. Products & Inventory Import Specifications (`products`)
- **Download Template Route**: `GET /admin/import/sample/products`
- **CSV Columns**:
  ```csv
  sku,barcode,name,category,brand,type,cost_price,retail_price,stock_quantity,low_stock_threshold,unit,description
  ```
- **Sample Row**:
  ```csv
  "PRD-SK-009","8934521009","Botanical Peptide Eye Cream 15ml","Skincare","Lumière Derma","both",16.50,55.00,25,5,"tube","Targets fine lines and dark circles."
  ```

### 4. Staff & Specialists Import Specifications (`staff`)
- **Download Template Route**: `GET /admin/import/sample/staff`
- **CSV Columns**:
  ```csv
  name,email,role,phone,specialization,commission_rate,status,password,bio
  ```
- **Sample Row**:
  ```csv
  "Natasha Romanov","natasha.r@salonspa.local","therapist","+1 (555) 912-3456","Holistic Facialist & Gua Sha",18.00,"active","password","10 years of clinical experience in advanced European skincare."
  ```

---

## Database Schema Overview

```
                      ┌──────────────────────┐
                      │        users         │
                      │ (Admin/Staff/Therap) │
                      └──────────┬───────────┘
                                 │ 1:N
       ┌─────────────────────────┼────────────────────────┐
       │ 1:N                     │ 1:N                    │ 1:N
┌──────▼─────────────┐   ┌───────▼───────────┐   ┌────────▼──────────┐
│  staff_schedules   │   │   appointments    │   │commission_payouts │
└────────────────────┘   └───────┬───────────┘   └────────▲──────────┘
                                 │ 1:N                    │
                                 │                        │ 1:1
┌────────────────────┐   ┌───────▼───────────┐   ┌────────┴──────────┐
│     customers      ├───► appointment_items │   │   invoice_items   │
└─────────┬──────────┘   └───────▲───────────┘   └────────▲──────────┘
          │                      │                        │
          │ 1:N                  │ N:1                    │ 1:N
          │              ┌───────┴───────────┐   ┌────────┴──────────┐
          └──────────────►     invoices      ├───┤     products      │
                         └───────────────────┘   └───────────────────┘
```

---

## Thermal Receipt & Invoice Printing

Receipts can be printed directly from the **Invoices** view or upon completing POS checkout:
- **80mm Thermal Receipt**: Formatted automatically when sent to thermal receipt printers.
- **A4 / Letter Standard Invoice**: Detailed line items, tax breakdown, therapist attribution, and salon header.
- **Browser Print**: Simply click the **"Print Receipt"** button or press `Ctrl + P`.

---

## Internal Network Deployment Notes

This application is strictly configured to execute without third-party internet dependencies:
- **Bootstrap 5.3.3**: Stored in `public/vendor/bootstrap/css/bootstrap.min.css` and `public/vendor/bootstrap/js/bootstrap.bundle.min.js`.
- **Bootstrap Icons 1.11.3**: Stored in `public/vendor/bootstrap-icons/` with localized WOFF/WOFF2 font binaries.
- **Chart.js 4.4+**: Stored in `public/vendor/chartjs/chart.umd.min.js`.
- **Zero External CDNs**: All Blade layouts reference local assets via `{{ asset(...) }}`.
- **Internal Network Ready**: Ideal for closed enterprise salon networks, secure hospital/resort intranet environments, or isolated POS LANs.
