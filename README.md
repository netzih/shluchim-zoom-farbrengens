# Shluchim Zoom Farbrengens

A simple website for listing upcoming Zoom Farbrengens for Shluchim worldwide.

## Features

- **Public Events Page** - Lists upcoming Zoom Farbrengens with:
  - Title and Farbrenger name
  - Occasion tag (e.g., Yud Tes Kislev, Yud Shevat)
  - Description and Date/Time
  - "Join Zoom" button to easily access the meeting
  - Filter by Occasion

- **Admin Panel** (`/admin.php`) - Manage Farbrengens:
  - Add, edit, and delete events
  - Customize site title, header, and description
  - Manage users (Super Admin only)

- **Public Submission Form** (`/submit-event.html`) - Password-protected form for adding new Farbrengens

## Quick Start

### Option A: Automated Setup (Plesk Hosting)

If you're on Plesk hosting, run the automated setup script:

```bash
chmod +x plesk-setup.sh
./plesk-setup.sh
```

This will:
- Create the database in your Plesk account
- Generate `config.php` with secure credentials
- Optionally import the database schema

### Option B: Manual Setup

1. Create a MySQL database
2. Copy `config.php.example` to `config.php` and update database credentials
3. Import `database.sql` to set up tables

### After Setup

1. Run `setup.php` to initialize the admin user
2. Access `/admin.php` to log in (default: admin / farbrengen2024)

## Event Fields

| Field | Description |
|-------|-------------|
| Title | Name of the Farbrengen |
| Farbrenger | Person leading the Farbrengen |
| Occasion | What the Farbrengen is for (Yud Tes Kislev, etc.) |
| Description | Details about the event |
| Date & Time | When the Farbrengen takes place |
| Zoom Link | URL for participants to join |

## Tech Stack

- PHP 7.4+
- MySQL / MariaDB
- Vanilla JavaScript
- Nunito font from Google Fonts

## License

Private - for Shluchim use only.
