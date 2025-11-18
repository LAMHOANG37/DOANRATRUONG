<?php
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('vnpay_config.php');

  session_start();

  if(!isset($_SESSION['login']) || $_SESSION['login']!=true){
    redirect('../index.php');
  }

  // Lấy thông tin từ VNPay callback
  $vnp_SecureHash = $_GET['vnp_SecureHash'];
  $inputData = array();
  foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
      $inputData[$key] = $value;
    }
  }
  
  unset($inputData['vnp_SecureHash']);
  ksort($inputData);
  $i = 0;
  $hashData = "";
  foreach ($inputData as $key => $value) {
    if ($i == 1) {
      $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
      $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
      $i = 1;
    }
  }

  $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);
  
  $vnp_ResponseCode = $_GET['vnp_ResponseCode'];
  $vnp_TxnRef = $_GET['vnp_TxnRef'];
  $vnp_Amount = $_GET['vnp_Amount'] / 100;
  $vnp_TransactionNo = $_GET['vnp_TransactionNo'];


  // Xử lý kết quả thanh toán
  if($secureHash == $vnp_SecureHash) {
    if($vnp_ResponseCode == '00' && isset($_SESSION['payment_booking_id'])) {
      // Thanh toán thành công - Cập nhật booking từ pending sang booked
      $booking_id = $_SESSION['payment_booking_id'];
      
      $query = "UPDATE `booking_order` SET `trans_status`='TXN_SUCCESS', `trans_id`=?, `booking_status`='booked' WHERE `booking_id`=?";
      update($query, [$vnp_TransactionNo, $booking_id], 'si');
      
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
  } else {
    // Chữ ký không hợp lệ
    $room_id = null;
    
    if(isset($_SESSION['payment_booking_id'])) {
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
    
    if($room_id) {
      redirect('../booking/room_details.php?id='.$room_id.'&payment_error=true');
    } else {
      redirect('../booking/bookings.php?payment_error=true');
    }
  }
?>
