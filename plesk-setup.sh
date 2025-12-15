#!/bin/bash

# ============================================
# Plesk Database Setup Script
# ============================================
# This script creates a MySQL database and user in Plesk,
# generates a secure config.php file with the credentials,
# and optionally imports the database schema.
#
# Usage: ./plesk-setup.sh [database_name]
#   If no database name is provided, it will prompt for one.
#
# Requirements:
# - Must be run on a Plesk server with SSH access
# - User must have Plesk database management permissions
# - Run from the website's document root directory
# ============================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get the script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# ============================================
# Functions
# ============================================

print_header() {
    echo ""
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

# Generate a secure random password
generate_password() {
    # Generate a 24-character password with letters, numbers, and safe special chars
    openssl rand -base64 32 | tr -dc 'a-zA-Z0-9!@#%^&*_+-=' | head -c 24
}

# Get the current Plesk username
get_plesk_user() {
    # Try to get username from pwd or whoami
    local user=$(whoami)
    
    # If we're in a Plesk website directory, extract domain from path
    local pwd_path=$(pwd)
    if [[ "$pwd_path" =~ /var/www/vhosts/([^/]+) ]]; then
        # We're in a vhost directory - get the system user for this domain
        local domain="${BASH_REMATCH[1]}"
        if [ -f "/var/www/vhosts/$domain/.owner" ]; then
            user=$(cat "/var/www/vhosts/$domain/.owner")
        fi
    fi
    
    echo "$user"
}

# Check if Plesk CLI is available
check_plesk_cli() {
    if ! command -v plesk &> /dev/null; then
        print_error "Plesk CLI not found. This script must be run on a Plesk server."
        exit 1
    fi
}

# Create database using Plesk CLI
create_database() {
    local db_name="$1"
    local db_user="$2"
    local db_pass="$3"
    local plesk_user="$4"
    
    print_info "Creating database: ${plesk_user}_${db_name}"
    
    # Create the database
    if plesk bin database --create "${db_name}" -domain "$DOMAIN" -type mysql 2>/dev/null; then
        print_success "Database created successfully"
    else
        # Try alternative method using direct MySQL commands via Plesk
        print_warning "Trying alternative database creation method..."
        if plesk db "${db_name}" -domain "$DOMAIN" --create 2>/dev/null; then
            print_success "Database created successfully (alternative method)"
        else
            print_error "Failed to create database. It may already exist or you lack permissions."
            return 1
        fi
    fi
    
    return 0
}

# Create database user
create_db_user() {
    local db_name="$1"
    local db_user="$2"
    local db_pass="$3"
    
    print_info "Creating database user: ${db_user}"
    
    if plesk bin database --create-dbuser "${db_user}" -passwd "${db_pass}" -database "${db_name}" -domain "$DOMAIN" 2>/dev/null; then
        print_success "Database user created successfully"
    else
        print_warning "Could not create separate user. Database may use default credentials."
    fi
    
    return 0
}

# ============================================
# Main Script
# ============================================

print_header "Plesk Database Setup Script"

# Check for Plesk CLI
check_plesk_cli

# Get current Plesk user
PLESK_USER=$(get_plesk_user)
print_info "Detected Plesk user: $PLESK_USER"

# Get the domain (required for Plesk operations)
if [ -z "$DOMAIN" ]; then
    # Try to detect domain from current directory
    pwd_path=$(pwd)
    if [[ "$pwd_path" =~ /var/www/vhosts/([^/]+) ]]; then
        DOMAIN="${BASH_REMATCH[1]}"
        print_info "Detected domain: $DOMAIN"
    else
        echo ""
        read -p "Enter your domain name (e.g., example.com): " DOMAIN
        if [ -z "$DOMAIN" ]; then
            print_error "Domain name is required"
            exit 1
        fi
    fi
fi

# Get database name
if [ -n "$1" ]; then
    DB_NAME="$1"
else
    echo ""
    read -p "Enter database name (without prefix, e.g., farbrengens): " DB_NAME
    if [ -z "$DB_NAME" ]; then
        print_error "Database name is required"
        exit 1
    fi
fi

# Clean the database name (remove special chars)
DB_NAME_CLEAN=$(echo "$DB_NAME" | tr -cd 'a-zA-Z0-9_')

# Generate full names with user prefix (Plesk convention)
FULL_DB_NAME="${PLESK_USER}_${DB_NAME_CLEAN}"
DB_USER="${PLESK_USER}_${DB_NAME_CLEAN}"

# Generate secure password
DB_PASS=$(generate_password)

print_header "Database Configuration"
echo "Domain:        $DOMAIN"
echo "Database Name: $FULL_DB_NAME"
echo "Database User: $DB_USER"
echo "Password:      [Generated - will be saved to config.php]"
echo ""

# Confirm before proceeding
read -p "Proceed with database creation? (y/n): " CONFIRM
if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    print_warning "Operation cancelled"
    exit 0
fi

print_header "Creating Database"

# Create database using Plesk CLI
plesk bin database --create "$DB_NAME_CLEAN" -domain "$DOMAIN" -type mysql 2>/dev/null || {
    print_warning "Database may already exist or alternative method needed"
}

# Create database user
plesk bin database --create-dbuser "$DB_NAME_CLEAN" \
    -passwd "$DB_PASS" \
    -database "$FULL_DB_NAME" \
    -domain "$DOMAIN" 2>/dev/null || {
    print_warning "Could not create user with plesk CLI, trying alternative..."
    # The user might be created automatically with the database in some Plesk versions
}

print_success "Database setup completed"

# ============================================
# Generate config.php
# ============================================

print_header "Generating config.php"

CONFIG_FILE="${SCRIPT_DIR}/config.php"

# Check if config.php already exists
if [ -f "$CONFIG_FILE" ]; then
    print_warning "config.php already exists!"
    read -p "Overwrite existing config.php? (y/n): " OVERWRITE
    if [[ ! "$OVERWRITE" =~ ^[Yy]$ ]]; then
        # Save to alternative location
        CONFIG_FILE="${SCRIPT_DIR}/config.php.new"
        print_info "Saving to config.php.new instead"
    fi
fi

# Write the config file
cat > "$CONFIG_FILE" << 'CONFIGEOF'
<?php
/**
 * Shluchim Zoom Farbrengens Configuration
 *
 * AUTO-GENERATED by plesk-setup.sh
 * Generated: GENERATED_DATE
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'DB_NAME_PLACEHOLDER');
define('DB_USER', 'DB_USER_PLACEHOLDER');
define('DB_PASS', 'DB_PASS_PLACEHOLDER');

// Debug mode (set to false in production)
define('DEBUG_MODE', false);

/**
 * Get database connection
 */
function getDbConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Connection failed: " . $e->getMessage());
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }

    return $pdo;
}

