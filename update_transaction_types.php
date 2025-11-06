<?php
// Migration script to update transaction_type ENUM in existing databases
require_once 'app/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        die("Database connection failed\n");
    }

    echo "Connected to database successfully\n";

    // Check current ENUM values
    $stmt = $db->prepare("DESCRIBE transactions transaction_type");
    $stmt->execute();
    $result = $stmt->fetch();

    echo "Current transaction_type definition: " . $result['Type'] . "\n";

    // Update the ENUM to include missing values
    $alterQuery = "ALTER TABLE transactions MODIFY COLUMN transaction_type ENUM('send', 'receive', 'topup', 'withdraw', 'add_money', 'pay_bills') NOT NULL";

    $stmt = $db->prepare($alterQuery);
    $result = $stmt->execute();

    if ($result) {
        echo "Successfully updated transaction_type ENUM\n";

        // Verify the change
        $stmt = $db->prepare("DESCRIBE transactions transaction_type");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "Updated transaction_type definition: " . $result['Type'] . "\n";

    } else {
        echo "Failed to update transaction_type ENUM\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "Migration completed\n";
?>
