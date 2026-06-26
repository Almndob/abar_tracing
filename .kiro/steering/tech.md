# Technology Stack

## Development Environment

- **Server**: XAMPP (Apache + MySQL + PHP)
- **Database**: MariaDB 10.4.32
- **PHP Version**: 8.2.12
- **Local Path**: `c:\xampp\htdocs\abar_tracing\`

## Backend Technologies

### PHP
- **Version**: 8.2.12
- **Session Management**: Native PHP sessions for authentication
- **Database Layer**: MySQLi (procedural style with prepared statements)
- **Security**: Password hashing with `password_verify()` and `password_hash()`

### Database
- **System**: MySQL/MariaDB
- **Database Name**: `abar_tracing`
- **Connection**: MySQLi with error handling
- **Credentials**: Default XAMPP setup (root user, no password)
- **Character Set**: utf8mb4 with utf8mb4_general_ci collation

## Frontend Technologies

### CSS Framework & Libraries
- **Font Families**: 
  - Poppins (body text)
  - Inter (headings)
  - Roboto Mono (monospace)
- **Icons**: Font Awesome 6.0.0-beta3
- **Animations**: AOS (Animate On Scroll) library 2.3.1
- **Charts**: Chart.js (for dashboard analytics)

### JavaScript Libraries
- **Mapping**: Leaflet.js (OpenStreetMap tiles)
- **AJAX**: Native Fetch API for async requests
- **DOM Manipulation**: Vanilla JavaScript (ES6+)

### Design System
- **Primary Color**: `#0A1D37` (Deep navy blue)
- **Accent Color**: `#FF7A00` (Orange)
- **Admin Theme**: `#7A2A8A` (Purple - dark mode)
- **Border Radius**: 15px standard
- **Shadow**: `0 10px 30px rgba(0, 0, 0, 0.1)`

## Database Schema

### Core Tables
1. **users** - Admin authentication
   - Fields: id, username, password (hashed), email
   
2. **trucks** - Fleet vehicles
   - Fields: id, truck_number, driver_name, status
   - Status values: Active, On Route, Loading, Stopped
   
3. **shipments** - Delivery records
   - Fields: id, shipment_id, truck_id, origin, destination, estimated_arrival, status, departure_date, arrival_date
   - Status values: In Transit, Delivered, Pending
   - Foreign Key: truck_id → trucks(id)
   
4. **tracking_history** - GPS location logs
   - Fields: id, truck_id, latitude (decimal 10,8), longitude (decimal 11,8), timestamp
   - Foreign Key: truck_id → trucks(id)

## API Endpoints

### Public API
- **Track Endpoint**: `/api/track.php?id={tracking_id}`
  - Method: GET
  - Response: JSON with truck_info, location (lat/lng), success status
  - Auto-refresh: Every 30 seconds on frontend

## Security Features

- **Authentication**: Session-based with server-side validation
- **SQL Injection Protection**: Prepared statements with MySQLi
- **Password Security**: PHP password_hash() with bcrypt
- **Access Control**: `.htaccess` files protecting sensitive directories
- **Session Checks**: Redirect to login if not authenticated

## Common Commands

### Starting the Development Server
```cmd
:: Start XAMPP Control Panel
c:\xampp\xampp-control.exe

:: Start Apache and MySQL services from XAMPP panel
```

### Database Management
```cmd
:: Access phpMyAdmin
http://localhost/phpmyadmin

:: Import database
mysql -u root abar_tracing < abar_tracing.sql
```

### File Structure Access
```cmd
:: Navigate to project
cd c:\xampp\htdocs\abar_tracing

:: View error logs
type c:\xampp\apache\logs\error.log
```

## Development URLs

- **Public Site**: `http://localhost/abar_tracing/`
- **Admin Login**: `http://localhost/abar_tracing/admin/login.php`
- **Admin Dashboard**: `http://localhost/abar_tracing/admin/dashboard.php`
- **Tracking Page**: `http://localhost/abar_tracing/tracking.php`
- **API**: `http://localhost/abar_tracing/api/track.php`

## Configuration Files

- **Database Config**: `/includes/db.php`
- **Access Control**: `.htaccess` files in `/admin`, `/api`, `/includes`, `/assets`
- **SQL Schema**: `/abar_tracing.sql`
