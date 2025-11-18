<?php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('vnpay_config.php');

  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    redirect('../index.php');
  }

  if(!isset($_POST['pay_now'])) {
    redirect('../index.php');
  }

  if(isset($_POST['pay_now']))
  {
    $ORDER_ID = 'ORD_'.$_SESSION['uId'].time();    
    $TXN_AMOUNT = $_SESSION['room']['payment'];
    
    $frm_data = filteration($_POST);

    // Tạo booking pending trước khi thanh toán
    $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `order_id`, `booking_status`, `trans_status`, `trans_amt`) 
    VALUES (?,?,?,?,?,?,?,?)";

    insert($query1,[
      $_SESSION['uId'],
      $_SESSION['room']['id'],
      $frm_data['checkin'],
      $frm_data['checkout'],
      $ORDER_ID,
      'pending',
      'pending',
      $TXN_AMOUNT
    ],'isssssis');
    
    $booking_id = mysqli_insert_id($con);

    // Insert booking details
    $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, `total_pay`,
      `user_name`, `phonenum`, `address`) VALUES (?,?,?,?,?,?,?)";

    insert($query2,[
      $booking_id,
      $_SESSION['room']['name'],
      $_SESSION['room']['price'],
      $TXN_AMOUNT,
      $frm_data['name'],
      $frm_data['phonenum'],
      $frm_data['address']
    ],'isiisss');

    // Lưu booking_id vào session
    $_SESSION['payment_booking_id'] = $booking_id;
    
    // Lưu thông tin booking vào session
    $_SESSION['booking_data'] = [
      'booking_id' => $booking_id,
      'order_id' => $ORDER_ID,
      'user_id' => $_SESSION['uId'],
      'room_id' => $_SESSION['room']['id'],
      'room_name' => $_SESSION['room']['name'],
      'room_price' => $_SESSION['room']['price'],
      'check_in' => $frm_data['checkin'],
      'check_out' => $frm_data['checkout'],
      'amount' => $TXN_AMOUNT,
      'name' => $frm_data['name'],
      'phonenum' => $frm_data['phonenum'],
      'address' => $frm_data['address']
    ];

    // Tạo request đến VNPay
    $vnp_TxnRef = $ORDER_ID;
    $vnp_OrderInfo = 'Thanh toan dat phong ' . $_SESSION['room']['name'];
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $TXN_AMOUNT * 100; // VNPay tính theo đơn vị nhỏ nhất
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
  }
?>
