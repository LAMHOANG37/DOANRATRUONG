<?php
  session_start();
  
  // Kiểm tra đăng nhập
  if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
    header('Location: ../index.php');
    exit();
  }
  
  // Kiểm tra có POST data
  if(!isset($_POST['pay_now'])) {
    header('Location: ../index.php');
    exit();
  }
  
  // Kiểm tra phương thức thanh toán
  $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'momo';
  
  if($payment_method == 'vnpay') {
    include('vnpay_pay.php');
  } else {
    include('pay_now.php');
  }
?>
