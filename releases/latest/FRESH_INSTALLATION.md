# MartPoint v4.0.9 - Fresh Installation Guide

**Version:** 4.0.9  
**Release Date:** 2026-08-31  
**Status:** ✅ Ready for Fresh Installation  
**Installation Time:** 30-45 minutes

---

## 📦 What's Included in v4.0.9

### New Features
✅ **Marketing Module** - Complete marketing management system  
✅ **Enhanced POS System** - Improved payment processing  
✅ **Staff Commission Tracking** - Commission calculation system  
✅ **Database Compatibility** - MySQL 5.5+ support  
✅ **Mobile Interface** - Additional responsive layouts  

### Release Package
- **Release ZIP:** `release_upload/releases/martpoint-4.0.9.zip` (17 MB)
- **Release Manifest:** `release_build/release-manifest.json`
- **Database Migrations:** 2 new migrations included
- **Complete Package:** All files synchronized to v4.0.9

---

## 🚀 Fresh Installation (30-45 minutes)

### Step 1: Download Release Package (5 minutes)

**Option A: Using Git (Recommended)**
```bash
# Clone the repository
git clone https://github.com/avariodigitals/martpoint-retail-source.git martpoint
cd martpoint

# Checkout v4.0.9 release
git checkout v4.0.9

# Or pull latest main branch
git pull origin main
```

**Option B: Using Release ZIP**
```bash
# Download from release system
wget https://your-domain.com/releases/martpoint-4.0.9.zip
unzip martpoint-4.0.9.zip
cd martpoint
```

### Step 2: Create Database (5 minutes)

```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE martpoint_v409 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional)
CREATE USER 'martpoint'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON martpoint_v409.* TO 'martpoint'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Import Base Database (5 minutes)

```bash
# Use the base database from release package
# If available, import the seed database
mysql -u martpoint -p martpoint_v409 < release_upload/releases/latest/database/seed.sql

# Or create from scratch with migrations
# The application will create tables on first run
```

### Step 4: Configure Application (5 minutes)

**Edit: `application/config/database.php`**
```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'martpoint',
    'password' => 'secure_password',
    'database' => 'martpoint_v409',
    'dbdriver' => 'mysqli',
    'dbprefix' => 'db_',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

**Edit: `application/config/config.php`**
```php
$config['base_url'] = 'http://your-domain.com/';
$config['app_version'] = '4.0.9';
$config['app_name'] = 'MartPoint';
```

### Step 5: Run Database Migrations (5 minutes)

```bash
# Navigate to application directory
cd /path/to/martpoint

# Run migrations via CLI or web interface
# The application will automatically run pending migrations on first access

# Or manually run migrations:
mysql -u martpoint -p martpoint_v409 < release_build/migrations/4.0.8_to_4.0.9_compatibility.sql
mysql -u martpoint -p martpoint_v409 < release_build/migrations/4.0.8_to_4.0.9_holditems_staff_commission.sql
```

### Step 6: Set File Permissions (5 minutes)

```bash
# Set directory permissions
chmod -R 755 /path/to/martpoint/application/
chmod -R 755 /path/to/martpoint/release_upload/

# Set writable directories
chmod -R 777 /path/to/martpoint/application/cache/
chmod -R 777 /path/to/martpoint/application/logs/
chmod -R 777 /path/to/martpoint/uploads/

# Set web server ownership (if needed)
sudo chown -R www-data:www-data /path/to/martpoint/
```

### Step 7: Configure Web Server (5 minutes)

**Apache (.htaccess already included)**
```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

**Nginx (Create server block)**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/martpoint;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 8: Access Application (5 minutes)

```bash
# Navigate to application
http://your-domain.com/

# Default credentials (if using seed database)
# Username: admin
# Password: admin123

# First login will prompt to change password
```

---

## ✅ Post-Installation Verification

### Database Verification
```bash
# Check tables created
mysql -u martpoint -p martpoint_v409 -e "SHOW TABLES;"

# Verify migrations ran
mysql -u martpoint -p martpoint_v409 -e "DESCRIBE hold_items;" | grep commission

