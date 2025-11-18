<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// VNPay Configuration
define('VNPAY_TMN_CODE', 'V37HKQM4'); // Mã website tại VNPay
define('VNPAY_HASH_SECRET', 'G3XW79ENGDDKHS22LPP6KAQ4Y9WHRCLI'); // Chuỗi bí mật
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', 'http://localhost/vinhcenter/payment/vnpay_return.php');
define('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction');
?>
