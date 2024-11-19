<?php
require_once 'includes/db.inc.php';
require 'vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

$httpClient = new \Goutte\Client();
$response = $httpClient->request('GET', 'https://aliexpress.ru/item/1005007641037367.html?sku_id=12000041611596822');

$titles = $response->evaluate('//h1[@class="snow-ali-kit_Typography__base__1shggo snow-ali-kit_Typography-Primary__base__1xop0e snow-ali-kit_Typography__strong__1shggo snow-ali-kit_Typography__sizeHeadingL__1shggo HazeProductDescription_HazeProductDescription__name__1fmsi HazeProductDescription_HazeProductDescription__smallText__1fmsi"]');

$prices = $response->evaluate('//div[@class="HazeProductPrice_SnowPrice__mainS__k8qlm"]');

$sizes = $response->evaluate('//ul[@class="SnowSku_SkuPropertyItem__optionList__1lob1"]/li/button/span[2]');

$featured_images = $response->evaluate('//ul[@class="SnowSku_SkuPropertyItem__optionList__1lob1"]/li/button/picture/img');

$priceArray = [];
foreach ($prices as $key => $price) {
    $priceArray[] = $price->textContent;
}

$sizeArray = [];
foreach ($sizes as $size) {
    $sizeArray[] = $size->textContent;
}

$imageSrcArray = [];
foreach ($featured_images as $image) {
    $imageSrcArray[] = $image->getAttribute('src');
}

function convertPriceToUSD($rubPrice) {
    return $rubPrice * 0.01;
}

foreach ($titles as $key => $title) {
    $translatedTitle = GoogleTranslate::trans($title->textContent, 'en', 'ru');
    $productSizes = isset($sizeArray[$key]) ? implode(", ", $sizeArray) : 'N/A';
    $rubPrice = floatval($priceArray[$key]);
    $usdPrice = convertPriceToUSD($rubPrice);
    $featuredImageSrc = $imageSrcArray[$key] ?? null; 

    echo "Product Title: " . $translatedTitle . ' @ ' . $productSizes . PHP_EOL;

    $sizes = explode(', ', $productSizes);
    foreach ($sizes as $size) {
        $name = $translatedTitle . ' ' . $size;
        echo 'Name: ' . $name . PHP_EOL;
        echo 'Image Src: ' . $featuredImageSrc . PHP_EOL;

        $checkStmt = $pdo->prepare('SELECT * FROM products WHERE product_name = :product_name');
        $checkStmt->execute(['product_name' => $name]);
        $existingProduct = $checkStmt->fetch();

        if ($existingProduct) {
            $updateStmt = $pdo->prepare('UPDATE products SET price = :price, featured_image = :featured_image, date = now() WHERE product_name = :product_name');
            $updateStmt->execute([
                'price' => $usdPrice,
                'featured_image' => $featuredImageSrc,
                'product_name' => $name,
            ]);
            echo 'Product updated: ' . $name . PHP_EOL;
        } else {
            $insertStmt = $pdo->prepare('INSERT INTO products (product_name, price, featured_image, date) VALUES (:product_name, :price, :featured_image, now())');
            $insertStmt->execute([
                'product_name' => $name,
                'price' => $usdPrice,
                'featured_image' => $featuredImageSrc,
            ]);
            echo 'Product inserted: ' . $name . PHP_EOL;
        }
    }
}
?>
