<?php
require_once('vendor/autoload.php'); // Đảm bảo đã cài đặt thư viện qua Composer

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

// Đường dẫn Selenium Server
$host = 'http://localhost:4444/wd/hub';
$capabilities = DesiredCapabilities::chrome();
$driver = RemoteWebDriver::create($host, $capabilities);

// URL trang AliExpress
$url = 'https://aliexpress.ru/item/1005007641037367.html?sku_id=12000041611596822';
$driver->get($url);

// Lấy HTML sau khi JavaScript tải
$htmlContent = $driver->getPageSource();
$driver->quit();

// Xử lý HTML với simple_html_dom
include('simple_html_dom.php');
$html = str_get_html($htmlContent);
echo $html;
?>
