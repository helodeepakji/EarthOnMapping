<?php

$current_page = 'create-project';
include 'settings/config/config.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

?>
<?php 
  $title = 'Create Project || EOM ';
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
        <?php

          if (isset($_GET['edit'])) {
            $project_id = base64_decode($_GET['edit']);
            $sqll = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
            $sqll->execute([$project_id]);
            $project = $sqll->fetch(PDO::FETCH_ASSOC);

        ?>
        <form id="productForm">
            <div class="container">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="fw-bold">Create Project</p>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="project_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required </p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Project <span style="color:red">*</span></label>
                  <input type="text" name="project_name" class="form-control" value="<?php echo $project['project_name'] ?>" required>
                  <input type="hidden" name="type" value="updateProject">
                  <input type="hidden" name="product_id" value="<?php echo $project_id ?>">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Summary <span style="color:red">*</span></label>
                  <input type="text" name="summary" class="form-control " value="<?php echo $project['summary'] ?>" required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Description <span style="color:red">*</span></label>
                  <input type="text" name="description" value="<?php echo $project['description'] ?>" class="form-control " required>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Original Estimation <span style="color:red">*</span></label>
                  <input type="text" name="original_estimation" class="form-control " value="<?php echo $project['original_estimation'] ?>" required>
                </div>
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity <span style="color:red">*</span></label>

                  <select class="form-control" name="complexity" required>
                    <option value="" disabled>Choose Complexity </option>
                    <option value="higher" <?php if($project['complexity'] == 'higher'){ echo 'selected'; } ?>>Higher</option>
                    <option value="medium" <?php if($project['complexity'] == 'medium'){ echo 'selected'; } ?>>Medium</option>
                    <option value="lower" <?php if($project['complexity'] == 'lower'){ echo 'selected'; } ?>>Lower</option>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Vector Project <span style="color:red">*</span></label>
                  <div class="input-group">
                    <label for="html" style="width:80px">Yes <input type="radio" id="html" name="vector" value="1" <?php if($project['vector'] == 1) { echo 'checked'; } ?>> </label> <br>
                    <label for="css">No
                    <input type="radio" id="css" name="vector" value="0" <?php if($project['vector'] == 0) { echo 'checked'; } ?>></label><br>
                  </div>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Attachment </label>
                  <div class="input-group">
                    <input type="file" class="form-control Attachment-input" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="attachment">
                  </div>
                </div>
                <div class="d-flex input-box no-margin ">
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">Start date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="datepicker" placeholder="Select a date" name="start_date" value="<?php echo $project['start_date'] ?>" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">End date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="datepicker1" placeholder="Select a date" name="end_date" value="<?php echo $project['end_date'] ?>" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-flex input-box">
                <label for="Summary" class=" me-2 control-label">Estimated Hour </label>
                <div class="input-group">
                  <input type="number" class="form-control overflow" value="<?php echo $project['estimated_hour'] ?>" name="estimated_hour" id="estimated_hour" required>
                </div>
              </div>

              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <button type="submit" class="btn btn-primary create-btn ">Update</button>
              </div>
            </div>
          </form>
        <?php
          }else{
        ?>
          <form id="productForm">
            <div class="container">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="fw-bold">Create Project</p>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="project_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Project <span style="color:red">*</span></label>
                  <input type="text" name="project_name" class="form-control" required>
                  <input type="hidden" name="type" value="addProject">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Summary <span style="color:red">*</span></label>
                  <input type="text" name="summary" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Description <span style="color:red">*</span></label>
                  <input type="text" name="description" class="form-control " required>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Original Estimation <span style="color:red">*</span></label>
                  <input type="text" name="original_estimation" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity <span style="color:red">*</span></label>

                  <select class="form-control" name="complexity" required>
                    <option value="" disabled>Choose Complexity</option>
                    <option value="higher">Higher</option>
                    <option value="medium">Medium</option>
                    <option value="lower">Lower</option>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Vector Project <span style="color:red">*</span></label>
                  <div class="input-group">
                    <label for="html" style="width:80px">Yes <input type="radio" id="html" name="vector" value="1"> </label> <br>
                    <label for="css">No
                    <input type="radio" id="css" name="vector" value="0"></label><br>
                  </div>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Attachment </label>
                  <div class="input-group">
                    <input type="file" class="form-control Attachment-input" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="attachment">
                  </div>
                </div>
                <div class="d-flex input-box no-margin ">
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">Start date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="datepicker" placeholder="Select a date" name="start_date" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">End date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="datepicker1" placeholder="Select a date" name="end_date" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-flex input-box">
                      <label for="Summary" class=" me-2 control-label">Estimated Hour </label>
                      <div class="input-group">
                        <input type="number" class="form-control overflow" name="estimated_hour" id="estimated_hour" required>
                      </div>
                    </div>

              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <button type="submit" class="btn btn-primary create-btn ">Create</button>
              </div>
            </div>
          </form>
          <?php } ?>
        </div>
      </div>
    </div>
  </main>

  <?php include 'settings/footer.php' ?>
  <script>
      var start_date_default = '<?php echo date('m/d/Y', strtotime($project['start_date'])); ?>';
      var end_date_default = '<?php echo date('m/d/Y', strtotime($project['end_date'])); ?>';
      if(start_date_default == '01/01/1970'){
        let date = new Date();
        start_date_default = date.toISOString();
      }
      if(end_date_default == '01/01/1970'){
        let date = new Date();
        end_date_default = date.toISOString();
      }
        // let date = new Date();
        // var dd = String(date.getDate()).padStart(2, '0');
        // var mm = String(date.getMonth() + 1).padStart(2, '0');
        // var yyyy = date.getFullYear();
        // let today = mm + '/' + dd + '/' + yyyy;
        // console.log('today is', today);

        $('#datepicker').dateDropper({
            format: 'Y/m/d',
            large: true,
            largeDefault: true,
            largeOnly: true,
            theme: 'datetheme' ,
            defaultDate: start_date_default
        });
        $('#datepicker1').dateDropper({
            format: 'Y/m/d',
            large: true,
            largeDefault: true,
            largeOnly: true,
            theme: 'datetheme',
            defaultDate: end_date_default
        });
  </script>
  <script>
    var notyf = new Notyf({
      position: {
        x: 'right',
        y: 'top'
      }
    });

    function diffDate(start_date, end_date) {
        const date1 = new Date(start_date);
        const date2 = new Date(end_date);
        const differenceInMilliseconds = date2 - date1;
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



    $('#productForm').submit(function(event) {
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: 'settings/api/projectApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
          notyf.success(response.message);
          setTimeout(() => {
            window.location = 'project.php';
          }, 1000);

        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    });
  </script>

</body>

</html>