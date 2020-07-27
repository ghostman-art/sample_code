<?php
require_once('../all_users.php');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if ($action == 'getUser')
{
    if (isset($massiv2[$_GET['fio']]))
    {
        echo json_encode($massiv2[$_GET['fio']]); // возвраащем данные в JSON формате;
    }
    else
    {
        echo json_encode(array('область2'));
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	 <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Подача заявки</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/fontawesome/css/all.css">
    

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>


    <script type="text/javascript">
    // <![CDATA[
    function loadUsers(select)
    {
      var mobileSelect = $('select[name="contact_number_select"]');
          mobileSelect.attr('disabled', 'disabled'); // делаем список мобильных не активным
            // послыаем AJAX запрос, который вернёт список мобильников для выбранного ФИО
            $.getJSON('create_request.php', {action:'getUser', fio:select.value}, function(List){

                mobileSelect.html(''); // очищаем список мобильников
                
                // заполняем список городов новыми пришедшими данными
                $.each(List, function(i,v){
                  // i - имя пользователя, v - массив содержащий почту и телефон (первый элемент почта, второй телефон)
                  if(v[1] != null) //если телефон найден
                  {
                  	var str = String(v[1]);
                  	var short = str.substr(0, 12);
                  	mobileSelect.append('<option value="' + short  + '">' + short  + '</option>');
                  }
                  else{
                  	v[1] = "";
                  	mobileSelect.append('<option value="' +null+'">' + v[1] + '</option>');
                  }
                });
                
                mobileSelect.removeAttr('disabled'); // делаем список городов активным
                
              });
          }
    // ]]>
  </script>

    <style type="text/css">
      div.plus-truck{
        text-align: right;
      }
      div.minus-truck{
        text-align: right;
        display: none;
      }
      div.hide-show-truck{
        display: none;
      }
      div.plus-specialvehicle{
        text-align: right;
      }
      div.minus-specialvehicle{
        text-align: right;
        display: none;
      }
      div.hide-show-specialvehicle{
        display: none;
      }
      div.hide_show_input_trucks{
        display: none;
      }
      div.hide_show_input_specialvehicles{
        display: none;
      }
      div.hide_show_input_fio{
      	display: none;
      }
      div.hide_show_input_phone_fio{
      	display: none;
      }
    </style>

</head>
<body>
<br><br><br>
<?php include "../content/navbar_menu.php"; 
//include "ldap.php";
?>

<!-- метод POST -->
<?php
require_once 'connection.php';
$link = mysqli_connect($host, $user, $password, $database) or die ("Ошибка ". mysqli_error($link));

if(isset($_POST['save']))
{
   if(!empty($_POST['datetime_submission']) && !empty($_POST['delivery_address']) && !empty($_POST['description_goods']) && !empty($_POST['overall_dimensions']) && !empty($_POST['total_weight']) && !empty($_POST['author_request']) && !empty($_POST['general_type_work']) && !empty($_POST['object']))
   {
      $today = date("d.m.Y H:i:s");
      $date_registration_request = date("Y-m-d H:i:s", strtotime($today));
      $datetime_submission = date("Y-m-d H:i:s", strtotime($_POST['datetime_submission']));
      $delivery_address = trim(htmlentities(mysqli_real_escape_string($link, $_POST['delivery_address'])));
      if(!empty($_POST['contact_person']))
      {
      	$contact_person = trim(htmlentities(mysqli_real_escape_string($link, $_POST['contact_person'])));
      	$contact_number = trim(htmlentities(mysqli_real_escape_string($link, $_POST['contact_number'])));
    	}
    	else if(!empty($_POST['contact_person_select']))
    	{
    		$contact_person = trim(htmlentities(mysqli_real_escape_string($link, $_POST['contact_person_select'])));
    		$contact_number = trim(htmlentities(mysqli_real_escape_string($link, $_POST['contact_number_select'])));
    	}


      
      $description_goods = trim(htmlentities(mysqli_real_escape_string($link, $_POST['description_goods'])));
      $overall_dimensions = trim(htmlentities(mysqli_real_escape_string($link, $_POST['overall_dimensions'])));
      $total_weight = trim(htmlentities(mysqli_real_escape_string($link, $_POST['total_weight'])));
      if(!empty($_POST['comments']))
      {
         $comments = '"'. trim(htmlentities(mysqli_real_escape_string($link, $_POST['comments']))) .'"';
      }
      else
      {
         $comments = 'NULL';
      }
      $author_request = trim(htmlentities(mysqli_real_escape_string($link, $_POST['author_request'])));
      $email = trim(htmlentities(mysqli_real_escape_string($link, $_POST['email'])));

      $general_type_work = trim(htmlentities(mysqli_real_escape_string($link, $_POST['general_type_work'])));
      $id_object = trim(htmlentities(mysqli_real_escape_string($link, $_POST['object'])));

      //Обработка файлов
      if(isset($_FILES) && $_FILES['files']['error'][0] == 0)
      {
         $uploaddir = "/var/www/html/transport/trucksspecialvehicles/files/";

   //Количество добавляемых файлов
         $count_files = count($_FILES['files']['name']);
         $time_for_filename = date("dmyHi");

         for($i=0; $i<$count_files; $i++)
         {
            $filename_from_form = $_FILES['files']['name'][$i];
            $filename = $time_for_filename."_".$filename_from_form;
            $upload = $uploaddir . $filename;
            move_uploaded_file($_FILES['files']['tmp_name'][$i], $upload);
            $query_add_files = "INSERT INTO files (file_name) VALUES ('$filename')";
            $result_add_files = mysqli_query($link, $query_add_files) or die ("Ошибка записи в files ".mysqli_error($link));
         }

   //Запрос id из files для записи в таблицу result_referens_visits
         $query_files = "SELECT id FROM files ORDER BY id DESC LIMIT $count_files";
         $result_files = mysqli_query($link, $query_files) or die ("Ошибка запроса файлов ".mysqli_error($link));
         while ($data = mysqli_fetch_array($result_files)) 
         {
           $id_files[] = $data['id'];

        }

     }
     
     $query_new_request = "INSERT INTO requests (date_registration_request, status_request, datetime_submission, delivery_address, contact_person, contact_number, description_goods, overall_dimensions, total_weight, comments, author_request, email, general_type_work, id_object) VALUES ('$date_registration_request', 1, '$datetime_submission', '$delivery_address', '$contact_person', '$contact_number', '$description_goods', '$overall_dimensions', '$total_weight', $comments, '$author_request', '$email', '$general_type_work', '$id_object')";
     $result_new_request = mysqli_query($link, $query_new_request) or die("Ошибка добавления новой заявки ". mysqli_error($link));

     //Запись в промежуточную таблицу id заявки и id файла(ов)
     //12 августа НАДО ПЕРЕДЕЛАТЬ ВЕСЬ ИФ
     if(isset($id_files) && !empty($id_files))
     {
      $query_last_request = "SELECT id FROM requests ORDER BY id DESC LIMIT 1";
      $result_last_request = mysqli_query($link, $query_last_request);
      while ($data = mysqli_fetch_array($result_last_request)) 
      {
         $id_request = $data['id'];
      }

//переворачиваем массив чтоб id файлов были по возрастанию.
      if(count($id_files) > 1)
      {
         $id_files_reverse = array_reverse($id_files);
      }
      else
      {
         $id_files_reverse = $id_files;
      }
//echo "<br> Количество файлов = ".count($id_files_reverse);
      for($i=0; $i<count($id_files_reverse); $i++)
      {

         $id_file = $id_files_reverse[$i];

         $query_insert_idrequest_idfile = "INSERT INTO idrequest_idfile VALUES(NULL, '$id_request', '$id_file')";
         $result_insert_idrequest_idfile = mysqli_query($link, $query_insert_idrequest_idfile);
      }
     }

   $query_last_request = "SELECT id FROM requests ORDER BY id DESC LIMIT 1";
   $result_last_request = mysqli_query($link, $query_last_request);
   $row = mysqli_fetch_row($result_last_request);
   $id_last_request = $row[0];

//Грузовая техника
   $count_trucks = count($_POST['capacity_vehicle']);
  for($i = 0; $i < $count_trucks; $i++)
  {
   if(!empty($_POST['capacity_vehicle'][$i]) && !empty($_POST['tent_board'][$i]) && !empty($_POST['loading_method'][$i]) && !empty($_POST['unloading_address'][$i]) && !empty($_POST['loading_time'][$i]))
   {
      if(!empty($_POST['type_vehicle_select'][$i]))
      {
        $type_vehicle = trim(htmlentities(mysqli_real_escape_string($link, $_POST['type_vehicle_select'][$i])));
      }
      else if(!empty($_POST['type_vehicle_input'][$i]))
      {
        $new_truck = trim(htmlentities(mysqli_real_escape_string($link, $_POST['type_vehicle_input'][$i])));
        $query_new_truck = "INSERT INTO trucks (type_vehicle) VALUES ('$new_truck')";
        $result_new_truck = mysqli_query($link, $query_new_truck);

        $query_last_truck = "SELECT * FROM trucks ORDER BY id DESC LIMIT 1";
        $result_last_truck = mysqli_query($link, $query_last_truck);
        while ($data = mysqli_fetch_array($result_last_truck)) 
        {
          $type_vehicle = $data['id'];
          //echo "<br><br><br>Мы в новой грузовой = ".$type_vehicle;
        }
      }
      
      $capacity_vehicle = trim(htmlentities(mysqli_real_escape_string($link, $_POST['capacity_vehicle'][$i])));
      $tent_board = trim(htmlentities(mysqli_real_escape_string($link, $_POST['tent_board'][$i])));
      $loading_method = trim(htmlentities(mysqli_real_escape_string($link, $_POST['loading_method'][$i])));
      $unloading_address = trim(htmlentities(mysqli_real_escape_string($link, $_POST['unloading_address'][$i])));
      $loading_time = trim(htmlentities(mysqli_real_escape_string($link, $_POST['loading_time'][$i])));
      $cargo_insurance = trim(htmlentities(mysqli_real_escape_string($link, $_POST['cargo_insurance'][$i])));
      $packaged_goods = trim(htmlentities(mysqli_real_escape_string($link, $_POST['packaged_goods'][$i])));

      $query_truck = "INSERT INTO trucks_requests (id_request, type_vehicle, capacity_vehicle, tent_board, loading_method, unloading_address, loading_time, cargo_insurance, packaged_goods) VALUES ('$id_last_request', '$type_vehicle', '$capacity_vehicle', '$tent_board', '$loading_method', '$unloading_address', '$loading_time', '$cargo_insurance', '$packaged_goods')";
      $result_truck = mysqli_query($link, $query_truck) or die ("Ошибка добавления truck ". mysqli_error($link));

   }//if trucks
 }

//Спецтехника
   $count_specialvehicle = count($_POST['characteristics_specialvehicle']);
   for($i = 0; $i<$count_specialvehicle; $i++)
   {
      if(!empty($_POST['characteristics_specialvehicle'][$i]) && !empty($_POST['capacity_specialvehicle'][$i]) && !empty($_POST['usage_time'][$i]))
      {
        if(!empty($_POST['specialvehicles_select'][$i]))
        {
          $type_vehicle_spv = trim(htmlentities(mysqli_real_escape_string($link, $_POST['specialvehicles_select'][$i])));
        }
        else if(!empty($_POST['specialvehicles_input'][$i]))
        {
          $new_specialvehicles = trim(htmlentities(mysqli_real_escape_string($link, $_POST['specialvehicles_input'][$i])));
          $query_new_specialvehicle = "INSERT INTO specialvehicles (type_vehicle) VALUES ('$new_specialvehicles')";
          $result_new_specialvehicle = mysqli_query($link, $query_new_specialvehicle);

          $query_last_specialvehicle = "SELECT * FROM specialvehicles ORDER BY id DESC LIMIT 1";
          $result_last_specialvehicle = mysqli_query($link, $query_last_specialvehicle);
          while ($data = mysqli_fetch_array($result_last_specialvehicle)) 
          {
            $type_vehicle_spv = $data['id'];
          }
        }
         
         $characteristics_specialvehicle = trim(htmlentities(mysqli_real_escape_string($link, $_POST['characteristics_specialvehicle'][$i])));
         $capacity_specialvehicle = trim(htmlentities(mysqli_real_escape_string($link, $_POST['capacity_specialvehicle'][$i])));
         //$type_work = trim(htmlentities(mysqli_real_escape_string($link, $_POST['type_work'][$i])));
         $usage_time = trim(htmlentities(mysqli_real_escape_string($link, $_POST['usage_time'][$i])));

         $query_specialvehicle = "INSERT INTO specialvehicles_requests (id_request, type_vehicle_spv, characteristics_vehicle_spv, capacity_vehicle, usage_time) VALUES ('$id_last_request', '$type_vehicle_spv', '$characteristics_specialvehicle', '$capacity_specialvehicle', '$usage_time')";
         $result_specialvehicle = mysqli_query($link, $query_specialvehicle) or die("Ошибка добавления спецтехники" . mysqli_error($link));
      }//if specialvehicle
   }//for

   if($result_new_request)
   {
   	$_SESSION['id_request'] = $id_last_request;
   	include "sendmail/sendmail_newrequest.php";
   	
      echo"<div id='myModalBox' class='modal fade'>
            <div class='modal-dialog'>
             <div class='modal-content'>
              <div class='modal-header'>
               <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
               <h4 class='modal-title'>Регистрация заявки</h4>
              </div>
         <!-- Основное содержимое модального окна -->
              <div class='modal-body'>
                Заявка успешно зарегистрирована!
              </div>
         <!-- Футер модального окна -->
              <div class='modal-footer'>
               <input type='button' class='btn btn-primary' value='OK' onClick='modalbutton()'>
              </div>
             </div>
            </div>
           </div>";

   }

   else{
      echo "<div id='myModalBox' class='modal fade'>
             <div class='modal-dialog'>
              <div class='modal-content'>
               <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
                 <h4 class='modal-title'>Регистрация заявки</h4>
               </div>
         <!-- Основное содержимое модального окна -->
               <div class='modal-body'>
                 Заявка не зарегистрирована! Проверьте корректность введенных данных.
               </div>
         <!-- Футер модального окна -->
               <div class='modal-footer'>
                <input type='button' class='btn btn-primary' value='OK' onClick='modalbutton_errors()'>
               </div>
              </div>
             </div>
            </div>";
}

   }
}//submit save

//Отмена изменений
else if(isset($_POST['back']))
{
    header('Location: /transport/index.php', true, 301);
    exit();
}

function ConstructionObjects()
{
  global $array_objects;
  foreach ($array_objects as $key => $value) {
    echo "<option value='$key'>$value</option>";
  }
}

function SpecialVehicle()
{
	/*global $link;
	$query = "SELECT * FROM specialvehicles";
	$result = mysqli_query ($link, $query) or die( "Ошибка выбора спецтехники");
	while ($data = mysqli_fetch_array($result)) 
	{
		$id = $data['id'];
		$type_vehicle = $data['type_vehicle'];
		echo "<option value='$id'>$type_vehicle</option>";
	}*/
	global $array_typevehicles;
	foreach ($array_typevehicles as $key => $value) {
		echo "<option value='$key'>$value</option>";
	}
}

function Trucks()
{
  global $array_trucks;
  foreach ($array_trucks as $key => $value) {
    echo "<option value='$key'>$value</option>";
  }
}
?>
<!--конец метода POST-->
<?php
$query_objects = "SELECT * FROM construction_objects";
$result_objects = mysqli_query($link, $query_objects) or die("Ошибка запроса объектов ". mysqli_error($link));
while ($data = mysqli_fetch_array($result_objects)) 
{
  $array_objects[$data['id']] = $data['objects'];
}

$query = "SELECT * FROM specialvehicles";
	$result = mysqli_query ($link, $query) or die( "Ошибка выбора спецтехники");
	while ($data = mysqli_fetch_array($result)) 
	{
		$array_typevehicles[$data['id']]=$data['type_vehicle'];

	}

$query_trucks = "SELECT * FROM trucks";
$result_trucks = mysqli_query($link, $query_trucks);
while ($data = mysqli_fetch_array($result_trucks)) 
{
  $array_trucks[$data['id']]=$data['type_vehicle'];
}
?>
<br>
<form action="create_request.php" method="post" enctype="multipart/form-data">

	<div class="container">
   	<div class="row">

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<?php
   				$today = date("d.m.Y H:i"); 
   				?>
   				<div class="form-group">
   					<div class="col-md-12">
   						<label class="col-md-6  control-label">Дата регистрации заявки</label>
   						<div class="col-md-6">
   							<input class="form-control" type="text"  name="date_registration_request" value="<?php echo $today ?>" disabled="disabled" /> 
   						</div>
   					</div>
   				</div>

   			</div>
   		</div>

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<div class="col-md-12">
   						<label class=" col-md-6 control-label">Статус заявки</label>
   						<div class="col-md-6">
   							<input class="form-control" type="text" value="Зарегистрирована" disabled="disabled" />
   						</div>
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Дата и время подачи техники</label>
   					<div class="col-md-9">
   						<input class="form-control" required name="datetime_submission" type="text" id="datetimepicker1" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Адрес подачи техники</label>
   					<div class="col-md-9">
   						<input class="form-control" required name="delivery_address" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-7">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class="col-md-5  control-label">Контактное лицо</label>
   					<div class="col-md-7">
							<div class="input-group">
								<div class="hide_show_select_fio" id="hide_show_select_fio">
									<select class="form-control" id="select_fio" name="contact_person_select" id="select_fullname" onchange="loadUsers(this)">
										<option></option>
										<?php
										foreach ($massiv2 as $fio => $List) 
										{
											echo '<option value="' . $fio . '">' . $fio . '</option>' . "\n";
										}
										?>
									</select>
								</div>
								<div class="hide_show_input_fio" id="hide_show_input_fio">
   								<input class="form-control" placeholder="Введите ФИО" type="text" name="contact_person" /> 
   							</div>
   							<span class="input-group-addon">
   								<input type="checkbox" title="Поставьте галочку для ввода ФИО, если его нет в списке" id="inp_sel_fio" name="check_fio">
   							</span>
							</div>
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-5">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-6 control-label">Контактный телефон</label>
   					<div class="col-md-6">
   						<div class="hide_show_select_phone_fio" id="hide_show_select_phone_fio">
   							<select class="form-control" name="contact_number_select" id="select_phone_fio" style="-webkit-appearance: none; -moz-appearance: none; appearance: none;">
   								<option selected></option>
   							</select>
   						</div>
   						<div class="hide_show_input_phone_fio" id="hide_show_input_phone_fio">
   							<input class="form-control" type="text" name="contact_number"/>
   						</div>
   					</div>
   				</div>
   			</div>
   		</div>

      <div class="col-md-12">
        <div class='form-horizontal'>
          <div class="form-group">
            <label class=" col-md-3 control-label">Вид работ</label>
            <div class="col-md-9">
              <input class="form-control" required name="general_type_work" type="text" />
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class='form-horizontal'>
          <div class="form-group">
            <label class=" col-md-3 control-label">Объект</label>
            <div class="col-md-9">
              <select class="form-control" required name="object">
                  <option selected></option>
                  <?php
                    ConstructionObjects();
                  ?>
                </select>
            </div>
          </div>
        </div>
      </div>

   		<div class="col-md-6">
   			<div class="form-group">
   				<div class="col-md-6">
   					<p style="text-align: right;"><u><b>Характеристики груза:</b></u></p>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Наименование груза</label>
   					<div class="col-md-9">
   						<input class="form-control" required name="description_goods" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-6 control-label">Гарабитные размеры</label>
   					<div class="col-md-6">
   						<input class="form-control" required name="overall_dimensions" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-6 control-label">Вес общий/каждой единицы </label>
   					<div class="col-md-6">
   						<input class="form-control" required name="total_weight" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class="form-horizontal">
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Комментарии к заявке</label>
   					<div class="col-md-9">
   						<input class="form-control" required placeholder="Прописать информацию о подъездных путях, наличии препятствий если такие имеются" name="comments" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

      <hr/>

      <div class="col-md-6">
        <div class="form-group">
         <div class="col-md-6">
          <p style="text-align: right;"><u><b>Грузовой транспорт:</b></u></p>
            <div class="plus-truck">
              <a id="plus_truck_on" href="#"><i class="far fa-plus-square"></i></a>
            </div>
            <div class="minus-truck">
               <a id="minus_truck_off" href="#"><i class="far fa-minus-square "></i></a>
            </div>
          </div>
        </div>
      </div>
      
  <div class="hide-show-truck">
   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Вид ТС</label>

   					<div class="col-md-9">
              <div class="input-group">
                <div class="hide_show_select_trucks" id="hide_show_select_trucks">
                  <select class="form-control" name="type_vehicle_select[]">
                    <option selected></option>
                    <?php Trucks(); ?>
                  </select>
                </div>
                <div class="hide_show_input_trucks" id="hide_show_input_trucks">
   					<input class="form-control" name="type_vehicle_input[]" type="text" />
                </div>
                <span class="input-group-addon">
                  <input type="checkbox" title="Поставьте галочку для ввода грузового транспорта" id="inp_sel_trucks" value="trucks" name="check_trucks">
                </span>
              </div>
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Грузоподъемность ТС</label>
   					<div class="col-md-9">
   						<input class="form-control" id="capacity_vehicle" name="capacity_vehicle[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-6 control-label">Тент / борт</label>
   					<div class="col-md-6">
   						<input class="form-control" id="tent_board" name="tent_board[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-6">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-6 control-label">Способ погрузки</label>
   					<div class="col-md-6">
   						<input class="form-control" id="loading_method" name="loading_method[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Адрес разгрузки/контактное лицо(ФИО, телефон)</label>
   					<div class="col-md-9">
   						<input class="form-control" id="unloading_address" name="unloading_address[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Время на загрузку ТМЦ</label>
   					<div class="col-md-9">
   						<input class="form-control" id="loading_time" name="loading_time[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

      <div class="col-md-6">
        <div class='form-horizontal'>
          <div class="form-group">
            <label class="col-md-6  control-label">Страхование груза</label>
            <div class="col-md-6">
              <select id="cargo_insurance" name="cargo_insurance[]" class="form-control">
                <option selected></option>
                <option value="Да">Да</option>
                <option value="Нет">Нет</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class='form-horizontal'>
          <div class="form-group">
            <label class=" col-md-6 control-label">Груз в упаковке</label>
            <div class="col-md-6">
              <select id="packaged_goods" name="packaged_goods[]" class="form-control">
                <option selected></option>
                <option value="Да">Да</option>
                <option value="Нет">Нет</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class='form-horizontal'>
          <div class="form-group">
            <span class="button-plus-truck btn btn-primary plus-dinamic-truck pull-right">Добавить грузовой транспорт</span>

          </div>
        </div>
      </div>
  </div> <!-- hide-show-trucks -->

    <hr/>

   		<div class="col-md-6">
   			<div class="form-group">
   				<div class="col-md-6">
   					<p style="text-align: right;"><u><b>Спецтехника:</b></u></p>
            <div class="plus-specialvehicle">
              <a id="plus_specialvehicle_on" href="#"><i class="far fa-plus-square"></i></a>
            </div>
            <div class="minus-specialvehicle">
              <a id="minus_specialvehicle_off" href="#"><i class="far fa-minus-square "></i></a>
            </div>
   				</div>
   			</div>
   		</div>

      <div class="hide-show-specialvehicle">

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Вид ТС</label>
   					<div class="col-md-9">
              <div class="input-group">
                <div class="hide_show_select_specialvehicles" id="hide_show_select_specialvehicles">
                  <select name="specialvehicles_select[]" class="form-control">
                    <option></option>
                    <?php SpecialVehicle(); ?>
                  </select>
                </div>
                <div class="hide_show_input_specialvehicles" id="hide_show_input_specialvehicles">
                  <input type="text" name="specialvehicles_input[]" class="form-control" />
                </div>
                <span class="input-group-addon">
                  <input type="checkbox" title="Поставьте галочку для ввода спецтехники" id="inp_sel_specialvehicles" value="specialvehicles" name="check_specialvehicles">
                </span>
              </div>
            </div>
   				</div>
   			</div>
   		</div>
      
   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Характеристика ТС</label>
   					<div class="col-md-9">
   						<input class="form-control" id="characteristics_specialvehicle" name="characteristics_specialvehicle[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Грузоподъемность ТС</label>
   					<div class="col-md-9">
   						<input class="form-control" id="capacity_specialvehicle" name="capacity_specialvehicle[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<!--<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Вид работы</label>
   					<div class="col-md-9">
   						<input class="form-control" id="type_work" name="type_work[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>-->

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<label class=" col-md-3 control-label">Время использования</label>
   					<div class="col-md-9">
   						<input class="form-control" id="usage_time" name="usage_time[]" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

   		<div class="col-md-12">
   			<div class='form-horizontal'>
   				<div class="form-group">
   					<span class="button-plus btn btn-primary plus pull-right">Добавить спецтехнику</span>

   				</div>
   			</div>
   		</div>

    </div> <!-- hide-show-specialvehicle -->

   		<div class="col-md-12">
   			<div class="form-horizontal">
   				<div class="form-group">
   					<label class="col-md-3 control-label">Автор заявки</label>
   					<div class="col-md-9">
   						<input class="form-control" name="author_request" value="<?php echo $_SESSION['fio']; ?>" type="text" />
   					</div>
   				</div>
   			</div>
   		</div>

         <div class="col-md-12 hidden">
            <div class="form-horizontal">
               <div class="form-group">
                  <label class="col-md-3 control-label">Email</label>
                  <div class="col-md-9">
                     <input class="form-control" name="email" value="<?php echo $_SESSION['mail']; ?>" type="text" />
                  </div>
               </div>
            </div>
         </div>

   		<div class="col-md-12">
   			<div class="form-horizontal">
   				<div class="form-group">
   					<label class="col-md-3 control-label">Вложить файл(ы):</label>
   					<div class="col-md-9">
   						<input class="form-control" type="file" name="files[]" multiple title="Выберите файлы со своего пк">
   					</div>
   				</div>
   			</div>
   		</div>



   	</div> <!-- row -->

			

  </div>

<div class="col-md-9">
    <div class="form-group">
        <div class="col-md-offset-5">
        <input id="bSubmit" class="btn btn-primary" type="submit" name="save" value="Сохранить" />
        <!--<input class="btn btn-default" type="submit" name="back" value="Назад" />-->
        <a class="btn btn-default" href="/transport/index.php" role="button">Назад</a> 
    </div>
    </div>
</div>

</form>

<!--<script src="bootstrap/dist/js/bootstrap.js"></script>-->
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js"></script>

<!--Скрипты для работы виджета выбора даты и времени -->
<!--<script src="bootstrap/dist/js/jquery-3.2.1.min.js"></script>-->
<script src="bootstrap/dist/js/moment-with-locales.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.js"></script>
<script src="bootstrap/dist/js/bootstrap-datetimepicker.js"></script>
<link rel="stylesheet" href="bootstrap/dist/css/bootstrap-datetimepicker.css">

<script type="text/javascript" src="bootstrap/dist/js/select2/select2.min.js"></script>
<link rel="stylesheet" href="bootstrap/dist/css/select2/select2-bootstrap.css">
<link rel="stylesheet" href="bootstrap/dist/css/select2.css">

  <script type="text/javascript">
 $(document).ready(function() { $("#select_fio").select2(); }); //Select с поиском
  </script>

<script type="text/javascript">
//Обработка чекбокса в поле контактное лицо
$(function(){
	$("#inp_sel_fio").on("click", function(){
		if($(this).is(":checked"))
		{
			$('div[id="hide_show_select_fio"]').hide();
			$('div[id="hide_show_input_fio"]').show();

			$('div[id="hide_show_select_phone_fio"]').hide();
			$('div[id="hide_show_input_phone_fio"]').show();
		}
		else
		{
			$('div[id="hide_show_select_fio"]').show();
			$('div[id="hide_show_input_fio"]').hide();

			$('div[id="hide_show_select_phone_fio"]').show();
			$('div[id="hide_show_input_phone_fio"]').hide();
		}
	})
});
</script>  

<script type="text/javascript">  //скрипт выбора даты и времени подачи техники
$(function () {
	$('#datetimepicker1').datetimepicker({
		locale: 'ru'
	});
});
</script>

<!--Динамические поля грузовой транспорт -->
<script type="text/javascript">
  k = 1;
  var array_trucks = [<?php foreach ($array_trucks as $value) {echo '"'.$value.'",';} ?>];
  jQuery('.plus-dinamic-truck').click(function(){
    ++k;
    jQuery('.button-plus-truck').before(
      '<div class="block-truck">'+
      '<hr/>'+

      '<div class="col-md-12">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-3 control-label">Вид ТС</label>'+

            '<div class="col-md-9">'+
              '<div class="input-group">'+
                '<div class="hide_show_select_trucks" id="hide_show_select_trucks'+k+'">'+
                  '<select class="form-control" name="type_vehicle_select[]" id="select_typevehicle_tr'+k+'">'+
                    '<option></option>'+
                  '</select>'+
                '</div>'+

                '<div class="hide_show_input_trucks" id="hide_show_input_trucks'+k+'">'+
                  '<input class="form-control" name="type_vehicle_input[]" type="text" />'+
                '</div>'+

                '<span class="input-group-addon">'+
                  '<input class="checkbox_trucks" type="checkbox" title="Поставьте галочку для ввода грузового транспорта" id="inp_sel_trucks'+k+'" value="'+k+'" name="check_trucks">'+
                '</span>'+

              '</div>'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-12">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-3 control-label">Грузоподъемность ТС</label>'+
            '<div class="col-md-9">'+
              '<input class="form-control" name="capacity_vehicle[]" type="text" />'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-6">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-6 control-label">Тент / борт</label>'+
            '<div class="col-md-6">'+
              '<input class="form-control" id="tent_board" name="tent_board[]" type="text" />'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-6">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-6 control-label">Способ погрузки</label>'+
            '<div class="col-md-6">'+
              '<input class="form-control" id="loading_method" name="loading_method[]" type="text" />'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-12">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-3 control-label">Адрес разгрузки/контактное лицо(ФИО, телефон)</label>'+
            '<div class="col-md-9">'+
              '<input class="form-control" id="unloading_address" name="unloading_address[]" type="text" />'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-12">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-3 control-label">Время на загрузку ТМЦ</label>'+
            '<div class="col-md-9">'+
              '<input class="form-control" id="loading_time" name="loading_time[]" type="text" />'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-6">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class="col-md-6  control-label">Страхование груза</label>'+
            '<div class="col-md-6">'+
              '<select id="cargo_insurance" name="cargo_insurance[]" class="form-control">'+
                '<option selected></option>'+
                '<option value="Да">Да</option>'+
                '<option value="Нет">Нет</option>'+
              '</select>'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-6">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<label class=" col-md-6 control-label">Груз в упаковке</label>'+
            '<div class="col-md-6">'+
              '<select id="packaged_goods" name="packaged_goods[]" class="form-control">'+
                '<option selected></option>'+
                '<option value="Да">Да</option>'+
                '<option value="Нет">Нет</option>'+
              '</select>'+
            '</div>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '<div class="col-md-12">'+
        '<div class="form-horizontal">'+
          '<div class="form-group">'+
            '<span class="btn btn-danger minus-truck pull-right">Удалить грузовой транспорт</span>'+
          '</div>'+
        '</div>'+
      '</div>'+

      '</div>' //block-truck
      );

for(var i = 0; i < array_trucks.length; i++)
{
  var a = i+1;
  $('#select_typevehicle_tr'+k+'').append('<option value="'+a+'">'+array_trucks[i]+'</option>');
}

});

// on - так как элемент динамически создан и обычный обработчик с ним не работает
jQuery(document).on('click', '.minus-truck', function(){
    jQuery( this ).closest( '.block-truck' ).remove(); // удаление строки с полями
    --k; 
  });

jQuery(document).on('change', '.checkbox_trucks', function(){
    var value_checkbox = $(this).val();
     if(this.checked) 
     { 
        $('div[id="hide_show_select_trucks'+value_checkbox+'"]').hide();
        $('div[id="hide_show_input_trucks'+value_checkbox+'"]').show();
    }
    else 
    {
        $('div[id="hide_show_select_trucks'+value_checkbox+'"]').show();
        $('div[id="hide_show_input_trucks'+value_checkbox+'"]').hide();
    }
});
</script>

<!--Динамические поля спецтехника -->
<script type="text/javascript">
	j = 1;
	var array_typevehicles = [<?php foreach ($array_typevehicles as $value) {echo '"'.$value.'",'; } ?>];
	jQuery('.plus').click(function(){
		++j;
		jQuery('.button-plus').before(
			
			'<div class="block">'+

      '<hr/>'+
   		'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<label class=" col-md-3 control-label">Вид ТС</label>'+
   					'<div class="col-md-9">'+
              '<div class="input-group">'+

                '<div class="hide_show_select_specialvehicles" id="hide_show_select_specialvehicles'+j+'">'+
   						     '<select name="specialvehicles_select[]" id="select_typevehicle'+j+'" class="form-control">'+
   							    '<option></option>'+
   						     '</select>'+
                '</div>'+

                '<div class="hide_show_input_specialvehicles" id="hide_show_input_specialvehicles'+j+'">'+
                  '<input type="text" name="specialvehicles_input[]" class="form-control" />'+
                '</div>'+

                '<span class="input-group-addon">'+
                  '<input class="checkbox_specialvehicles" type="checkbox" title="Поставьте галочку для ввода спецтехники" id="inp_sel_specialvehicles'+j+'" value="'+j+'" name="check_specialvehicles">'+
                '</span>'+

              '</div>'+
   					'</div>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+

   		'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<label class=" col-md-3 control-label">Характеристика ТС</label>'+
   					'<div class="col-md-9">'+
   						'<input class="form-control" name="characteristics_specialvehicle[]" type="text" />'+
   					'</div>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+

   		'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<label class=" col-md-3 control-label">Грузоподъемность ТС</label>'+
   					'<div class="col-md-9">'+
   						'<input class="form-control" name="capacity_specialvehicle[]" type="text" />'+
   					'</div>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+

   		/*'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<label class=" col-md-3 control-label">Вид работы</label>'+
   					'<div class="col-md-9">'+
   						'<input class="form-control" name="type_work[]" type="text" />'+
   					'</div>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+*/

   		'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<label class=" col-md-3 control-label">Время использования</label>'+
   					'<div class="col-md-9">'+
   						'<input class="form-control" name="usage_time[]" type="text" />'+
   					'</div>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+

   		'<div class="col-md-12">'+
   			'<div class="form-horizontal">'+
   				'<div class="form-group">'+
   					'<span class="btn btn-danger minus pull-right">Удалить спецтехнику</span>'+
   				'</div>'+
   			'</div>'+
   		'</div>'+

   		'</div>'//block

   		);
		
  	for(var i = 0; i<array_typevehicles.length; i++)
  	{
      var b = i+1;
			$('#select_typevehicle'+j+'').append( '<option value="'+b+'">'+array_typevehicles[i]+'</option>' );
  	}

	});

// on - так как элемент динамически создан и обычный обработчик с ним не работает
jQuery(document).on('click', '.minus', function(){
    jQuery( this ).closest( '.block' ).remove(); // удаление строки с полями
    --j; 
	});

jQuery(document).on('change', '.checkbox_specialvehicles', function(){
    var value_checkbox = $(this).val();
     if(this.checked) 
     { 
        $('div[id="hide_show_select_specialvehicles'+value_checkbox+'"]').hide();
        $('div[id="hide_show_input_specialvehicles'+value_checkbox+'"]').show();
    }
    else 
    {
        $('div[id="hide_show_select_specialvehicles'+value_checkbox+'"]').show();
        $('div[id="hide_show_input_specialvehicles'+value_checkbox+'"]').hide();
    }
});

</script>


<!-- Показываем/скрываем грузовой транспорт -->
<script type="text/javascript">
  $(function(){
    $("#plus_truck_on").on("click", function(){
      $('div[class="hide-show-truck"').show();
      $('div[class="plus-truck"]').hide();
      $('div[class="minus-truck"]').show();  

      $('input[id="capacity_vehicle"]').attr('required', "required");
      $('input[id="tent_board"]').attr('required', "required");
      $('input[id="loading_method"]').attr('required', "required");
      $('input[id="unloading_address"]').attr('required', "required");
      $('input[id="loading_time"]').attr('required', "required");
      $('select[id="cargo_insurance"]').attr('required', "required");
      $('select[id="packaged_goods"]').attr('required', "required");
    })

     $("#minus_truck_off").on("click", function(){
      $('div[class="hide-show-truck"').hide();
      $('div[class="plus-truck"]').show();
      $('div[class="minus-truck"]').hide(); 

      $('input[id="capacity_vehicle"]').removeAttr('required', "required");
      $('input[id="tent_board"]').removeAttr('required', "required");
      $('input[id="loading_method"]').removeAttr('required', "required");
      $('input[id="unloading_address"]').removeAttr('required', "required");
      $('input[id="loading_time"]').removeAttr('required', "required");
      $('select[id="cargo_insurance"]').removeAttr('required', "required");
      $('select[id="packaged_goods"]').removeAttr('required', "required");
    })
  });
</script>

<!-- Показываем/скрываем спецтехнику -->
<script type="text/javascript">
  $(function(){
    $("#plus_specialvehicle_on").on("click", function(){
      $('div[class="hide-show-specialvehicle"').show();
      $('div[class="plus-specialvehicle"]').hide();
      $('div[class="minus-specialvehicle"]').show();

      $('input[id="characteristics_specialvehicle"]').attr('required', "required");
      $('input[id="capacity_specialvehicle"]').attr('required', "required");
     // $('input[id="type_work"]').attr('required', "required");
      $('input[id="usage_time"]').attr('required', "required");
    })

     $("#minus_specialvehicle_off").on("click", function(){
      $('div[class="hide-show-specialvehicle"').hide();
      $('div[class="plus-specialvehicle"]').show();
      $('div[class="minus-specialvehicle"]').hide();

      $('input[id="characteristics_specialvehicle"]').removeAttr('required', "required");
      $('input[id="capacity_specialvehicle"]').removeAttr('required', "required");
      //$('input[id="type_work"]').removeAttr('required', "required");
      $('input[id="usage_time"]').removeAttr('required', "required");  
    })
  });
</script>

<script type="text/javascript">
  $(function() {
    $("#inp_sel_trucks").change(function()
    {
      if($(this).is(":checked"))
      {
        $('div[id="hide_show_input_trucks"]').show(); 
        $('div[id="hide_show_select_trucks"]').hide();
      }
      else 
      {
        $('div[id="hide_show_select_trucks"]').show(); 
        $('div[id="hide_show_input_trucks"]').hide();
      }
    })
  });
</script>

<script type="text/javascript">
  $(function() {
    $("#inp_sel_specialvehicles").change(function()
    {
      if($(this).is(":checked"))
      {
        $('div[id="hide_show_input_specialvehicles"]').show(); 
        $('div[id="hide_show_select_specialvehicles"]').hide();
      }
      else 
      {
        $('div[id="hide_show_select_specialvehicles"]').show(); 
        $('div[id="hide_show_input_specialvehicles"]').hide();
      }
    })
  });
</script>

<script>//для модально окна
$(document).ready(function() {
  $("#myModalBox").modal('show');
});
</script>

<script type="text/javascript"> //кнопка модального окна
   function modalbutton( ) { location.href = '/transport/index.php' }
</script>

<script type="text/javascript"> //кнопка модального окна
   function modalbutton_errors( ) { location.href = '/transport/trucksspecialvehicles/create_request.php' }
</script>

</body>
</html>