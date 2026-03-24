# Bimmer Firmware Download System

A Symfony-based system for managing and downloading vehicle firmware updates.

## Features

- **API Endpoint**: `/api/software/version` (POST) to check for updates.
- **Frontend Search**: `/carplay/software-download` for users to find updates.
- **Admin Panel**: `/admin` for non-technical staff to manage firmware rules.
- **Automatic Matching**: Ported logic to match hardware and software versions precisely.

## Setup Instructions

### Requirements

- PHP 8.2+
- Composer
- SQLite (pre-configured)

### Installation

1.  **Clone/Extract the project**.
2.  **Install dependencies**:
    ```bash
    composer install
    ```
3.  **Setup Database**:
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate --no-interaction
    ```
4.  **Import Data**:
    ```bash
    php bin/console app:import-software-versions
    ```
5.  **Run Server**:
    ```bash
    symfony server:start
    ```

## Usage

### User Interface
Access `http://localhost:8000/carplay/software-download` to search for firmware.

### Administration
Access `http://localhost:8000/admin` to manage the firmware database. 
- You can add, edit, or delete firmware entries.
- Fields include name, version strings, download links, and "Latest" status.

## Technical Details

- **Framework**: Symfony 7.x
- **Database**: SQLite (stored in `var/data.db`)
- **Admin Panel**: EasyAdmin 4.x
- **API Matching Logic**: Implemented in `src/Controller/ApiController.php`.
