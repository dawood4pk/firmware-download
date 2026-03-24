# BimmerTech Firmware Download — Symfony App

A Symfony 7 reimplementation of the CarPlay / Android Auto MMI firmware download page at
[bimmer-tech.net/carplay/software-download](https://www.bimmer-tech.net/carplay/software-download),
with an admin panel that lets non-technical staff manage software versions without touching code.

---

## System Requirements

| Requirement | Version |
|---|---|
| PHP | 8.2 or higher |
| Composer | 2.x |
| SQLite | 3.x (default, zero-config) **or** MySQL 8.0+ / MariaDB 10.6+ |

PHP extensions required: `pdo_sqlite` (default) or `pdo_mysql`, `intl`, `mbstring`, `xml`, `ctype`, `iconv`.

On Debian/Ubuntu these can be installed with:

```bash
sudo apt install php8.2-cli php8.2-sqlite3 php8.2-intl php8.2-mbstring php8.2-xml
```

---

## Installation

### 1. Install PHP dependencies

```bash
cd firmware-download
composer install
```

### 2. Configure the application secret

Open `.env` and replace the placeholder value:

```dotenv
APP_SECRET=replace_this_with_a_random_32_character_string
```

You can generate a suitable value with:

```bash
php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"
```

### 3. Choose your database

**Option A — SQLite (recommended for local use, no setup required)**

The default `.env` already points to SQLite. No further configuration is needed.

**Option B — MySQL / MariaDB**

Open `.env`, comment out the SQLite line, and uncomment the MySQL line:

```dotenv
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/firmware_download?serverVersion=8.0.32&charset=utf8mb4"
```

Replace `db_user`, `db_password`, and `firmware_download` with your actual credentials. Create the database first:

```sql
CREATE DATABASE firmware_download CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run database migrations

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

This creates the `software_version` and `admin_user` tables.

### 5. Load the firmware version data

```bash
# SQLite:
sqlite3 var/data.db < migrations/seed.sql

# MySQL:
mysql -u db_user -p firmware_download < migrations/seed.sql
```

This imports all 116 existing firmware versions from the original JSON data file.

### 6. Create an admin user

```bash
php bin/console app:create-admin admin your-secure-password
```

Change `admin` and `your-secure-password` to whatever credentials you prefer. You can run this command multiple times to add more users.

### 7. Start the web server

```bash
php -S 0.0.0.0:8080 -t public/
```

The application is now running at **http://localhost:8080**.

---

## URLs

| Path | Description |
|---|---|
| `http://localhost:8080/carplay/software-download` | Customer-facing firmware download page |
| `http://localhost:8080/api/carplay/software/version` | API endpoint (POST) |
| `http://localhost:8080/admin` | Admin panel (requires login) |
| `http://localhost:8080/admin/login` | Admin login page |

---

## Managing Software Versions

### Accessing the admin panel

Open **http://localhost:8080/admin** in your browser and sign in with the credentials you created in step 6.

### Adding a new firmware version

1. Click **Software Versions** in the left menu.
2. Click the **+ Add Software Version** button (top right).
3. Fill in the fields:

| Field | What to enter |
|---|---|
| **Product Name** | Select from the dropdown. Names starting with **LCI** are for LCI-generation hardware. |
| **System Version** | The full version string *with* the leading `v`, e.g. `v3.3.8.mmipri.c` |
| **System Version (customer input)** | The same string *without* the `v`, e.g. `3.3.8.mmipri.c`. This is what customers type. |
| **Generic Download Link** | The standard download folder link (for non-LCI entries). |
| **ST Download Link (CIC)** | Download link for CIC hardware. Leave blank if not applicable. |
| **GD Download Link (NBT / EVO)** | Download link for NBT/EVO hardware. Leave blank if not applicable. |
| **Is Latest Version** | Turn ON for the newest firmware only. Customers on this version see "Your system is up to date". |

> **Important:** Only one entry per product family should have *Is Latest Version* enabled.
> When you release a new version, enable it on the new entry and disable it on the previous one.

### Editing or deleting a version

Use the action buttons (pencil icon to edit, bin icon to delete) in the software versions list.

---

## API Contract

The API endpoint replicates the original `POST /api2/carplay/software/version` behaviour exactly.

**Request** (form-encoded POST body):

| Parameter | Required | Description |
|---|---|---|
| `version` | Yes | Customer's current software version |
| `hwVersion` | Yes | Customer's hardware version string |
| `mcuVersion` | No | Ignored |

**Response** (JSON):

```json
// Version recognised, update available:
{ "versionExist": true, "msg": "The latest version of software is v3.3.7 ", "link": "...", "st": "...", "gd": "..." }

// Already on the latest version:
{ "versionExist": true, "msg": "Your system is upto date!", "link": "", "st": "", "gd": "" }

// Version not found in database:
{ "versionExist": false, "msg": "There was a problem identifying your software. Contact us for help.", "link": "", "st": "", "gd": "" }

// Validation error (missing required field or unrecognised HW version):
{ "msg": "Version is required" }
```

---

## Project Structure

```
src/
├── Command/
│   └── CreateAdminUserCommand.php     — CLI command to create admin users
├── Controller/
│   ├── Admin/
│   │   ├── DashboardController.php    — EasyAdmin dashboard entry point
│   │   └── SecurityController.php    — Admin login / logout
│   └── SoftwareDownloadController.php — Customer page (GET) + API (POST)
├── EasyAdmin/
│   └── SoftwareVersionCrudController.php — Admin CRUD with field help text
├── Entity/
│   ├── AdminUser.php
│   └── SoftwareVersion.php
├── Repository/
│   ├── AdminUserRepository.php
│   └── SoftwareVersionRepository.php
└── Service/
    ├── FirmwareResponseBuilder.php    — Builds JSON response payload
    ├── HardwareDetectionResult.php    — Value object (hardware type DTO)
    ├── HardwareVersionDetector.php    — Parses HW version string via regex
    └── SoftwareVersionMatcher.php     — Looks up the correct firmware entry
```
