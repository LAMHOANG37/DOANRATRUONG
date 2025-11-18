let add_hotel_form = document.getElementById('add_hotel_form');
let edit_hotel_form = document.getElementById('edit_hotel_form');

add_hotel_form.addEventListener('submit',function(e){
  e.preventDefault();
  add_hotel();
});

function add_hotel()
{
  let data = new FormData();
  data.append('add_hotel','');
  data.append('name',add_hotel_form.elements['name'].value);
  data.append('area',add_hotel_form.elements['area'].value);
  data.append('address',add_hotel_form.elements['address'].value);
  data.append('phone',add_hotel_form.elements['phone'].value);
  data.append('email',add_hotel_form.elements['email'].value);
  data.append('description',add_hotel_form.elements['description'].value);
  data.append('image',add_hotel_form.elements['image'].files[0]);

  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/hotels.php",true);

  xhr.onload = function(){
    var myModal = document.getElementById('add-hotel');
    var modal = bootstrap.Modal.getInstance(myModal);
    modal.hide();

    if(this.responseText == 'inv_img'){
      alert('error','Chỉ chấp nhận ảnh JPG, WEBP hoặc PNG!');
    }
    else if(this.responseText == 'inv_size'){
      alert('error','Ảnh phải nhỏ hơn 2MB!');
    }
    else if(this.responseText == 'upd_failed'){
      alert('error','Tải ảnh lên thất bại!');
    }
    else{
      alert('success','Thêm khách sạn thành công!');
      add_hotel_form.reset();
      get_all_hotels();
    }
  }

  xhr.send(data);
}

function get_all_hotels()
{
  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/hotels.php",true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function(){
    document.getElementById('hotel-data').innerHTML = this.responseText;
  }

  xhr.send('get_all_hotels');
}

function edit_details(id)
{
  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/hotels.php",true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function(){
    let data = JSON.parse(this.responseText);
    edit_hotel_form.elements['name'].value = data.name;
    edit_hotel_form.elements['area'].value = data.area_id;
    edit_hotel_form.elements['address'].value = data.address;
    edit_hotel_form.elements['phone'].value = data.phone;
    edit_hotel_form.elements['email'].value = data.email;
    edit_hotel_form.elements['description'].value = data.description;
    edit_hotel_form.elements['hotel_id'].value = data.id;
  }

  xhr.send('get_hotel='+id);
}

edit_hotel_form.addEventListener('submit',function(e){
  e.preventDefault();
  submit_edit_hotel();
});

function submit_edit_hotel()
{
  let data = new FormData();
  data.append('edit_hotel','');
  data.append('hotel_id',edit_hotel_form.elements['hotel_id'].value);
  data.append('name',edit_hotel_form.elements['name'].value);
  data.append('area',edit_hotel_form.elements['area'].value);
  data.append('address',edit_hotel_form.elements['address'].value);
  data.append('phone',edit_hotel_form.elements['phone'].value);
  data.append('email',edit_hotel_form.elements['email'].value);
  data.append('description',edit_hotel_form.elements['description'].value);
  
  // Thêm ảnh nếu có
  if(edit_hotel_form.elements['image'].files[0]){
    data.append('image',edit_hotel_form.elements['image'].files[0]);
  }

  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/hotels.php",true);

  xhr.onload = function(){
    var myModal = document.getElementById('edit-hotel');
    var modal = bootstrap.Modal.getInstance(myModal);
    modal.hide();

    console.log('Response:', this.responseText); // Debug

    if(this.responseText == '1'){
      alert('success','Cập nhật khách sạn thành công!');
      get_all_hotels();
    }
    else if(this.responseText == 'inv_img'){
      alert('error','Chỉ chấp nhận ảnh JPG, WEBP hoặc PNG!');
    }
    else if(this.responseText == 'inv_size'){
      alert('error','Ảnh phải nhỏ hơn 2MB!');
    }
    else if(this.responseText == 'upd_failed'){
      alert('error','Tải ảnh lên thất bại!');
    }
    else{
      alert('error','Cập nhật thất bại! Response: ' + this.responseText);
    }
  }

  xhr.send(data);
}

function toggle_status(id,val)
{
  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/hotels.php",true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function(){
    if(this.responseText==1){
      alert('success','Đã thay đổi trạng thái!');
      get_all_hotels();
    }
    else{
      alert('error','Thao tác thất bại!');
    }
  }

  xhr.send('toggle_status='+id+'&value='+val);
}

function remove_hotel(id)
{
  if(confirm("Bạn có chắc muốn xóa khách sạn này?"))
  {
    let data = new FormData();
    data.append('remove_hotel',id);

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/hotels.php",true);

    xhr.onload = function(){
      if(this.responseText == 1){
        alert('success','Đã xóa khách sạn!');
        get_all_hotels();
      }
      else{
        alert('error','Xóa thất bại!');
      }
    }

    xhr.send(data);
  }
}

window.onload = function(){
  get_all_hotels();
}
