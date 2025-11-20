<?php 

  require('../admin/inc/db_config.php');
  require('../admin/inc/essentials.php');

  session_start();

  $area_filter = isset($_GET['area']) && $_GET['area'] != '' ? $_GET['area'] : null;

  // Query hotels
  if($area_filter != null){
    $hotel_res = select("SELECT h.*, a.name as area_name FROM `hotels` h 
                        INNER JOIN `areas` a ON h.area_id = a.id 
                        WHERE h.area_id=? AND h.status=? AND h.removed=? 
                        ORDER BY h.rating DESC",[$area_filter,1,0],'iii');
  }
  else{
    $hotel_res = select("SELECT h.*, a.name as area_name FROM `hotels` h 
                        INNER JOIN `areas` a ON h.area_id = a.id 
                        WHERE h.status=? AND h.removed=? 
                        ORDER BY h.rating DESC",[1,0],'ii');
  }

  $count_hotels = mysqli_num_rows($hotel_res);
  $output = "";

  if($count_hotels > 0){
    while($hotel_data = mysqli_fetch_assoc($hotel_res))
    {
      // Get hotel image
      $hotel_img = HOTELS_IMG_PATH.$hotel_data['image'];

      // Count rooms in hotel
      $room_count_q = mysqli_query($con,"SELECT COUNT(*) as total FROM `rooms` WHERE `hotel_id`='$hotel_data[id]' AND `status`=1 AND `removed`=0");
      $room_count = mysqli_fetch_assoc($room_count_q)['total'];

      // Rating stars
      $rating_stars = "";
      for($i=0; $i<floor($hotel_data['rating']); $i++){
        $rating_stars .="<i class='bi bi-star-fill text-warning'></i> ";
      }

      $output .= "
        <div class='card mb-4 border-0 shadow'>
          <div class='row g-0 p-3 align-items-center'>
            <div class='col-md-4 mb-lg-0 mb-md-0 mb-3'>
              <img src='$hotel_img' class='img-fluid rounded' style='height: 200px; width: 100%; object-fit: cover;'>
            </div>
            <div class='col-md-6 px-lg-3 px-md-3 px-0'>
              <h5 class='mb-2'>$hotel_data[name]</h5>
              <div class='mb-2'>
                <span class='badge bg-primary'>$hotel_data[area_name]</span>
              </div>
              <p class='text-muted mb-2'>
                <i class='bi bi-geo-alt-fill'></i> $hotel_data[address]
              </p>
              <p class='text-muted mb-2'>
                <i class='bi bi-telephone-fill'></i> $hotel_data[phone]
              </p>
              <div class='mb-2'>
                <span class='badge rounded-pill bg-light text-dark'>
                  <i class='bi bi-door-open'></i> $room_count phòng
                </span>
              </div>
              <div class='rating'>
                $rating_stars
                <span class='text-muted'>($hotel_data[rating])</span>
              </div>
            </div>
            <div class='col-md-2 text-center'>
              <a href='booking/rooms.php?hotel_id=$hotel_data[id]' class='btn btn-sm w-100 custom-bg text-white shadow-none mb-2'>Xem phòng</a>
              <a href='tel:$hotel_data[phone]' class='btn btn-sm w-100 btn-outline-dark shadow-none'>
                <i class='bi bi-telephone'></i> Liên hệ
              </a>
            </div>
          </div>
        </div>
      ";
    }
    echo $output;
  }
  else{
    echo "<h3 class='text-center text-danger'>Không tìm thấy khách sạn nào!</h3>";
  }

?>
