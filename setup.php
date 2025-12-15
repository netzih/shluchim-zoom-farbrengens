<?php
/**
 * Shluchim Zoom Farbrengens - Setup Script
 * Run this once to initialize the database tables and default admin user
 */

require_once 'config.php';

try {
    $pdo = getDbConnection();

    // First, ensure all tables exist
    echo "Checking database tables...\n";

    // Create events table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            farbrenger VARCHAR(255) DEFAULT NULL,
            occasion VARCHAR(100) DEFAULT NULL,
            event_date VARCHAR(255) DEFAULT NULL,
            event_timezone VARCHAR(50) DEFAULT 'America/New_York',
            zoom_link VARCHAR(500) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT DEFAULT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            INDEX idx_occasion (occasion),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Add event_timezone column if it doesn't exist (for existing installations)
    try {
        $pdo->exec("ALTER TABLE events ADD COLUMN event_timezone VARCHAR(50) DEFAULT 'America/New_York' AFTER event_date");
        echo "✓ events table updated with timezone column\n";
    } catch (PDOException $e) {
        // Column likely already exists, ignore
    }
    echo "✓ events table ready\n";

    // Create admin_users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) DEFAULT NULL,
            organization VARCHAR(255) DEFAULT NULL,
            role ENUM('super_admin', 'admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT DEFAULT NULL,
            last_login TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ admin_users table ready\n";

    // Create site_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT DEFAULT NULL,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ site_settings table ready\n";

    // Insert default settings if they don't exist
    $pdo->exec("
        INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
            ('primary_color', '#6B2C3E'),
            ('secondary_color', '#E67E22'),
            ('site_title', 'Shluchim Zoom Farbrengens'),
            ('header_line1', 'Shluchim Zoom'),
            ('header_line2', 'Farbrengens'),
            ('header_description', 'Join live Farbrengens with Shluchim from around the world')
    ");
    echo "✓ default settings ready\n\n";

    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->fetch()) {
        echo "Admin user already exists. Setup complete.\n";
        exit;
    }

    // Create default admin user
    $password_hash = password_hash('farbrengen2024', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO admin_users (username, email, password_hash, full_name, organization, role, is_active)
        VALUES ('admin', 'admin@shluchimfarbrengens.com', ?, 'Super Admin', 'Shluchim Zoom Farbrengens', 'super_admin', TRUE)
    ");

    $stmt->execute([$password_hash]);

    echo "Setup complete! Default admin user created.\n";
    echo "Username: admin\n";
    echo "Password: farbrengen2024\n";
    echo "\nPlease change the password after logging in.\n";

} catch (PDOException $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
