<?php 
  // File này xử lý IPN (Instant Payment Notification) từ MoMo
  require('../admin/inc/db_config.php');
  require('momo_config.php');

  // Nhận dữ liệu từ MoMo
  $data = file_get_contents('php://input');
  $jsonData = json_decode($data, true);

  // Log để debug (tùy chọn)
  file_put_contents('momo_ipn_log.txt', date('Y-m-d H:i:s') . " - " . $data . "\n", FILE_APPEND);

  // Xác thực chữ ký và cập nhật trạng thái đơn hàng nếu cần
  if(isset($jsonData['resultCode']) && $jsonData['resultCode'] == 0) {
    // Thanh toán thành công, có thể cập nhật database ở đây
    $orderId = $jsonData['orderId'];
    $transId = $jsonData['transId'];
    
    // Cập nhật trạng thái nếu cần
    // mysqli_query($con, "UPDATE booking_order SET trans_id='$transId' WHERE order_id='$orderId'");
  }

  // Trả về response cho MoMo
  header('Content-Type: application/json');
  echo json_encode(['status' => 'success']);
?>
