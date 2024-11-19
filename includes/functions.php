<?php
function select_all_products(object $pdo)  {
    $query = "SELECT * FROM products";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo=null;
    $stmt=null;
    return $results;
}

function getPropertiesByType($pdo, $type) {
    $query = "SELECT id, name_ FROM property WHERE type_ = :type";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




function isValidInput($input){
    return preg_match('/^[\p{L}0-9 .,–\-_]+$/u', $input);
}

function check_duplicate(object $pdo, string $type_, string $name_) {
    try {
        $query = "SELECT COUNT(*) FROM property WHERE type_ = :type_ AND name_ = :name_";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['type_' => $type_, 'name_' => $name_]);
        
        return $stmt->fetchColumn() > 0; 
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}


function insert_property(object $pdo, string $type_, string $name_) {
    try {
        $data = [
            'type_' => $type_,
            'name_' => $name_
        ];

        $query = "INSERT INTO property (type_, name_) VALUES (:type_, :name_)";
        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute($data)) {
            return $pdo->lastInsertId(); 
        } else {
            return false; 
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false; 
    }
}


function getRecordCount($pdo, $searchTermLike, $category = null, $tag = null, $date_from = null, 
                        $date_to = null, $price_from = null, $price_to = null) {
    $query = "SELECT COUNT(DISTINCT products.id) FROM products";
    $conditions = ["products.product_name LIKE :search_term"];
    $params = [':search_term' => $searchTermLike];
    
    if ($category) {
        $categoryPlaceholders = implode(',', array_map(function ($index) {
            return ':category' . $index;
        }, array_keys($category)));
        
        $query .= " JOIN product_property pp1 ON products.id = pp1.product_id AND pp1.property_id IN ($categoryPlaceholders)";
        
        foreach ($category as $index => $category_id) {
            $params[':category' . $index] = $category_id;
        }
    }

    if ($tag) {
        $tagPlaceholders = implode(',', array_map(function ($index) {
            return ':tag' . $index;
        }, array_keys($tag)));
        
        $query .= " JOIN product_property pp2 ON products.id = pp2.product_id AND pp2.property_id IN ($tagPlaceholders)";
        
        foreach ($tag as $index => $tag_id) {
            $params[':tag' . $index] = $tag_id;
        }
    }

    if ($date_from && $date_to) {
        $conditions[] = "products.date BETWEEN :date_from AND :date_to";
        $params[':date_from'] = $date_from;
        $params[':date_to'] = $date_to;
    }

    if ($price_from && $price_to) {
        $conditions[] = "products.price BETWEEN :price_from AND :price_to";
        $params[':price_from'] = $price_from;
        $params[':price_to'] = $price_to;
    }

    if (count($conditions) > 0) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    return $stmt->fetchColumn();
}


function isValidInputSKU($input) {
    return preg_match('/^[\p{L}0-9 .,–\-\s]*$/u', $input);
}


function isValidNumberWithDotInput($input) {
    return preg_match('/^[0-9.]+$/', $input);
}

function generateSKU() {
    function generateRandomLetter() {
        return chr(rand(97, 122)); 
    }

    $part1 = generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter(); 
    $part2 = generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter(); 
    $part3 = generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter() . generateRandomLetter(); 
    
    $sku = $part1 . '-' . $part2 . '-' . $part3;

    return $sku;
}


function update_product(object $pdo, int $product_id, string $product_name, string $sku, string $price, string $featured_image){
    $data = [
        'product_id' => $product_id,
        'product_name' => $product_name, 
        'sku' => $sku, 
        'price' => $price, 
        'featured_image' => $featured_image, 
    ];
    
    $query = "UPDATE products 
              SET product_name = :product_name, 
                  sku = :sku, 
                  price = :price, 
                  featured_image = :featured_image 
              WHERE id = :product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_name", $product_name);
    $stmt->bindParam(":sku", $sku);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":featured_image", $featured_image);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute($data);

    return $stmt->rowCount(); 
}


function update_product_no_image(object $pdo, int $product_id, string $product_name, string $sku, string $price){
    $data = [
        'product_id' => $product_id,
        'product_name' => $product_name, 
        'sku' => $sku, 
        'price' => $price, 
    ];
    
    $query = "UPDATE products 
              SET product_name = :product_name, 
                  sku = :sku, 
                  price = :price
              WHERE id = :product_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_name", $product_name);
    $stmt->bindParam(":sku", $sku);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute($data);

    return $stmt->rowCount(); 
}


function insert_product(object $pdo, string $product_name, string $sku, string $price, string $featured_image){
    $data = [
        'product_name' => $product_name, 
        'sku' => $sku, 
        'price' => $price, 
        'featured_image' => $featured_image, 

        ];
        
        $query = "INSERT INTO products (product_name, sku, price,featured_image, date) VALUES (:product_name, :sku, :price,:featured_image, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":sku", $sku);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":featured_image", $featured_image);
        $stmt->execute($data);
        return $pdo->lastInsertId();
}



function add_product_property(PDO $pdo, int $product_id, int $property_id) {
    $query = "INSERT INTO product_property (product_id, property_id) VALUES (:product_id, :property_id);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->bindParam(":property_id", $property_id);
    $stmt->execute();
}


?>