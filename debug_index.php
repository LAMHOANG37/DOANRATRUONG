<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG INDEX.PHP ===<br><br>";

// Test 1: Check if files exist
echo "<h3>1. Kiểm tra file tồn tại:</h3>";
$files = [
    'inc/links.php',
    'inc/db_config.php',
    'inc/essentials.php',
    'inc/header.php',
    'inc/footer.php'
];

foreach($files as $file) {
    if(file_exists($file)) {
        echo "✅ $file - OK<br>";
    } else {
        echo "❌ $file - KHÔNG TỒN TẠI<br>";
    }
}

echo "<br><h3>2. Test require inc/links.php:</h3>";
try {
    require('inc/links.php');
    echo "✅ inc/links.php loaded successfully<br>";
    echo "✅ Settings: " . $settings_r['site_title'] . "<br>";
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>3. Test database connection:</h3>";
if(isset($con) && $con) {
    echo "✅ Database connected<br>";
} else {
    echo "❌ Database NOT connected<br>";
}

echo "<br><h3>4. Test query:</h3>";
try {
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM rooms");
    if($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Total rooms: " . $row['total'] . "<br>";
    }
} catch(Exception $e) {
    echo "❌ Query error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>5. Test images path:</h3>";
echo "SITE_URL: " . SITE_URL . "<br>";
echo "ROOMS_IMG_PATH: " . ROOMS_IMG_PATH . "<br>";

echo "<br><h3>6. Nếu tất cả OK, index.php sẽ hoạt động!</h3>";
?>
