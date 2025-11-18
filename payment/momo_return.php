<?php 
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('momo_config.php');

  session_start();

  if(!isset($_SESSION['login']) || $_SESSION['login']!=true){
    redirect('../index.php');
  }

  // Lấy thông tin từ MoMo callback
  $partnerCode = $_GET['partnerCode'] ?? '';
  $orderId = $_GET['orderId'] ?? '';
  $requestId = $_GET['requestId'] ?? '';
  $amount = $_GET['amount'] ?? '';
  $orderInfo = $_GET['orderInfo'] ?? '';
  $orderType = $_GET['orderType'] ?? '';
  $transId = $_GET['transId'] ?? '';
  $resultCode = $_GET['resultCode'] ?? '';
  $message = $_GET['message'] ?? '';
  $payType = $_GET['payType'] ?? '';
  $responseTime = $_GET['responseTime'] ?? '';
  $extraData = $_GET['extraData'] ?? '';
  $signature = $_GET['signature'] ?? '';

  // Kiểm tra chữ ký
  $rawHash = "accessKey=" . MOMO_ACCESS_KEY . 
             "&amount=" . $amount . 
             "&extraData=" . $extraData . 
             "&message=" . $message . 
             "&orderId=" . $orderId . 
             "&orderInfo=" . $orderInfo . 
             "&orderType=" . $orderType . 
             "&partnerCode=" . $partnerCode . 
             "&payType=" . $payType . 
             "&requestId=" . $requestId . 
             "&responseTime=" . $responseTime . 
             "&resultCode=" . $resultCode . 
             "&transId=" . $transId;
  
  $checkSignature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);

  // Xử lý kết quả thanh toán
  if($resultCode == 0 && isset($_SESSION['payment_booking_id'])) {
    // Thanh toán thành công - Cập nhật booking từ pending sang booked
    $booking_id = $_SESSION['payment_booking_id'];
    
    $query = "UPDATE `booking_order` SET `trans_status`='TXN_SUCCESS', `trans_id`=?, `booking_status`='booked' WHERE `booking_id`=?";
    update($query, [$transId, $booking_id], 'si');
    
    unset($_SESSION['payment_booking_id']);
    unset($_SESSION['booking_data']);
    redirect('../booking/bookings.php?payment_success=true');
  } else {
    // Thanh toán thất bại hoặc hủy
    $room_id = null;
    
    if(isset($_SESSION['payment_booking_id'])) {
      // Lấy room_id từ booking_id
      $booking_id = $_SESSION['payment_booking_id'];
      $booking_q = select("SELECT `room_id` FROM `booking_order` WHERE `booking_id`=?", [$booking_id], 'i');
      if(mysqli_num_rows($booking_q) > 0) {
        $booking_data = mysqli_fetch_assoc($booking_q);
        $room_id = $booking_data['room_id'];
      }
      unset($_SESSION['payment_booking_id']);
    }
    
    if(isset($_SESSION['booking_data'])) {
      $room_id = $_SESSION['booking_data']['room_id'];
      unset($_SESSION['booking_data']);
    }
    
    // Redirect về trang chi tiết phòng nếu có room_id, nếu không thì về bookings
    if($room_id) {
      redirect('../booking/room_details.php?id='.$room_id.'&payment_cancelled=true');
    } else {
      redirect('../booking/bookings.php?payment_cancelled=true');
    }
  }
?>
