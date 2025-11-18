<?php
// File này sẽ tự động hủy các booking pending quá 15 phút
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

// Tìm các booking pending quá 15 phút
$query = "UPDATE `booking_order` 
          SET `booking_status`='cancelled' 
          WHERE `booking_status`='pending' 
          AND `trans_status`='pending' 
          AND TIMESTAMPDIFF(MINUTE, `datentime`, NOW()) > 15";

mysqli_query($con, $query);

echo "Auto-cancel completed";
?>
