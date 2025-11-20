<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - Khách sạn</title>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <div class="my-5 px-4">
    <h2 class="fw-bold h-font text-center">KHÁCH SẠN TẠI THÀNH PHỐ VINH</h2>
    <div class="h-line bg-dark"></div>
  </div>

  <div class="container-fluid">
    <div class="row">

      <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
          <div class="container-fluid flex-lg-column align-items-stretch">
            <h4 class="mt-2">Bộ lọc</h4>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropdown">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropdown">
              
              <!-- Area filter -->
              <div class="border bg-light p-3 rounded mb-3">
                <h5 class="mb-3" style="font-size: 18px;">
                  <span>Khu vực</span>
                </h5>
                <?php 
                  $area_q = selectAll('areas');
                  while($row = mysqli_fetch_assoc($area_q))
                  {
                    if($row['status'] == 1){
                      $checked = '';
                      if(isset($_GET['area']) && $_GET['area'] == $row['id']){
                        $checked = 'checked';
                      }
                      echo<<<areas
                        <div class="mb-2">
                          <input type="radio" onclick="filter_hotels()" name="area" value="$row[id]" class="form-check-input shadow-none me-1" id="area$row[id]" $checked>
                          <label class="form-check-label" for="area$row[id]">$row[name]</label>
                        </div>
                      areas;
                    }
                  }
                ?>
              </div>

            </div>
          </div>
        </nav>
      </div>

      <div class="col-lg-9 col-md-12 px-4" id="hotels-data">
        <!-- Hotels will be loaded here -->
      </div>

    </div>
  </div>

  <?php require('inc/footer.php'); ?>

  <script>
    function filter_hotels() {
      let area_val = '';
      let get_area = document.querySelector('[name="area"]:checked');
      if(get_area != null){
        area_val = get_area.value;
      }

      let xhr = new XMLHttpRequest();
      xhr.open("GET","ajax/hotels_list.php?area="+area_val,true);

      xhr.onprogress = function(){
        document.getElementById('hotels-data').innerHTML = `<div class="spinner-border text-info mb-3 d-block mx-auto" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>`;
      }

      xhr.onload = function(){
        document.getElementById('hotels-data').innerHTML = this.responseText;
      }

      xhr.send();
    }

    window.onload = function(){
      filter_hotels();
    }
  </script>

</body>
</html>
