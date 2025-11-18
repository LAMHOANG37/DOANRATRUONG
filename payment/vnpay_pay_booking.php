<?php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('vnpay_config.php');

  session_start();

  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    redirect('../index.php');
  }

  if(!isset($_GET['booking_id'])){
    redirect('../bookings.php');
  }

  $booking_id = filteration($_GET)['booking_id'];

  // Lấy thông tin booking
  $query = "SELECT bo.*, bd.* FROM `booking_order` bo
    INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
    WHERE bo.booking_id = ? AND bo.user_id = ?";
  
  $result = select($query, [$booking_id, $_SESSION['uId']], 'ii');

  if(mysqli_num_rows($result) == 0){
    redirect('../bookings.php');
  }

  $booking_data = mysqli_fetch_assoc($result);

  // Kiểm tra đã thanh toán chưa
  if($booking_data['trans_status'] == 'TXN_SUCCESS' && !empty($booking_data['trans_id'])){
    redirect('../booking/bookings.php?already_paid=true');
  }

  // Lưu booking_id vào session
  $_SESSION['payment_booking_id'] = $booking_id;

  // Tạo request đến VNPay
  $vnp_TxnRef = $booking_data['order_id'];
  $vnp_OrderInfo = 'Thanh toan dat phong ' . $booking_data['room_name'];
  $vnp_OrderType = 'billpayment';
  $vnp_Amount = $booking_data['trans_amt'] * 100;
  $vnp_Locale = 'vn';
  $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];


  $inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => VNPAY_TMN_CODE,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => VNPAY_RETURN_URL,
    "vnp_TxnRef" => $vnp_TxnRef
  );

  ksort($inputData);
  $query = "";
  $i = 0;
  $hashdata = "";
  foreach ($inputData as $key => $value) {
    if ($i == 1) {
      $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
      $hashdata .= urlencode($key) . "=" . urlencode($value);
      $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
  }

  $vnp_Url = VNPAY_URL . "?" . $query;
  $vnpSecureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
  $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

  header('Location: ' . $vnp_Url);
  exit();
?>
