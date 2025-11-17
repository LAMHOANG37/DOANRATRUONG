<?php 

  require('../inc/db_config.php');
  require('../inc/essentials.php');
  adminLogin();

  if(isset($_POST['get_bookings']))
  {
    $frm_data = filteration($_POST);

    $query = "SELECT bo.*, bd.* FROM `booking_order` bo
      INNER JOIN `booking_details` bd ON bo.booking_id = bd.booking_id
      WHERE (bo.order_id LIKE ? OR bd.phonenum LIKE ? OR bd.user_name LIKE ?) 
      AND (bo.booking_status=?) ORDER BY bo.booking_id DESC";

    $res = select($query,["%$frm_data[search]%","%$frm_data[search]%","%$frm_data[search]%","pending"],'ssss');
    
    $i=1;
    $table_data = "";

    if(mysqli_num_rows($res)==0){
      echo"<tr><td colspan='5' class='text-center'><b>Không có đặt phòng nào đang chờ xử lý!</b></td></tr>";
      exit;
    }

    while($data = mysqli_fetch_assoc($res))
    {
      $date = date("d-m-Y H:i",strtotime($data['datentime']));
      $checkin = date("d-m-Y",strtotime($data['check_in']));
      $checkout = date("d-m-Y",strtotime($data['check_out']));

      $status_badge = "<span class='badge bg-warning text-dark'>Chờ xử lý</span>";

      $table_data .="
        <tr>
          <td>$i</td>
          <td>
            <span class='badge bg-primary'>
              Order ID: $data[order_id]
            </span>
            <br>
            <b>Tên:</b> $data[user_name]
            <br>
            <b>SĐT:</b> $data[phonenum]
            <br>
            <b>Địa chỉ:</b> $data[address]
          </td>
          <td>
            <b>Phòng:</b> $data[room_name]
            <br>
            <b>Giá:</b> ".number_format($data['price'])." VND/đêm
          </td>
          <td>
            <b>Check-in:</b> $checkin
            <br>
            <b>Check-out:</b> $checkout
            <br>
            <b>Tổng tiền:</b> ".number_format($data['total_pay'])." VND
            <br>
            <b>Ngày đặt:</b> $date
            <br>
            $status_badge
          </td>
          <td>
            <button type='button' onclick='confirm_booking($data[booking_id])' class='btn btn-success btn-sm fw-bold shadow-none mb-2'>
              <i class='bi bi-check-circle'></i> Xác nhận
            </button>
            <br>
            <button type='button' onclick='reject_booking($data[booking_id])' class='btn btn-danger btn-sm fw-bold shadow-none'>
              <i class='bi bi-x-circle'></i> Từ chối
            </button>
          </td>
        </tr>
      ";

      $i++;
    }

    echo $table_data;
  }

  if(isset($_POST['confirm_booking']))
  {
    $frm_data = filteration($_POST);
    
    // Chuyển trạng thái từ pending sang booked
    $query = "UPDATE `booking_order` SET `booking_status`=?, `trans_status`=? WHERE `booking_id`=?";
    $values = ['booked','TXN_SUCCESS',$frm_data['booking_id']];
    $res = update($query,$values,'ssi');

    echo $res;
  }

  if(isset($_POST['reject_booking']))
  {
    $frm_data = filteration($_POST);
    
    // Chuyển trạng thái từ pending sang cancelled
    $query = "UPDATE `booking_order` SET `booking_status`=?, `refund`=? WHERE `booking_id`=?";
    $values = ['cancelled',0,$frm_data['booking_id']];
    $res = update($query,$values,'sii');

    echo $res;
  }

?>