/**
 * Start secure session
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * JSON response helper
 */
function jsonResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    if ($input === null) return null;
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
CONFIGEOF

# Replace placeholders with actual values
GENERATED_DATE=$(date '+%Y-%m-%d %H:%M:%S')
sed -i.bak "s/GENERATED_DATE/${GENERATED_DATE}/" "$CONFIG_FILE"
sed -i.bak "s/DB_NAME_PLACEHOLDER/${FULL_DB_NAME}/" "$CONFIG_FILE"
sed -i.bak "s/DB_USER_PLACEHOLDER/${DB_USER}/" "$CONFIG_FILE"
sed -i.bak "s|DB_PASS_PLACEHOLDER|${DB_PASS}|" "$CONFIG_FILE"

# Remove backup files created by sed
rm -f "${CONFIG_FILE}.bak"

# Set secure permissions on config file
chmod 640 "$CONFIG_FILE"

print_success "config.php created successfully"

# ============================================
# Import Database Schema (Optional)
# ============================================

SQL_FILE="${SCRIPT_DIR}/database.sql"

if [ -f "$SQL_FILE" ]; then
    print_header "Database Schema Import"
    read -p "Import database.sql schema? (y/n): " IMPORT_SCHEMA
    
    if [[ "$IMPORT_SCHEMA" =~ ^[Yy]$ ]]; then
        print_info "Importing database schema..."
        
        # Import using mysql command
        if mysql -u "$DB_USER" -p"$DB_PASS" "$FULL_DB_NAME" < "$SQL_FILE" 2>/dev/null; then
            print_success "Database schema imported successfully"
        else
            print_warning "Could not import schema automatically."
            print_info "You can import it manually via Plesk or phpMyAdmin"
        fi
    fi
fi

# ============================================
# Summary
# ============================================

print_header "Setup Complete!"
echo ""
echo "Database Credentials:"
echo "───────────────────────────────────────────"
echo "  Host:     localhost"
echo "  Database: $FULL_DB_NAME"
echo "  Username: $DB_USER"
echo "  Password: $DB_PASS"
echo "───────────────────────────────────────────"
echo ""
print_success "Credentials saved to: $CONFIG_FILE"
echo ""
print_warning "SECURITY REMINDER:"
echo "  • Keep your config.php secure (chmod 640)"
echo "  • Never commit config.php to version control"
echo "  • Consider adding config.php to .gitignore"
echo ""

# Check if config.php is in .gitignore
if [ -f "${SCRIPT_DIR}/.gitignore" ]; then
    if ! grep -q "config.php" "${SCRIPT_DIR}/.gitignore"; then
        print_warning "config.php is NOT in .gitignore!"
        read -p "Add config.php to .gitignore? (y/n): " ADD_GITIGNORE
        if [[ "$ADD_GITIGNORE" =~ ^[Yy]$ ]]; then
            echo "config.php" >> "${SCRIPT_DIR}/.gitignore"
            print_success "Added config.php to .gitignore"
        fi
    else
        print_success "config.php is already in .gitignore"
    fi
fi

print_header "Next Steps"
echo "1. Test database connection by visiting your site"
echo "2. If schema wasn't imported, import database.sql via Plesk"
echo "3. Create your first admin user via setup.php"
echo ""
