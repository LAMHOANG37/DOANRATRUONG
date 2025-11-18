<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?php echo (strpos($_SERVER['PHP_SELF'], '/booking/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../' : ''; ?>css/common.css">

<?php

  session_start();
  date_default_timezone_set("Asia/Ho_Chi_Minh");

  require(__DIR__ . '/db_config.php');
  require(__DIR__ . '/essentials.php');
  
  // Load contact and settings with error handling
  try {
    $contact_q = "SELECT * FROM `contact_details` WHERE `sr_no`=?";
    $settings_q = "SELECT * FROM `settings` WHERE `sr_no`=?";
    $values = [1];
    $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    $settings_r = mysqli_fetch_assoc(select($settings_q,$values,'i'));
  } catch (Exception $e) {
    die("Lỗi kết nối database: " . $e->getMessage() . "<br>Vui lòng kiểm tra: <br>1. MySQL đã chạy chưa?<br>2. Database 'vinhcenter' đã được import?<br>3. Thông tin kết nối trong inc/db_config.php đúng chưa?");
  }

  if($settings_r['shutdown']){
    echo<<<alertbar
      <div class='bg-danger text-center p-2 fw-bold'>
        <i class="bi bi-exclamation-triangle-fill"></i>
        Tạm thời không hỗ trợ đặt phòng!
      </div>
    alertbar;
  }
  
?>