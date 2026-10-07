# MartPoint Retail Releases

This repository contains release packages for MartPoint Retail.

## Latest Release: v4.0.9.2

### For Fresh Installations
Download: `releases/martpoint-4.0.9.zip` (71 MB)
- Complete application with all dependencies
- Upload to cPanel, extract, visit `/setup/install/`

### For Existing Installations (4.0.8+)
Download: `releases/martpoint-4.0.9-update.zip` (1.9 MB)
- Contains only changed files (477 files)
- Run database migrations first, then upload files

### Database Migration (for existing installs)
Download: `releases/martpoint-4.0.9-database.sql` (15 KB)
- Single file with all migrations (MySQL 5.7 safe)
- Run in phpMyAdmin before uploading application files

### Latest Release Directory
`releases/latest/` contains the unpacked latest release for direct access.
The application's auto-updater pulls from this directory.

## Installation Guide

### Fresh Install
1. Download `releases/martpoint-4.0.9.zip`
2. Upload to your cPanel hosting
3. Extract the zip
4. Visit `https://yourdomain.com/setup/install/`
5. Follow the installer (creates database.php and config.php automatically)

### Existing Install Update
1. Backup your database and files
2. Run `releases/martpoint-4.0.9-database.sql` in phpMyAdmin
3. Upload contents of `releases/martpoint-4.0.9-update.zip` over existing files
4. DO NOT overwrite `application/config/database.php` or `config.php`
5. Clear `application/cache/` directory
6. Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)

## Requirements
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx
- cPanel compatible
