<?php
require_once 'includes/db.inc.php';
require_once './includes/functions.php';

//get_product.php

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
        $category = explode(',', $category);  // Convert string to array
    }
    $categoryPlaceholders = implode(',', array_map(function ($index) {
        return ':category' . $index;
    }, array_keys($category)));
    $query .= " AND pp_categories.property_id IN ($categoryPlaceholders)";
}

if (!empty($tag) && $tag[0] != 0) {
    if (is_string($tag)) {
        $tag = explode(',', $tag);  // Convert string to array
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

$content = '';
$content .= "
<thead>
<tr>
<th class='date'>Date</th>
<th class='prd_name'>Product name</th>
<th>SKU</th>
<th>Price</th>
<th>Feature Image</th>
<th class='gallery_name'>Gallery</th>
<th >Categories</th>
<th class='tag_name'>Tags</th>
<th id='action_box' class='action_box'>
        <span>Action</span>
        <div class='box_delete_buttons'>
            <a  class='delete_buttons'>
                <i class='trash icon'></i>
            </a>
        </div>
    </th>
</tr>
</thead>
";
if (count($results) > 0) {
foreach ($results as $row) {
    $product_id = $row['id'];
    $galleryImages = $row['gallery_images'];
    $galleryImagesArray = !empty($galleryImages) ? explode(', ', $galleryImages) : [];
    $imageSrc = $row['featured_image'];

    $content .= '<tbody>';
    $content .= '<tr  data-id="' . $row['id'] . '">';
    $content .= '<td>' . htmlspecialchars($row['date']) . '</td>';
    $content .= '<td class="product_name">' . htmlspecialchars($row['product_name']) . '</td>';
    $content .= '<td class="sku">' . htmlspecialchars($row['sku']) . '</td>';
    $content .= '<td class="price">$' . htmlspecialchars($row['price']) . '</td>';
    $content .= '<td class="featured_image">';

    if (filter_var($row['featured_image'], FILTER_VALIDATE_URL)) {
        $content .= '<img height="30" src="' . $row['featured_image'] . '">';
    } else {
        $content .= '<img height="30" src="./uploads/' . $row['featured_image'] . '">';
    }
    
    $content .= '</td>';
    

    if (!empty($galleryImagesArray)) {
        $content .= '<td class="gallery"><div class="gallery-container">';
        foreach ($galleryImagesArray as $image) {
            $content .= '<img height="30" src="./uploads/' . htmlspecialchars($image) . '">';
        }
        $content .= '</div></td>';
    } else {
        $content .= '<td>No gallery images</td>';
    }

    $categorySelected = "SELECT p.name_ FROM product_property pp
    JOIN property p ON pp.property_id = p.id
    WHERE pp.product_id = :product_id AND p.type_ = 'category'";
    $stmt = $pdo->prepare($categorySelected);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $categoriesse = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categoryNames = [];
    foreach ($categoriesse as $category) {
        $categoryNames[] = $category['name_'];
    }
    $categoryList = implode(', ', $categoryNames);

    $content .= '<td class="category">' . htmlspecialchars($categoryList) . '</td>';

    $tagSelected = "SELECT p.name_ FROM product_property pp
                    JOIN property p ON pp.property_id = p.id
                    WHERE pp.product_id = :product_id AND p.type_ = 'tag'";
        $stmt = $pdo->prepare($tagSelected);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $tagsse = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tagNames = [];
    foreach ($tagsse as $tag) {
        $tagNames[] = $tag['name_'];
    }
    $tagList = implode(', ', $tagNames);

    $content .= '<td class="tag">' . htmlspecialchars($tagList) . '</td>';
    $content .= '<td class="box_action"><button value='.$row['id'].'
                  class="edit_button"><i class="edit icon"></i></button>';
    $content .= '<a class="delete_button" data-id="' . $row['id'] . '">
    <i class="trash icon"></i></a></td>';
    $content .= '</tr>';
    $content .= '</tbody>'; 
}


}else{
    $content .= '
        <tr>
            <td colspan="9" style="text-align: center;">Product not found</td>
        </tr>
    ';
}


// Tạo HTML cho phân trang
$pagination = '';
$inputpage = '';

$total_pages = ceil($total_records / $per_page_record);

$inputpage .= '
<input type="hidden" id="currentPages" value='.$page.'> 
';
$pagination .= '    

<div id="paginationBox" class="pagination_box">
<div class="ui pagination menu">
';
if ($page > 1) {
    $pagination .= '<a class="item pagination-link" data-page="' . ($page - 1) . '">
    <i class="arrow left icon"></i>
    </a>';
} else {
    $pagination .= '<a class="item disabled">
    <i class="arrow left icon"></i>
    </a>';
}

for ($i = 1; $i <= $total_pages; $i++) {
    $active_class = ($i == $page) ? 'active' : '';
    $pagination .= '<a class="item pagination-link ' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
}

if ($page < $total_pages) {
    $pagination .= '<a class="item pagination-link" data-page="' . ($page + 1) . '">
    <i class="arrow right icon"></i>
    </a>';
} else {
    $pagination .= '<a class="item disabled">
    <i class="arrow right icon"></i>
    </a>
   ';
}
$pagination .= '
   </div>
</div>
';

echo json_encode([
    'content' => $content,
    'pagination' => $pagination,
    "totalProducts" => $total_records,  
    "page" => $page,
    "inputpage" => $inputpage
]);
?>


