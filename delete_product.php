<?php
include 'includes/db.inc.php'; 

if (isset($_POST['id'])) {
    $productId = $_POST['id'];

        $query = "DELETE prop
    FROM property AS prop
    JOIN product_property AS pp ON prop.id = pp.property_id
    JOIN products AS p ON pp.product_id = p.id
    WHERE p.id = :product_id AND prop.type_ = 'gallery';";
    $relatedStmt = $pdo->prepare($query);
    $relatedStmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $relatedStmt->execute();
    

    try {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        
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
