<?php
/**
 * Shluchim Zoom Farbrengens - Setup Script
 * Run this once to initialize the default admin user
 */

require_once 'config.php';

try {
    $pdo = getDbConnection();

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
