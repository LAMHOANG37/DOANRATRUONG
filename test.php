<?php
// Test file để kiểm tra PHP và database
echo "<h1>Test PHP</h1>";
echo "<p>PHP đang hoạt động!</p>";

// Test database connection
$hname = 'localhost';
$uname = 'root';
$pass = '';
$db = 'vinhcenter';

$con = mysqli_connect($hname, $uname, $pass, $db);

if(!$con){
    echo "<p style='color:red'>❌ Không thể kết nối database: " . mysqli_connect_error() . "</p>";
} else {
    echo "<p style='color:green'>✅ Kết nối database thành công!</p>";
    
    // Test query
    $result = mysqli_query($con, "SELECT * FROM settings LIMIT 1");
    if($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p style='color:green'>✅ Query database thành công!</p>";
        echo "<p>Site Title: " . $row['site_title'] . "</p>";
    } else {
        echo "<p style='color:red'>❌ Lỗi query: " . mysqli_error($con) . "</p>";
    }
}

echo "<hr>";
echo "<h2>Thông tin hệ thống:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current File: " . __FILE__ . "</p>";
?>
