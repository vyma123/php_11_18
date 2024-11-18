<?php
include 'db_connection.php';

$currentPage = $_GET['page'] ?? 1;
$productsPerPage = 5; 

$offset = $currentPage * $productsPerPage;
$sql = "SELECT id FROM products LIMIT $offset, $productsPerPage";
$result = $conn->query($sql);

$response = [
    'hasNextPage' => $result->num_rows > 0,
    'nextPageUrl' => 'index.php?page=' . ($currentPage + 1)
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
