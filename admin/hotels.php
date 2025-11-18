<?php
  require('inc/essentials.php');
  require('inc/db_config.php');
  adminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang quản lý - Khách sạn</title>
  <?php require('inc/links.php'); ?>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <div class="container-fluid" id="main-content">
    <div class="row">
      <div class="col-lg-10 ms-auto p-4 overflow-hidden">
        <h3 class="mb-4">QUẢN LÝ KHÁCH SẠN</h3>

        <!-- Hotels section -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">

            <div class="text-end mb-4">
              <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-hotel">
                <i class="bi bi-plus-square"></i> Thêm khách sạn
              </button>
            </div>

            <div class="table-responsive-lg" style="height: 450px; overflow-y: scroll;">
              <table class="table table-hover border text-center">
                <thead>
                  <tr class="bg-dark text-light">
                    <th scope="col">#</th>
                    <th scope="col">Ảnh</th>
                    <th scope="col">Tên khách sạn</th>
                    <th scope="col">Khu vực</th>
                    <th scope="col">Địa chỉ</th>
                    <th scope="col">Đánh giá</th>
                    <th scope="col">Trạng thái</th>
                    <th scope="col">Hành động</th>
                  </tr>
                </thead>
                <tbody id="hotel-data">
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Add hotel modal -->
  <div class="modal fade" id="add-hotel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form id="add_hotel_form">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm khách sạn</h5>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tên khách sạn</label>
                <input type="text" name="name" class="form-control shadow-none" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Khu vực</label>
                <select name="area" class="form-select shadow-none" required>
                  <option value="">Chọn khu vực</option>
                  <?php 
                    $area_res = selectAll('areas');
                    while($area = mysqli_fetch_assoc($area_res)){
                      echo "<option value='$area[id]'>$area[name]</option>";
                    }
                  ?>
                </select>
              </div>
              <div class="col-12 mb-3">
                <label class="form-label fw-bold">Địa chỉ</label>
                <textarea name="address" class="form-control shadow-none" rows="2" required></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Số điện thoại</label>
                <input type="text" name="phone" class="form-control shadow-none">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control shadow-none">
              </div>
              <div class="col-12 mb-3">
                <label class="form-label fw-bold">Mô tả</label>
                <textarea name="description" class="form-control shadow-none" rows="3"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Ảnh khách sạn</label>
                <input type="file" name="image" accept=".jpg,.png,.webp,.jpeg" class="form-control shadow-none">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">HỦY</button>
            <button type="submit" class="btn custom-bg text-white shadow-none">THÊM</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit hotel modal -->
  <div class="modal fade" id="edit-hotel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form id="edit_hotel_form">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Sửa khách sạn</h5>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tên khách sạn</label>
                <input type="text" name="name" class="form-control shadow-none" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Khu vực</label>
                <select name="area" class="form-select shadow-none" required>
                  <?php 
                    $area_res = selectAll('areas');
                    while($area = mysqli_fetch_assoc($area_res)){
                      echo "<option value='$area[id]'>$area[name]</option>";
                    }
                  ?>
                </select>
              </div>
              <div class="col-12 mb-3">
                <label class="form-label fw-bold">Địa chỉ</label>
                <textarea name="address" class="form-control shadow-none" rows="2" required></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Số điện thoại</label>
                <input type="text" name="phone" class="form-control shadow-none">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control shadow-none">
              </div>
              <div class="col-12 mb-3">
                <label class="form-label fw-bold">Mô tả</label>
                <textarea name="description" class="form-control shadow-none" rows="3"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Ảnh mới (nếu muốn thay đổi)</label>
                <input type="file" name="image" accept=".jpg,.png,.webp,.jpeg" class="form-control shadow-none">
              </div>
              <input type="hidden" name="hotel_id">
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">HỦY</button>
            <button type="submit" class="btn custom-bg text-white shadow-none">LƯU</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php require('inc/scripts.php'); ?>
  
  <script src="scripts/hotels.js"></script>

</body>
</html>
