<?php
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

$results = select_all_products($pdo);

$searchTerm = isset($_GET['search']) ? test_input($_GET['search']) : '';
$per_page_record = 5;
$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$page = filter_var($page, FILTER_VALIDATE_INT) !== false ? (int)$page : 1;

$start_from = ($page - 1) * $per_page_record;

$query = "SELECT * FROM products LIMIT :start_from, :per_page";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);


$allowed_sort_columns = ['id', 'product_name', 'price'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort_columns) ? $_GET['sort_by'] : 'id';
$allowed_order_directions = ['ASC', 'DESC'];
$order = isset($_GET['order']) && in_array($_GET['order'], $allowed_order_directions) ? $_GET['order'] : 'ASC';

$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$price_from = $_GET['price_from'] ?? null;
$price_to = $_GET['price_to'] ?? null;

$category = isset($_GET['category']) ? $_GET['category'] : [];  
$tag = isset($_GET['tag']) ? $_GET['tag'] : [];  



$query = "
SELECT products.*, 
    GROUP_CONCAT(DISTINCT p_tags.name_ SEPARATOR ', ') AS tags, 
    GROUP_CONCAT(DISTINCT p_categories.name_ SEPARATOR ', ') AS categories,
    GROUP_CONCAT(DISTINCT g_images.name_ SEPARATOR ', ') AS gallery_images
FROM products
LEFT JOIN product_property pp_tags ON products.id = pp_tags.product_id
LEFT JOIN property p_tags ON pp_tags.property_id = p_tags.id AND p_tags.type_ = 'tag'
LEFT JOIN product_property pp_categories ON products.id = pp_categories.product_id
LEFT JOIN property p_categories ON pp_categories.property_id = p_categories.id AND p_categories.type_ = 'category'
LEFT JOIN product_property pp_gallery ON products.id = pp_gallery.product_id
LEFT JOIN property g_images ON pp_gallery.property_id = g_images.id AND g_images.type_ = 'gallery'
WHERE products.product_name LIKE :search_term
";


if (!empty($category) && $category[0] != 0) {
    if (is_string($category)) {
        $category = explode(',', $category);  
    }
    $categoryPlaceholders = implode(',', array_map(function ($index) {
        return ':category' . $index;
    }, array_keys($category)));
    $query .= " AND pp_categories.property_id IN ($categoryPlaceholders)";
}

if (!empty($tag) && $tag[0] != 0) {
    if (is_string($tag)) {
        $tag = explode(',', $tag); 
    }
    $tagPlaceholders = implode(',', array_map(function ($index) {
        return ':tag' . $index;
    }, array_keys($tag)));
    $query .= " AND pp_tags.property_id IN ($tagPlaceholders)";
}
if (!empty($gallery)) {
    $query .= " AND g_images.name_ LIKE :gallery"; 
}

if (!empty($date_from)) {
    $query .= " AND products.date >= :date_from"; 
}

if (!empty($date_to)) {
    $query .= " AND products.date <= :date_to"; 
}

if (!empty($price_from)) {
    $query .= " AND products.price >= :price_from"; 
}

if (!empty($price_to)) {
    $query .= " AND products.price <= :price_to";
}


$query .= " GROUP BY products.id 
            ORDER BY $sort_by $order 
            LIMIT :start_from, :per_page";


$stmt = $pdo->prepare($query);

$searchTermLike = "%$searchTerm%";
$stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);

if (!empty($category) && $category[0] != 0) {
    foreach ($category as $index => $category_id) {
        $stmt->bindValue(':category' . $index, $category_id, PDO::PARAM_INT);
    }
}

if (!empty($tag) && $tag[0] != 0) {
    foreach ($tag as $index => $tag_id) {
        $stmt->bindValue(':tag' . $index, $tag_id, PDO::PARAM_INT);
    }
}


if (!empty($date_from)) {
    $stmt->bindParam(':date_from', $date_from);
}

if (!empty($date_to)) {
    $stmt->bindParam(':date_to', $date_to);
}

if (!empty($price_from)) {
    $stmt->bindParam(':price_from', $price_from);
}

if (!empty($price_to)) {
    $stmt->bindParam(':price_to', $price_to);
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page_record, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($category) || !empty($tag) || (!empty($date_from) && !empty($date_to)) || (!empty($price_from) && !empty($price_to))) {
    $total_records = getRecordCount($pdo, $searchTermLike, $category, $tag, $date_from, $date_to, $price_from, $price_to);
} else {
    $count_query = "SELECT COUNT(*) FROM products WHERE product_name LIKE :search_term";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->bindParam(':search_term', $searchTermLike, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
}

?>