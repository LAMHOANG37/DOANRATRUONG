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
                        ORDER BY h.rating DESC LIMIT 6",[$area_filter,1,0],'iii');
  }
  else{
    $hotel_res = select("SELECT h.*, a.name as area_name FROM `hotels` h 
                        INNER JOIN `areas` a ON h.area_id = a.id 
                        WHERE h.status=? AND h.removed=? 
                        ORDER BY h.rating DESC LIMIT 6",[1,0],'ii');
  }

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

    // print hotel card
    echo <<<data
      <div class="col-lg-4 col-md-6 my-3">
        <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
          <img src="$hotel_img" class="card-img-top" style="height: 200px; object-fit: cover;">
          <div class="card-body">
            <h5>$hotel_data[name]</h5>
            <div class="mb-2">
              <span class="badge bg-primary">$hotel_data[area_name]</span>
            </div>
            <p class="text-muted small mb-2">
              <i class="bi bi-geo-alt-fill"></i> $hotel_data[address]
            </p>
            <div class="mb-3">
              <span class="badge rounded-pill bg-light text-dark">
                <i class="bi bi-door-open"></i> $room_count phòng
              </span>
            </div>
            <div class="rating mb-3">
              $rating_stars
              <span class="text-muted">($hotel_data[rating])</span>
            </div>
            <div class="d-flex justify-content-between">
              <a href="booking/rooms.php?hotel_id=$hotel_data[id]" class="btn btn-sm btn-outline-dark shadow-none">Xem phòng</a>
              <a href="tel:$hotel_data[phone]" class="btn btn-sm custom-bg text-white shadow-none">
                <i class="bi bi-telephone"></i> Liên hệ
              </a>
            </div>
          </div>
        </div>
      </div>
    data;
  }

?>
