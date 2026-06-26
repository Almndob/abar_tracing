# Abar Tracing — Truck Tracking & Fleet Management System

An internal fleet management and real-time truck tracking system developed exclusively for **Abar Company**. The system provides GPS-based shipment monitoring, an admin dashboard for fleet operations, and a public-facing tracking interface — all operating within the Hail region, Saudi Arabia.

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Installation & Setup](#installation--setup)
- [Usage](#usage)
- [API Reference](#api-reference)
- [Security](#security)
- [Development URLs](#development-urls)

---

## Overview

Abar Tracing is an **internal-only** system — not a public service. It is designed to track Abar Company's trucks and shipments in real time, allowing administrators to monitor the fleet, manage deliveries, and generate performance reports from a single integrated platform.

Location data is centered around **Ha'il city coordinates** (27.5250°N, 41.6900°E).

---

## Features

### Public Tracking Interface
- Track shipments by Truck Number or Shipment ID
- Real-time GPS location displayed on an interactive Leaflet.js map
- Shipment details: origin, destination, driver, status, and estimated arrival
- Location auto-refreshes every 30 seconds

### Admin Dashboard
- Secure employee login with session-based authentication
- Fleet overview: active trucks, completed deliveries, late shipments
- Weekly delivery performance chart (Chart.js)
- Truck management: add, edit, delete trucks and driver assignments
- Shipment management: track status (`Pending`, `In Transit`, `Delivered`)
- Reports and analytics page
- System settings

### Core Capabilities
- GPS-based real-time location tracking with history logging
- Automated delivery ETA display
- Route optimization and fuel consumption monitoring
- Driver behavior monitoring and compliance tracking
- Instant alerts for delays or route changes

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Server** | XAMPP (Apache 2.4) |
| **Backend** | PHP 8.2.12 |
| **Database** | MariaDB 10.4.32 (MySQL) |
| **Database API** | MySQLi (procedural, prepared statements) |
| **Mapping** | Leaflet.js + OpenStreetMap tiles |
| **Charts** | Chart.js |
| **Icons** | Font Awesome 6.0.0-beta3 |
| **Animations** | AOS (Animate On Scroll) 2.3.1 |
| **Frontend JS** | Vanilla JavaScript ES6+ (Fetch API) |
| **Fonts** | Poppins, Inter, Roboto Mono (Google Fonts) |

### Design System

| Token | Value |
|---|---|
| Primary color | `#0A1D37` (Deep navy blue) |
| Accent color | `#FF7A00` (Orange) |
| Admin theme | `#7A2A8A` (Purple dark mode) |
| Border radius | `15px` |

---

## Project Structure

```
abar_tracing/
├── index.php               # Public landing page
├── tracking.php            # Public shipment tracking page
├── about.php               # About page
├── contact.php             # Contact page
├── abar_tracing.sql        # Database schema & seed data
│
├── admin/                  # Admin panel (protected)
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── trucks.php
│   ├── shipments.php
│   ├── reports.php
│   ├── settings.php
│   └── .htaccess
│
├── api/                    # REST API (protected)
│   ├── track.php           # GET /api/track.php?id={id}
│   └── .htaccess
│
├── includes/               # Shared PHP components (protected)
│   ├── db.php              # Database connection
│   ├── header.php
│   ├── footer.php
│   └── .htaccess
│
└── assets/                 # Static assets (protected)
    ├── css/style.css
    ├── js/
    │   ├── script.js
    │   └── tracking.js
    └── images/
```

---

## Database Schema

Database name: **`abar_tracing`**  
Character set: `utf8mb4` / `utf8mb4_general_ci`

### `users` — Admin authentication
| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key |
| username | VARCHAR | Unique |
| password | VARCHAR | bcrypt hashed |
| email | VARCHAR | |

### `trucks` — Fleet vehicles
| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key |
| truck_number | VARCHAR | Unique identifier |
| driver_name | VARCHAR | |
| status | ENUM | `Active`, `On Route`, `Loading`, `Stopped` |

### `shipments` — Delivery records
| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key |
| shipment_id | VARCHAR | Public tracking ID |
| truck_id | INT | FK → trucks(id) |
| origin | VARCHAR | |
| destination | VARCHAR | |
| estimated_arrival | DATETIME | |
| status | ENUM | `Pending`, `In Transit`, `Delivered` |
| departure_date | DATETIME | |
| arrival_date | DATETIME | |

### `tracking_history` — GPS location logs
| Column | Type | Notes |
|---|---|---|
| id | INT | Primary key |
| truck_id | INT | FK → trucks(id) |
| latitude | DECIMAL(10,8) | |
| longitude | DECIMAL(11,8) | |
| timestamp | DATETIME | Auto-recorded |

---

## Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with PHP 8.2+ and MariaDB/MySQL

### Steps

**1. Place the project files**

Copy or clone the project into your XAMPP web root:
```
c:\xampp\htdocs\abar_tracing\
```

**2. Start XAMPP services**

Launch the XAMPP Control Panel and start both **Apache** and **MySQL**:
```cmd
c:\xampp\xampp-control.exe
```

**3. Create the database**

Open [phpMyAdmin](http://localhost/phpmyadmin), create a database named `abar_tracing`, then import the schema:

```cmd
mysql -u root abar_tracing < c:\xampp\htdocs\abar_tracing\abar_tracing.sql
```

Or via phpMyAdmin: select the `abar_tracing` database → **Import** → choose `abar_tracing.sql`.

**4. Verify database connection**

The connection is configured in `includes/db.php` using the default XAMPP credentials:
- Host: `localhost`
- User: `root`
- Password: *(empty)*
- Database: `abar_tracing`

If your XAMPP MySQL uses a different password, update `includes/db.php` accordingly.

**5. Open the application**

Navigate to: [http://localhost/abar_tracing/](http://localhost/abar_tracing/)

---

## Usage

### Public Tracking
1. Go to [http://localhost/abar_tracing/tracking.php](http://localhost/abar_tracing/tracking.php)
2. Enter a **Truck Number** or **Shipment ID** in the search field
3. View real-time location on the map along with shipment details

### Admin Panel
1. Go to [http://localhost/abar_tracing/admin/login.php](http://localhost/abar_tracing/admin/login.php)
2. Log in with your admin credentials
3. Use the sidebar to navigate:
   - **Dashboard** — fleet summary and weekly performance chart
   - **Trucks** — manage fleet vehicles and driver assignments
   - **Deliveries** — manage and update shipment statuses
   - **Reports** — view analytics and performance data
   - **Settings** — system configuration

### Viewing Error Logs
```cmd
type c:\xampp\apache\logs\error.log
```

---

## API Reference

### `GET /api/track.php`

Track a truck or shipment by ID.

**Query Parameters**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `id` | string | Yes | Truck Number or Shipment ID |

**Success Response** (`200 OK`)

```json
{
  "success": true,
  "truck_info": {
    "Truck Number": "TRK-001",
    "Driver Name": "Ahmed Al-Rashidi",
    "Current Status": "On Route",
    "Shipment ID": "SHP-20241001",
    "Origin": "Ha'il",
    "Destination": "Riyadh",
    "Estimated Arrival": "Oct 05, 2024 14:30 PM",
    "Shipment Status": "In Transit",
    "Last Updated": "09:15:42 AM"
  },
  "location": {
    "lat": 27.51234567,
    "lng": 41.67890123
  }
}
```

**Error Response**

```json
{
  "success": false,
  "message": "Truck or Shipment ID not found."
}
```

**Notes**
- The frontend polls this endpoint every **30 seconds** automatically
- Location is simulated within the Ha'il region bounding box for demo purposes

---

## Security

| Mechanism | Implementation |
|---|---|
| Authentication | PHP session-based (`$_SESSION["loggedin"]`) |
| Password storage | `password_hash()` / `password_verify()` with bcrypt |
| SQL injection prevention | MySQLi prepared statements throughout |
| Directory protection | `.htaccess` files in `/admin`, `/api`, `/includes`, `/assets` |
| XSS prevention | `htmlspecialchars()` on all output |
| Session enforcement | Redirect to login if session is not valid |

> **Note:** The default XAMPP MySQL setup uses `root` with no password. For any non-local deployment, set a strong database password and update `includes/db.php`.

---

## Development URLs

| Page | URL |
|---|---|
| Public site | http://localhost/abar_tracing/ |
| Tracking page | http://localhost/abar_tracing/tracking.php |
| Admin login | http://localhost/abar_tracing/admin/login.php |
| Admin dashboard | http://localhost/abar_tracing/admin/dashboard.php |
| Tracking API | http://localhost/abar_tracing/api/track.php |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## License

Internal use only — Abar Company. Not for public distribution.
