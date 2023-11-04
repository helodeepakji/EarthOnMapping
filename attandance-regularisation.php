<?php

$current_page = 'attandance-regularisation';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';
$attendances = $conn->prepare("SELECT * FROM `attendence` WHERE `regularisation` = 1");
$attendances->execute();
$attendances = $attendances->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$title = 'Attendance Employee || EOM ';
include 'settings/header.php'
  ?>

<div class="modal" id="myModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Attendance</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="getAttendance" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Start Date </label>
              <div class="input-group">
                <input type="hidden" value="getMonth" name="type">
                <input type="date" class="form-control" id="startDate" name="startDate" required>
              </div>
            </div>
            <label for="project" class=" me-2 control-label p-2 ">Start Date </label>
            <div class="input-group">
              <input type="date" class="form-control" id="endDate" name="endDate" required>
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Download</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<main style="margin-top: 100px;">
  <div class="container pt-5">
    <div class="container">
      <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
        <p class="fw-bold">Attendance Regularisation</p>
        <a data-bs-toggle="modal" style="margin:0 20px" href="#myModal" class="btn btn-primary">Attendance</a>
      </div>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Date</th>
            <th scope="col">Time</th>
            <th scope="col">Remarks</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($attendances as $attendance) {
            $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $user->execute([$attendance['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            echo '
                    <tr id="row_' . $attendance['id'] . '">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['first_name'] . '</td>
                      <td>' . $user['last_name'] . '</</td>
                      <td>' . $attendance['date'] . '</td>
                      <td>' . date("h:i A", strtotime($attendance['clock_in_time'])) . '<br><span style="color:red">' . date("h:i A", strtotime($attendance['clock_out_time'])) . '</span></td>
                      <td>' . $attendance['remarks'] . '</td>
                      <td> <a class="btn btn-primary" style="margin:0 10px" onclick="approveAttendance(' . $attendance['id'] . ')">Approve</a></td>
                    </tr>
                  ';
            $i++;
          }

          ?>
        </tbody>
      </table>
    </div>
</main>


<?php include 'settings/footer.php' ?>
<script>

  var notyf = new Notyf({
    position: {
      x: 'right',
      y: 'top'
    }
  });

  function approveAttendance(id) {
    $.ajax({
      type: 'POST',
      url: 'settings/api/attendanceApi.php',
      data: {
        type: 'approveAttendance',
        id: id
      },
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        $('#row_' + id).remove();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  $('#getAttendance').submit(function (e) {
    e.preventDefault();
    var startDate = $('#startDate').val();
    var endDate = $('#endDate').val();
    $.ajax({
      url: 'settings/api/attendanceApi.php',
      type: 'GET',
      data: {
        startDate: startDate,
        endDate: endDate,
        type: 'getMonth'
      },
      dataType: 'json',
      success: function (response) {
        const extractedDataArray = [];
        var date = response.date
        date.push('Total Days ('+(date.length - 1)+')');
        const extractedData = date;
        extractedDataArray.push(extractedData);
        var attendance = response.attendance;
        attendance.forEach(element => {
          const countOfOnes = element.filter(item => item === '1').length;
          element.push(countOfOnes);
          extractedDataArray.push(element);
        });
        console.log(extractedDataArray);
        downloadExcel(extractedDataArray);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });


  function downloadExcel(data) {
    $.ajax({
      url: "settings/downloadExcel.php",
      type: 'POST',
      data: {
        data: data
      },
      xhrFields: {
        responseType: 'blob'
      },
      success: function (result) {
        var a = document.createElement('a');
        var url = window.URL.createObjectURL(result);
        a.href = url;
        a.download = "example.xlsx"; // Set the desired file name
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        notyf.success("Excel File Download SuccessFull");
      }
    });
  }

</script>

</body>

</html>