# Check version
mysql -u martpoint -p martpoint_v409 -e "SELECT * FROM site_settings WHERE setting_key = 'app_version';"
```

### Application Verification
- [ ] Application loads without errors
- [ ] Login page displays
- [ ] Dashboard accessible
- [ ] POS module loads
- [ ] Customer management works
- [ ] Sales module functional
- [ ] Marketing module accessible
- [ ] Mobile interface responsive
- [ ] Error logs clean

### Performance Verification
- [ ] Page load times acceptable
- [ ] Database queries responsive
- [ ] No timeout errors
- [ ] Server resources normal
- [ ] Memory usage stable

---

## 📋 System Requirements

### Minimum Requirements
- **PHP:** 7.0 or higher
- **MySQL:** 5.5 or higher
- **Web Server:** Apache 2.4+ or Nginx 1.10+
- **Disk Space:** 500 MB minimum
- **RAM:** 512 MB minimum

### Recommended Requirements
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Disk Space:** 1 GB or more
- **RAM:** 1 GB or more

### PHP Extensions Required
- mysqli
- curl
- gd
- mbstring
- openssl
- json
- zip

---

## 🗄️ Database Migrations Included

### Migration 1: Compatibility Layer
**File:** `release_build/migrations/4.0.8_to_4.0.9_compatibility.sql`

Adds MySQL 5.5+ compatibility layer with:
- ADD COLUMN IF NOT EXISTS patterns
- Database integrity checks
- Smooth upgrade path

### Migration 2: Staff Commission Tracking
**File:** `release_build/migrations/4.0.8_to_4.0.9_holditems_staff_commission.sql`

Adds commission tracking with:
- Commission fields in hold_items table
- Staff commission calculation support
- Commission data initialization

---

## 📁 Directory Structure

```
martpoint/
├── application/
│   ├── config/          # Configuration files
│   ├── controllers/     # Application controllers (including new Marketing)
│   ├── models/          # Data models
│   ├── views/           # View templates (including new marketing & pos views)
│   ├── helpers/         # Helper functions (including new marketing_helper)
│   ├── libraries/       # Custom libraries
│   ├── migrations/      # Database migrations
│   ├── cache/           # Cache directory (writable)
│   └── logs/            # Log directory (writable)
├── release_upload/
│   └── releases/
│       ├── latest/      # Latest release package
│       └── martpoint-4.0.9.zip  # Release ZIP file
├── release_build/
│   ├── migrations/      # Database migration scripts
│   └── release-manifest.json  # Release manifest
├── uploads/             # User uploads (writable)
├── theme/               # Theme assets
├── index.php            # Application entry point
└── .htaccess            # Apache rewrite rules
```

---

## 🔧 Configuration Files

### Essential Configuration
1. **`application/config/database.php`** - Database connection
2. **`application/config/config.php`** - Application settings
3. **`application/config/autoload.php`** - Library autoloading

### Optional Configuration
- **`application/config/email.php`** - Email settings
- **`application/config/upload.php`** - File upload settings
- **`application/config/constants.php`** - Application constants

---

## 🔐 Security Recommendations

### Initial Setup
1. Change default admin password immediately
2. Create new admin user with strong password
3. Disable default accounts
4. Configure HTTPS/SSL certificate
5. Set proper file permissions (755 for dirs, 644 for files)

### Ongoing Security
1. Keep PHP and MySQL updated
2. Regular database backups
3. Monitor error logs
4. Update application regularly
5. Use strong passwords
6. Enable HTTPS

---

## 📞 Support & Documentation

### Documentation Files
- **QUICK_DEPLOY.txt** - Quick deployment guide
- **LOCAL_UPDATE_PACKAGE.md** - Update package guide
- **DEPLOYMENT_GUIDE.md** - Full deployment guide
- **UPLOAD_CHECKLIST.md** - Upload checklist

### Release Information
- **Release Manifest:** `release_build/release-manifest.json`
- **Release Package:** `release_upload/releases/martpoint-4.0.9.zip`
- **Release Notes:** `.devin/RELEASE_NOTES.md`

### Support Contact
- **Email:** support@martpoint.com
- **Documentation:** See included guides
- **Issues:** Check error logs in `application/logs/`

---

## 🎯 Next Steps After Installation

1. **Configure Store Settings**
   - Set store name and details
   - Configure currency and tax
   - Set up payment methods

2. **Create Users**
   - Create staff accounts
   - Assign roles and permissions
   - Configure user access levels

3. **Setup Inventory**
   - Add product categories
   - Add products/items
   - Configure stock levels
   - Set up pricing

4. **Configure Sales**
   - Set up sales tax
   - Configure discount rules
   - Setup payment methods
   - Configure receipt templates

5. **Test All Features**
   - Test POS transactions
   - Test customer management
   - Test sales reporting
   - Test mobile interface
   - Test marketing module

---

## ✨ Success Criteria

Fresh installation is successful when:

✅ Application loads without errors  
✅ Login page displays correctly  
✅ Dashboard accessible  
✅ POS module functional  
✅ Customer management works  
✅ Sales module operational  
✅ Marketing module accessible  
✅ Mobile interface responsive  
✅ No critical errors in logs  
✅ Database integrity verified  

---

## 📝 Notes

- **Version:** 4.0.9
- **Release Date:** 2026-08-31
- **Backward Compatible:** Yes
- **Breaking Changes:** None
- **Database Migrations:** 2 included
- **New Features:** 6 major features
- **Installation Time:** 30-45 minutes

---

**Installation Guide Generated:** 2026-09-02  
**Status:** ✅ READY FOR FRESH INSTALLATION  
**Next Action:** Follow steps above for fresh installation

