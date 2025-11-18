<?php 
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('momo_config.php');

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

  // Tạo request đến MoMo
  $ORDER_ID = $booking_data['order_id'];
  $TXN_AMOUNT = $booking_data['trans_amt'];
  
  $requestId = time() . "";
  $requestType = "payWithATM";
  $extraData = "";

  // Lưu booking_id vào session để cập nhật sau
  $_SESSION['payment_booking_id'] = $booking_id;
  
  $rawHash = "accessKey=" . MOMO_ACCESS_KEY . 
             "&amount=" . $TXN_AMOUNT . 
             "&extraData=" . $extraData . 
             "&ipnUrl=" . MOMO_NOTIFY_URL . 
             "&orderId=" . $ORDER_ID . 
             "&orderInfo=Thanh toan dat phong " . $booking_data['room_name'] . 
             "&partnerCode=" . MOMO_PARTNER_CODE . 
             "&redirectUrl=" . MOMO_RETURN_URL . 
             "&requestId=" . $requestId . 
             "&requestType=" . $requestType;
  
  $signature = hash_hmac("sha256", $rawHash, MOMO_SECRET_KEY);
  
  $data = array(
    'partnerCode' => MOMO_PARTNER_CODE,
    'partnerName' => "VinhCenter",
    'storeId' => "VinhCenter",
    'requestId' => $requestId,
    'amount' => $TXN_AMOUNT,
    'orderId' => $ORDER_ID,
    'orderInfo' => "Thanh toan dat phong " . $booking_data['room_name'],
    'redirectUrl' => MOMO_RETURN_URL,
    'ipnUrl' => MOMO_NOTIFY_URL,
    'lang' => 'vi',
    'extraData' => $extraData,
    'requestType' => $requestType,
    'signature' => $signature
  );
  
  $result = execPostRequest(MOMO_ENDPOINT, json_encode($data));
  $jsonResult = json_decode($result, true);
  
  if(isset($jsonResult['payUrl'])) {
    header('Location: ' . $jsonResult['payUrl']);
    exit();
  } else {
    redirect('../booking/bookings.php?payment_error=true');
  }
?>
