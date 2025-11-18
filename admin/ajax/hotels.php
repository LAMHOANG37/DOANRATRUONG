<?php 

  require('../inc/db_config.php');
  require('../inc/essentials.php');
  adminLogin();

  if(isset($_POST['add_hotel']))
  {
    $frm_data = filteration($_POST);

    // Xử lý upload ảnh (nếu có)
    $img_r = 'default.jpg'; // ảnh mặc định
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
      $img_r = uploadImage($_FILES['image'],HOTELS_FOLDER);

      if($img_r == 'inv_img'){
        echo $img_r;
        exit;
      }
      else if($img_r == 'inv_size'){
        echo $img_r;
        exit;
      }
      else if($img_r == 'upd_failed'){
        echo $img_r;
        exit;
      }
    }

    $q = "INSERT INTO `hotels`(`area_id`, `name`, `address`, `description`, `phone`, `email`, `image`) VALUES (?,?,?,?,?,?,?)";
    $values = [$frm_data['area'],$frm_data['name'],$frm_data['address'],$frm_data['description'],$frm_data['phone'],$frm_data['email'],$img_r];
    $res = insert($q,$values,'issssss');
    echo $res;
  }

  if(isset($_POST['get_all_hotels']))
  {
    $res = select("SELECT h.*, a.name as area_name FROM `hotels` h 
                   INNER JOIN `areas` a ON h.area_id = a.id 
                   WHERE h.removed=?", [0], 'i');
    $i=1;

    $data = "";

    while($row = mysqli_fetch_assoc($res))
    {
      if($row['status']==1){
        $status = "<button onclick='toggle_status($row[id],0)' class='btn btn-dark btn-sm shadow-none'>active</button>";
      }
      else{
        $status = "<button onclick='toggle_status($row[id],1)' class='btn btn-warning btn-sm shadow-none'>inactive</button>";
      }

      $data.="
        <tr class='align-middle'>
          <td>$i</td>
          <td><img src='".HOTELS_IMG_PATH.$row['image']."' width='100px'></td>
          <td>$row[name]</td>
          <td>$row[area_name]</td>
          <td>$row[address]</td>
          <td>$row[rating] ⭐</td>
          <td>$status</td>
          <td>
            <button type='button' onclick='edit_details($row[id])' class='btn btn-primary shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#edit-hotel'>
              <i class='bi bi-pencil-square'></i>
            </button>
            <button type='button' onclick=\"hotel_images($row[id],'$row[name]')\" class='btn btn-info shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#hotel-images'>
              <i class='bi bi-images'></i>
            </button>
            <button type='button' onclick='remove_hotel($row[id])' class='btn btn-danger shadow-none btn-sm'>
              <i class='bi bi-trash'></i>
            </button>
          </td>
        </tr>
      ";
      $i++;
    }

    echo $data;
  }

  if(isset($_POST['get_hotel']))
  {
    $frm_data = filteration($_POST);

    $res = select("SELECT * FROM `hotels` WHERE `id`=?",[$frm_data['get_hotel']],'i');

    $data = mysqli_fetch_assoc($res);

    echo json_encode($data);
  }

  if(isset($_POST['edit_hotel']))
  {
    $frm_data = filteration($_POST);

    // Kiểm tra nếu có upload ảnh mới
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
      // Lấy ảnh cũ để xóa
      $res1 = select("SELECT `image` FROM `hotels` WHERE `id`=?",[$frm_data['hotel_id']],'i');
      $old_img = mysqli_fetch_assoc($res1);
      
      // Upload ảnh mới
      $img_r = uploadImage($_FILES['image'],HOTELS_FOLDER);

      if($img_r == 'inv_img' || $img_r == 'inv_size' || $img_r == 'upd_failed'){
        echo $img_r;
        exit;
      }

      // Xóa ảnh cũ (nếu không phải default)
      if($old_img['image'] != 'default.jpg'){
        deleteImage($old_img['image'],HOTELS_FOLDER);
      }

      // Cập nhật với ảnh mới
      $q = "UPDATE `hotels` SET `area_id`=?,`name`=?,`address`=?,`description`=?,`phone`=?,`email`=?,`image`=? WHERE `id`=?";
      $values = [$frm_data['area'],$frm_data['name'],$frm_data['address'],$frm_data['description'],$frm_data['phone'],$frm_data['email'],$img_r,$frm_data['hotel_id']];
      $res = update($q,$values,'issssssi');
      echo ($res >= 0) ? 1 : 0;
    }
    else{
      // Không đổi ảnh
      $q = "UPDATE `hotels` SET `area_id`=?,`name`=?,`address`=?,`description`=?,`phone`=?,`email`=? WHERE `id`=?";
      $values = [$frm_data['area'],$frm_data['name'],$frm_data['address'],$frm_data['description'],$frm_data['phone'],$frm_data['email'],$frm_data['hotel_id']];
      $res = update($q,$values,'isssssi');
      echo ($res >= 0) ? 1 : 0;
    }
  }

  if(isset($_POST['toggle_status']))
  {
    $frm_data = filteration($_POST);

    $q = "UPDATE `hotels` SET `status`=? WHERE `id`=?";
    $v = [$frm_data['value'],$frm_data['toggle_status']];
    $res = update($q,$v,'ii');
    echo $res;
  }

  if(isset($_POST['remove_hotel']))
  {
    $frm_data = filteration($_POST);

    $res1 = select("SELECT * FROM `hotels` WHERE `id`=?",[$frm_data['remove_hotel']],'i');
    $row = mysqli_fetch_assoc($res1);

    if(deleteImage($row['image'],HOTELS_FOLDER)){
      $q = "UPDATE `hotels` SET `removed`=? WHERE `id`=?";
      $values = [1,$frm_data['remove_hotel']];
      $res = update($q,$values,'ii');
      echo $res;
    }
    else{
      echo 0;
    }
  }

?>
