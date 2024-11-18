<?php
include 'includes/db.inc.php'; 

if (isset($_POST['id'])) {
    $productId = $_POST['id'];

    try {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo 'success'; 
        } else {
            echo 'error'; 
        }
    } catch (PDOException $e) {
        echo 'error: ' . $e->getMessage(); 
    }
}
?>
