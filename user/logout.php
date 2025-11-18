<?php 

  require('../admin/inc/essentials.php');

  session_start();
  session_destroy();
  header('Location: ../index.php');
  exit();

?>