<?php

if(isset($_POST['url'])) {
    $product_url = $_POST['url'];
    
    // Gửi request đến trang Aliexpress để lấy HTML
    $html = file_get_contents($product_url); // Hoặc dùng cURL nếu cần xử lý bảo mật/cookies
    
    // Tiến hành phân tích HTML để trích xuất thông tin
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Lấy tên sản phẩm chính
    $title = $xpath->query('//h1[@class="product-title-text"]')->item(0)->nodeValue;
    
    // Lấy thông tin các thuộc tính con (chỉ ví dụ, bạn có thể cần thay đổi XPath theo thực tế)
    $attributes = $xpath->query('//div[@class="product-options"]//li');
    
    // Lấy giá sản phẩm (RUB)
    $price_rub = $xpath->query('//span[@class="price-value"]')->item(0)->nodeValue;
    
    // Chuyển đổi giá RUB sang USD (1 RUB = 0.01 USD)
    $price_usd = floatval($price_rub) * 0.01;
    
    // Lấy ảnh chính của sản phẩm
    $image_url = $xpath->query('//img[@id="main-img"]')->item(0)->getAttribute('src');

    // Tạo các sản phẩm con từ các thuộc tính (ví dụ như màu sắc, kích thước...)
    $created_products = [];
    foreach($attributes as $attribute) {
        $attribute_name = $attribute->nodeValue;
        
        // Tạo tên sản phẩm con
        $sub_product_name = $title . ' - ' . $attribute_name;
        
        // Cập nhật ảnh và giá cho sản phẩm con
        $sub_product_image = $image_url; // Bạn có thể thay đổi nếu có ảnh riêng cho mỗi sản phẩm con
        $sub_product_price = $price_usd; // Giá đã chuyển đổi sang USD
        
        // Kiểm tra sản phẩm con có tồn tại trong cơ sở dữ liệu chưa
        $existing_product = checkIfProductExists($sub_product_name);
        
        if ($existing_product) {
            // Nếu sản phẩm con đã tồn tại, update thông tin
            updateProduct($existing_product['id'], $sub_product_name, $sub_product_image, $sub_product_price);
        } else {
            // Nếu chưa tồn tại, tạo mới sản phẩm
            createNewProduct($sub_product_name, $sub_product_image, $sub_product_price);
        }

        // Lưu vào mảng sản phẩm đã tạo
        $created_products[] = $sub_product_name;
    }
    
    echo json_encode($created_products); // Trả về danh sách sản phẩm đã tạo
    
} else {
    echo json_encode(['error' => 'URL is required']);
}

// Hàm kiểm tra sản phẩm đã tồn tại
function checkIfProductExists($name) {
    try {
        $host = 'localhost';
        $dbname = 'php1';
        $dbusername = 'root';
        $dbpassword = '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Truy vấn tìm sản phẩm theo tên
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_name = ?");
        $stmt->execute([$name]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            return $product; // Trả về sản phẩm nếu đã tồn tại
        } else {
            return null; // Không có sản phẩm nào
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Hàm cập nhật sản phẩm
function updateProduct($id, $name, $image, $price) {
    try {
        $host = 'localhost';
        $dbname = 'php1';
        $dbusername = 'root';
        $dbpassword = '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Cập nhật thông tin sản phẩm
        $stmt = $pdo->prepare("UPDATE products SET product_name = ?, featured_image = ?, price = ? WHERE id = ?");
        $stmt->execute([$name, $image, $price, $id]);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Hàm tạo sản phẩm mới
function createNewProduct($price) {
    try {
        $host = 'localhost';
        $dbname = 'php1';
        $dbusername = 'root';
        $dbpassword = '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Tạo mới sản phẩm
        $stmt = $pdo->prepare("INSERT INTO products (price) VALUES (?)");
        $stmt->execute([$price]);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
