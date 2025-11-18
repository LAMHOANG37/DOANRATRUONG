<?php 
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');
  require('momo_config.php');

  // Debug
  error_log("Session data: " . print_r($_SESSION, true));
  error_log("POST data: " . print_r($_POST, true));

  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    echo "Not logged in. Redirecting...";
    redirect('../index.php');
  }

  if(!isset($_POST['pay_now'])) {
    echo "No POST data. Redirecting...";
    redirect('../index.php');
  }

  if(!isset($_SESSION['room']) || !isset($_SESSION['room']['payment']) || $_SESSION['room']['payment'] == null) {
    echo "Error: Session room or payment not set. Please go back and select dates again.";
    echo "<br><a href='../booking/confirm_booking.php?id=".$_SESSION['room']['id']."'>Go Back</a>";
    exit();
  }

  if(isset($_POST['pay_now']))
  {
    $ORDER_ID = 'ORD_'.$_SESSION['uId'].time();    
    $CUST_ID = $_SESSION['uId'];
    $TXN_AMOUNT = $_SESSION['room']['payment'];
    
    $frm_data = filteration($_POST);

    // Tạo booking pending trước khi thanh toán
    $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`, `order_id`, `booking_status`, `trans_status`, `trans_amt`) 
    VALUES (?,?,?,?,?,?,?,?)";

    insert($query1,[
      $CUST_ID,
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

    // Lưu booking_id vào session để cập nhật sau
    $_SESSION['payment_booking_id'] = $booking_id;
    
    // Lưu thông tin booking vào session để xử lý khi thanh toán thất bại
    $_SESSION['booking_data'] = [
      'booking_id' => $booking_id,
      'order_id' => $ORDER_ID,
      'user_id' => $CUST_ID,
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

    // Tạo request đến MoMo
    $requestId = time() . "";
    $requestType = "payWithATM";
    $extraData = "";
    
    $rawHash = "accessKey=" . MOMO_ACCESS_KEY . 
               "&amount=" . $TXN_AMOUNT . 
               "&extraData=" . $extraData . 
               "&ipnUrl=" . MOMO_NOTIFY_URL . 
               "&orderId=" . $ORDER_ID . 
               "&orderInfo=Thanh toan dat phong " . $_SESSION['room']['name'] . 
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
      'orderInfo' => "Thanh toan dat phong " . $_SESSION['room']['name'],
      'redirectUrl' => MOMO_RETURN_URL,
      'ipnUrl' => MOMO_NOTIFY_URL,
      'lang' => 'vi',
      'extraData' => $extraData,
      'requestType' => $requestType,
      'signature' => $signature
    );
    
    $result = execPostRequest(MOMO_ENDPOINT, json_encode($data));
    $jsonResult = json_decode($result, true);
    
    // Debug: Log response
    error_log("MoMo Response: " . print_r($jsonResult, true));
    
    if(isset($jsonResult['payUrl'])) {
      header('Location: ' . $jsonResult['payUrl']);
      exit();
    } else {
      // Hiển thị lỗi để debug
      echo "MoMo Error: ";
      echo "<pre>";
      print_r($jsonResult);
      echo "</pre>";
      echo "<br><a href='../booking/confirm_booking.php?id=".$_SESSION['room']['id']."'>Go Back</a>";
      exit();
      // redirect('../booking/confirm_booking.php?id='.$_SESSION['room']['id'].'&error=payment_failed');
    }
  }
?>