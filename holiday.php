<?php

$current_page = 'holiday';

session_start();
include 'settings/config/config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
  $user_id = $_SESSION['userId'];
}

?>
<?php
if (isset($_POST['upload'])) {
  $tempFile = $_FILES["csvFile"]["tmp_name"];
  $targetFile = "upload/holidayimages/" . basename($_FILES["csvFile"]["name"]);
  if (move_uploaded_file($tempFile, $targetFile)) {

    // read from csv
    $csvFile = fopen($targetFile, "r");

    if (!$csvFile) {
      echo "unable to read the file";
    }

    $sl = 1;
    while ($data = fgetcsv($csvFile)) {

      if ($sl != 1) {
        $rawDate = $data[0];
        $date = date("Y-m-d", strtotime($rawDate));

        $holiday_name = $data[1];
        $sql = $conn->prepare("INSERT INTO `holiday`(`date`, `summary`) VALUES (? , ?)");
        $result = $sql->execute([$date, $holiday_name]);

      }
      $sl++;

      if ($sl >= 200) {
        break;
      }
    }


  } else {
    echo "failed";
  }
}


?>

<?php 
  $title = 'Holiday Lists || EOM ';
  include 'settings/header.php' 
?>

  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit Holiday</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">  
            <form id="updateHoliday">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Date</label>
                    <input type="text" class="form-control" name="date" id="date" required>
                    <input type="hidden" class="form-control" name="type" value="updateHoliday" required>
                    <input type="hidden" id="holiday_id" name="holiday_id" value="<?php echo $task_id ?>" required>
                  </div>
                </div>              
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Summary</label>
                    <input type="text" id="summary" class="form-control" name="summary" required>
                  </div>
                </div>
              </div>
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" id="image"class="form-control">
                  </div>
                </div>
                <div class="col-12 col-sm-6 p-2">
                  <img src="" id="imgae" width="200px" alt="">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
      </div>
    </div>
    </div>
  </div>

  <main style="margin-top:100px">
    <div class="login-form-bg ">
      <div class="container m-5 ">
        <div class="row justify-content-center ">
          <div class="col-xl-6">
            <div class="form-input-content">
              <div class="card login-form mb-0">
                <div class="card-body pt-5 shadow">
                  <h4 class="text-center">Add Holidays</h4>
                  <form enctype="multipart/form-data" method="post">
                    <div class="form-group">
                      <input type="file" class="form-control" name="csvFile" id="">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block m-2" name="upload">Upload</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row justify-content-center ">
          <div class="col-xl-12">
            <table class="table table-bordered mt-4">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Summary</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $sql = $conn->prepare("SELECT `id`,`date`, `summary` FROM `holiday`");
                $sql->execute();
                $holidays = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach ($holidays as $holiday) {
                  echo '<tr id="row_'.$holiday['id'].'">';
                  echo "<td>" . date('M j, D',strtotime($holiday['date'])) . "</td>";
                  echo "<td>" . $holiday['summary'] . "</td>";
                  echo '<td>
                  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getHoilday('.$holiday['id'].')">Edit</button>
                  <button class="btn btn-danger" onclick="deleteHoilday('.$holiday['id'].')">Delete</button>
                  </td>';
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php include 'settings/footer.php' ?>
</body>

</html>
<script>
    var notyf = new Notyf({
        position: {
          x: 'right',
          y: 'top'
        }
      });

      function getHoilday(id){
        $.ajax({
            url: "settings/api/holidayApi.php?type=getHoliday",
            data: { id: id },
            dataType: "json",
            success: function(response) {
              console.log(response);
              $('#date').val(response.date);
              $("#holiday_id").val(response.id);
              $("#summary").val(response.summary);
              $("#imgae").attr("src","images/holiday/"+response.image);
            }
          });
      }

      function deleteHoilday(id) {
          if (confirm("Want to delete this holiday?")) {
              $.ajax({
                  type: "POST",
                  url: "settings/api/holidayApi.php?type=deleteHoliday",
                  data: { id: id },
                  dataType: "json",
                  success: function(response) {
                      if (response.success) {
                        notyf.success(response.message);
                        $('#row_'+id).remove();
                      } else {
                        notyf.success("Failed to delete holiday.");
                      }
                  },
                  error: function() {
                      alert("An error occurred while processing the request.");
                  }
              });
          }
      }

      $('#updateHoliday').submit(function(){
        event.preventDefault();
				var formData = new FormData(this);
				$.ajax({
					url: 'settings/api/holidayApi.php',
					type: 'POST',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function (response) {
						location.reload();
					},
					error: function(xhr, status, error) {
						var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
						notyf.error(errorMessage);
					}
				});
      });

</script>