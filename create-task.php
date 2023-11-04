<?php
$current_page = 'create-task';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';
$sql = $conn->prepare('SELECT * FROM `projects`');
$sql->execute();
$projects = $sql->fetchAll(PDO::FETCH_ASSOC);

$sql3 = $conn->prepare("SELECT * FROM `users` WHERE `user_type` = 'user'");
$sql3->execute();
$users = $sql3->fetchAll(PDO::FETCH_ASSOC);

$optionProject = '';
foreach ($projects as $project) {
  $optionProject .= '<option value="' . $project['project_id'] . '">' . $project['project_name'] . '</option>';
}

?>

<body>
  <?php
  $title = 'Create Tasks || EOM ';
  include 'settings/header.php'
    ?>
  <style>
    .datepicker-container {
      background-color: #fff;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
      padding: 5px;
    }
  </style>


  <main style="margin-top: 58px;">
    <div class="container pt-4">
      <div class="project_issue">
        <div class="projec-task">
          <!-- <h1>Admin</h1> -->
          <div class="container">
            <div class="d-flex justify-content-between">

              <?php

              if (isset($_GET['edit'])) {

                $task_id = base64_decode($_GET['edit']);
                $sqll = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
                $sqll->execute([$task_id]);
                $task = $sqll->fetch(PDO::FETCH_ASSOC);

                ?>
                <div>
                  <p class="fw-bold">Update Task</p>
                </div>
              </div>
              <hr class="bg-dark">
              <div class="project_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                <form id="creatTaskForm">
                  <div class="d-flex input-box">
                    <label for="firstName" class=" me-2 control-label" id="Complexity">Project <span style="color:red">*</span></label>

                    <select class="form-control" name="project_id" id="project_id" required>
                      <option value="" disabled>Choose Project</option>
                      <?php
                      foreach ($projects as $project) {
                        $select = $task['project_id'] == $project['project_id'] ? 'selected' : '';
                        echo '<option value="' . $project['project_id'] . '" ' . $select . '>' . $project['project_name'] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="d-flex input-box">
                    <label for="Summary" class=" me-2 control-label">Task Id <span style="color:red">*</span></label>
                    <div class="input-group">
                      <input type="text" name="task_id" value="<?php echo $task['task_id'] ?>" class="form-control"
                      required readonly>
                      <input type="hidden" name="type" value="UpdateTask" required>
                    </div>
                  </div>
                  <div class="d-flex input-box">
                    <label for="Summary" class=" me-2 control-label">Estimated Hour <span style="color:red">*</span></label>
                    <div class="input-group">
                      <input type="number" class="form-control overflow" value="<?php echo $task['estimated_hour'] ?>"
                        name="estimated_hour" id="estimated_hour" required>
                    </div>
                  </div>
                  <div class="d-flex input-box">
                    <label for="Summary" class=" me-2 control-label">Area SQKM <span style="color:red">*</span></label>
                    <div class="input-group">
                      <input type="number" class="form-control overflow" value="<?php echo $task['area_sqkm'] ?>"
                        name="area_sqkm" id="area_sqkm" required>
                    </div>
                  </div>
                  <hr class="bg-dark">

                  <div class="d-flex input-box">
                    <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity </label>

                    <select class="form-control" name="complexity" required>
                      <option value="" disabled>Choose Complexity</option>
                      <option value="higher" <?php if ($task['complexity'] == 'higher')
                        echo 'selected' ?>>Higher</option>
                        <option value="medium" <?php if ($task['complexity'] == 'medium')
                        echo 'selected' ?>>Medium</option>
                        <option value="lower" <?php if ($task['complexity'] == 'lower')
                        echo 'selected' ?>>Lower</option>
                      </select>
                    </div>
                    <div class="d-flex input-box">
                      <label for="Summary" class=" me-2 control-label">Attachment </label>
                      <div class="input-group">
                        <input type="file" class="form-control Attachment-input overflow" id="inputGroupFile04"
                          aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="attachment">
                      </div>
                    </div>
                    <div class="d-flex input-box">
                      <label for="firstName" class=" me-2 control-label" id="Complexity">Status </label>

                      <input type="text" class="form-control" value="<?php echo $task['status'] ?>" name="status" readonly
                      required>
                  </div>
                  <div>
                    <div class="d-flex input-box no-margin">
                      <div class="col-6">
                        <div class="input-box">
                          <label for="" class="me-3">Start date. </label>
                          <input type="text" class="datepicker form-control Attachment-input" id="datepicker"
                            placeholder="Select a date" name="start_date" value="<?php echo $task['start_date'] ?>">

                        </div>
                      </div>
                      <div class="col-6">
                        <div class="input-box">
                          <label for="" class="me-3">End date </label>
                          <input type="text" class="datepicker form-control Attachment-input" id="datepicker1"
                            placeholder="Select a date" name="end_date" value="<?php echo $task['end_date'] ?>">
                        </div>
                      </div>
                    </div>
                  </div>
              </div>
              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <button type="submit" id="submit_form_btn" class="btn btn-primary create-btn ">Update</button>
              </div>
              </form>
              <?php

              } else {

                ?>
              <div>
                <p class="fw-bold">Create Task</p>
              </div>
              <!------ start modal ---------->
              <div class="first-modal">
                <a data-bs-toggle="modal" href="#myModal" class="btn btn-primary">CSV Upload</a>
                <div class="modal" id="myModal">
                  <div class="modal-dialog modal-md">
                    <div class="modal-content">
                      <div class="modal-header">
                        <!-- <h4 class="modal-title">Modal title</h4>     -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                      </div>
                      <form id="uploadCsv" name="upload">
                        <div class="modal-body">
                          <div class="mb-3">
                            <div class="mb-3">
                              <label for="project" class=" me-2 control-label p-2 ">Select Project </label>
                              <select class="form-select form-select" aria-label=".form-select-sm example"
                                name="project_id" id="project_id" required>
                                <option value="0" disabled>Choose Project</option>
                                <?php echo $optionProject ?>
                              </select>
                            </div>
                            <label for="fileInput" class="form-label file">Select a File</label>
                            <!-- <input type="file" class="form-control1 requied" id="fileInput"> -->
                            <div class="input-group">
                              <input type="file" class="form-control Attachment-input" id="Attachment_file"
                                aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="csvFile" required>
                            </div>


                          </div>

                        </div>
                        <div class="modal-footer">
                          <a href="#" data-bs-dismiss="modal" class="btn btn-outline-dark">Close</a>
                          <button id="modal2_btn" class="btn btn-primary">Upload file</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="modal " id="myModal2" data-bs-backdrop="static">
                  <div class="modal-dialog modal-xl myModal2">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">Task List</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                      </div>

                      <form id="updateTask" style="width:100%">
                        <div class="modal-body">
                          <table class="table p-5">
                            <thead>
                              <tr>
                                <th scope="col">Sno</th>
                                <th scope="col">Task id</th>
                                <th scope="col">Original Estimation</th>
                                <th scope="col">Area SQKM</th>
                                <th scope="col">Priority</th>
                                <th scope="col">Start date</th>
                                <th scope="col">End date</th>
                              </tr>
                            </thead>
                            <tbody id="TableBody">

                            </tbody>
                          </table>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-primary">Save changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>


              </div>
              <!------ end modal ---------->

            </div>
            <hr class="bg-dark">
            <div class="project_form">
              <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
              <form id="creatTaskForm">
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Project <span
                      style="color:red">*</span></label>

                  <select class="form-control" name="project_id" required>
                    <option value="1" disabled>Choose Project</option>
                    <?php echo $optionProject ?>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Task Id <span style="color:red">*</span></label>
                  <input type="text" name="task_id" class="form-control" required>
                  <input type="hidden" name="type" value="addTask" required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Original Estimated<span
                      style="color:red">*</span></label>
                  <div class="input-group">
                    <input type="number" class="form-control overflow" name="estimated_hour" id="estimated_hour" required>
                  </div>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Area SQKM<span style="color:red">*</span></label>
                  <div class="input-group">
                    <input type="number" class="form-control overflow" name="area_sqkm" required>
                  </div>
                </div>
                <hr class="bg-dark">
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity</label>

                  <select class="form-control" name="complexity" required>
                    <option value="lower">Lower</option>
                    <option value="medium">Medium</option>
                    <option value="higher">Higher</option>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Attachment </label>
                  <div class="input-group">
                    <input type="file" class="form-control Attachment-input overflow" id="inputGroupFile04"
                      aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="attachment">
                  </div>
                </div>
                <div>
                  <div class="d-flex input-box no-margin">
                    <div class="col-6">
                      <div class="input-box">
                        <label for="" class="me-3">Start date </label>
                        <input type="text" class="datepicker form-control Attachment-input" id="datepicker"
                          placeholder="Select a date" name="start_date">
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="input-box">
                        <label for="" class="me-3">End date</label>
                        <input type="text" class="datepicker form-control Attachment-input" id="datepicker1"
                          placeholder="Select a date" name="end_date">
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
              <button type="submit" class="btn btn-primary create-btn ">Create</button>
            </div>
            </form>

            <?php

              }

              ?>

        </div>

      </div>

    </div>


    </div>
    </div>
  </main>


  <?php include 'settings/footer.php' ?>

  <!-- end modal Library -->
  <script>
    // if(start_date_default == '08/24/2023'){
    //   let date = new Date();
    //   start_date_default = date.toISOString();
    // }
    // if(end_date_default == '08/26/2023'){
    //   let date = new Date();
    //   end_date_default = date.toISOString();
    // }

    // $('#datepicker').dateDropper({
    //   format: 'Y/m/d',
    //   large: true,
    //   largeDefault: true,
    //   largeOnly: true,
    //   theme: 'datetheme',
    //   // defaultDate: start_date_default
    // });

    // $('#datepicker1').dateDropper({
    //   format: 'Y/m/d',
    //   large: true,
    //   largeDefault: true,
    //   largeOnly: true,
    //   theme: 'datetheme',
    //   // defaultDate: end_date_default
    // });

    $('.datepicker').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });

  </script>


  <script>

    var flag = true;


    var notyf = new Notyf({ position: { x: 'right', y: 'top' } });


    function diffDate(start_date, end_date) {
      const date1 = new Date(start_date);
      const date2 = new Date(end_date);
      const differenceInMilliseconds = date2 - date1; // Reversed order for correct calculation
      const day = Math.floor(differenceInMilliseconds / (1000 * 60 * 60 * 24));
      return day;
    }

    $('#datepicker, #datepicker1').on('change', function () {
      var startDate = new Date($('#datepicker').val());
      var endDate = new Date($('#datepicker1').val());

      if (startDate && endDate) {
        if (startDate > endDate) {
          notyf.error('Start date cannot be greater than End date.');
          $('#datepicker1').val('');
        } else {
          const startDateStr = $('#datepicker').val();
          const endDateStr = $('#datepicker1').val();
          $('#estimated_hour').val(diffDate(startDateStr, endDateStr) * 8);
        }
      }
    });

    $('#estimated_hour').keyup(function () {
      var product_id = $('#project_id').val();
      var estimated_hour = $(this).val();
      if (product_id) {
        $.ajax({
          url: 'settings/api/projectApi.php',
          data: {
            type: 'totalEstimatedTime',
            project_id: product_id
          },
          success: function (response) {
            console.log(response);
            if (estimated_hour > response.ava_time) {
              flag = false;
              Notiflix.Confirm.show(
                'Confirmation',
                'Are you sure you want to proceed?',
                'Yes',
                'No',
                function () {
                  var flag = true;
                  $('#submit_form_btn').click();
                }
              );
            } else {
              flag = true;
            }
          }
        });
      } else {
        notyf.error("Select Project First");
      }
    });

    $('#modal2_btn').click(() => {
      if ($("#Attachment_file").val() !== "") {
        $("#myModal2").modal('show');
        $('#myModal').modal('hide');
      } else {
        notyf.error("Upload CSV File.");
      }
    })

    $('#creatTaskForm').submit(function (event) {
      event.preventDefault();
      var formData = new FormData(this);
      if (flag) {
        $.ajax({
          url: 'settings/api/taskApi.php',
          type: 'POST',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function (response) {
            notyf.success(response.message);
            window.location.href = "project.php";
          },
          error: function (xhr, status, error) {
            var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
            notyf.error(errorMessage);
          }
        });
      } else {
        notyf.error("Pls Estimated Hour Check");
      }
    });


    $(document).ready(function () {

      userProject();

      $('.select2').select2({
        dropdownParent: $('#myModal2')
      });

      $('.select2').select2();

      $('.select2-dropdown').addClass('form-control');

    });


    function userProject() {
      var html = '';
      $.ajax({
        url: 'settings/api/userApi.php',
        type: 'GET',
        data: {
          type: 'getAllUserData',
        },
        dataType: 'json',
        success: function (response) {
          var users = response.data;
          users.forEach(element => {
            html += '<option value="' + element.id + '">' + element.first_name + ' ' + element.last_name + '</option>';
          });
          $('.userOption').append(html);
          $('.select2-dropdown').addClass('form-control');
        }
      });
    }


    $('#updateTask').submit(function (event) {
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: 'settings/api/taskApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          console.log(response);
          notyf.success(response.message);
          $('#myModal2').modal('hide');
          setTimeout(function () {
            window.location.href = "project.php";
          }, 1000);
        }
      });
    });


    $('#uploadCsv').submit(function (event) {
      event.preventDefault();
	Notiflix.Loading.standard();
      var formData = new FormData(this);
      $.ajax({
        url: 'settings/api/csvTaskApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
	
          var list = 0;
          var rlist = 0;
          $('#myModal').modal('hide');
          console.log(response);
          $('#TableBody').html('');
          var data = response.data;
          var project_id = response.project_id;
          data.forEach(element => {
            list++ ;
            var html = '<tr id="data_id_' + element[0] + '"><th scope="row">' +  list + '</th><th scope="row">' + element[0] + '</th><td class="text-data">' + element[1] + '</td><td class="text-data">' + element[2] + '</td><td class="select-box"><select class="form-select form-select-sm select-data p-2" aria-label=".form-select-sm example" name="complexity[]" id="complexity_' + element[0] + '" required> <option value="lower">Lower</option> <option value="medium">Medium</option> <option value="higher">Higher</option></select>  </td> <td class="select-box"> <div class="input-group select-data border p-1"> <input type="text" class="form-control Attachment-input datepicker" name="start_date[]" placeholder="Select a date" > </div> </td> <td class="select-box"> <div class="input-group select-data border p-1"> <input type="text" name="end_date[]" class="form-control Attachment-input datepicker"  placeholder="Select a date"> </div> </td> <input type="hidden" name="task_id[]" value="' + element[0] + '" ><input type="hidden" name="type" value="updateTask" ><input type="hidden" name="project_id" value="' + project_id + '" ></tr>';

            $('#TableBody').append(html);

            $('.datepicker').dateDropper({
              format: 'Y/m/d',
              large: true,
              largeDefault: true,
              largeOnly: true,
              theme: 'datetheme'
            });
		Notiflix.Loading.remove();
          });

          var error = response.task_id;
          error.forEach(element => {
            rlist++;
            $('#data_id_' + element).remove();
            notyf.error('This Task Id ' + element + ' already exist');
          });

          if (rlist == list) {
            $('#myModal2').modal('hide');
          }

        },
        error: function (xhr, status, error) {
          $('#myModal2').modal('hide');
          if (xhr.status === 500) {
              const errorResponse = JSON.parse(xhr.responseText);
              notyf.error(errorResponse.message);
          } else {
              var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
              notyf.error(errorMessage);
          }
        }
      });
    });



  </script>



</body>

</html>