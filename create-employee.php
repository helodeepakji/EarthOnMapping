<?php

$current_page = 'create-employee';
include "settings/config/config.php";

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

?>


<?php 
  $title = 'Create Employee || EOM ';
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
              if(isset($_GET['edit'])){
                $id = $_GET['edit'];
                $id = base64_decode($id);
                $sql = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $sql->execute([$id]);
                $result = $sql->fetch(PDO::FETCH_ASSOC);
                if(!$result){
                    echo '<script>alert("user not found")</script>';
                }
          ?>
          <form id="employeeForm">
            <div class="container">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="fw-bold">Create Employee</p>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="employee_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">First Name <span style="color:red">*</span></label>
                  <input type="text" name="first_name" class="form-control" value="<?php echo $result['first_name'] ?>" required>
                  <input type="hidden" name="type" value="updatedata">
                  <input type="hidden" name="id" value="<?php echo $result['id'] ?>">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Last Name <span style="color:red">*</span></label>
                  <input type="text" name="last_name" value="<?php echo $result['last_name'] ?>" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Address <span style="color:red">*</span></label>
                  <input type="text" name="address" value="<?php echo $result['address'] ?>" class="form-control " required>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Date of Birth <span style="color:red">*</span></label>
                  <input type="date" value="<?php echo $result['dob'] ?>"  name="dob" class="form-control " required>
                </div>
                
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Employee Id <span style="color:red">*</span></label>
                  <input type="text" value="<?php echo $result['employee_id'] ?>"  name="employee_id" class="form-control " required>
                </div>

                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Phone <span style="color:red">*</span></label>
                  <input type="number" name="phone" id="phone" value="<?php echo $result['phone'] ?>" class="form-control " required>
                </div>

              
              <!-- <hr class="bg-dark" />
              <div class="d-flex input-box">
                <label for="Summary" class=" me-2 control-label">User Type <span style="color:red">*</span></label>
                <select class="form-control "  name="user_type" >
                  <option value="teamleader" <?php echo $result['user_type'] == 'teamleader' ? 'selected' : '' ?>>Team Leader</option>
                  <option value="user" <?php echo $result['user_type'] == 'user' ? 'selected' : '' ?>>User</option>
                </select>
              </div> -->
                
                
              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <a onclick="resetPassword(<?php echo $result['id'] ?>)" class="btn btn-danger create-btn m-2">Reset Password</a>
                <button type="submit" class="btn btn-primary create-btn m-2">Update</button>
              </div>
            </div>
          </form>
          <?php

              }else{
          ?>
          <form id="employeeForm">
            <div class="container">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="fw-bold">Create Employee</p>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="employee_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">First Name <span style="color:red">*</span></label>
                  <input type="text" name="first_name" class="form-control" required>
                  <input type="hidden" name="type" value="insertdata">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Last Name <span style="color:red">*</span></label>
                  <input type="text" name="last_name" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Address <span style="color:red">*</span></label>
                  <input type="text" name="address" class="form-control " required>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Employee Id <span style="color:red">*</span></label>
                  <input type="text" name="employee_id" class="form-control " required>
                </div>

                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Date of Birth <span style="color:red">*</span></label>
                  <input type="date" name="dob" class="form-control " required>
                </div>
                
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Phone <span style="color:red">*</span></label>
                  <input type="number" name="phone" class="form-control" id="phone" required>
                  <input type="hidden" name="password" class="form-control" value="Default" required readonly>
                </div>

                <!-- <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Password <span style="color:red">*</span></label>
                </div>
                <hr> -->
                <!-- <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">User Type <span style="color:red">*</span></label>
                  <select class="form-control "  name="user_type" >
                    <option value="user">User</option>
                    <option value="teamleader">Team Leader</option>
                  </select>
                </div> -->

                
                
              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <button type="submit" class="btn btn-primary create-btn ">Create</button>
              </div>
            </div>
          </form>
          <?php
              }
          ?>
        </div>
      </div>
    </div>
  </main>

  <?php include 'settings/footer.php' ?>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
  
  <script>
    var notyf = new Notyf({
      position: {
        x: 'right',
        y: 'top'
      }
    });

    $('#employeeForm').submit(function(event) {
      event.preventDefault();
      var formData = new FormData(this);
      var phone = $('#phone').val();
      if(phone.length == 10){
        $.ajax({
          url: 'settings/api/userApi.php',
          type: 'POST',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(response) {
            notyf.success(response.message);
            setTimeout(function() {
              window.location.href = "employee-list.php";
            }, 1000);
          },
          error: function(xhr, status, error) {
            var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
            notyf.error(errorMessage);
          }
        });
      }else{
        notyf.error("Phone no must be 10 digits");
      }
    });


    function resetPassword(id){
      $.ajax({
          url: 'settings/api/userApi.php',
          type: 'POST',
          data: {
            type : 'resetPassword',
            user_id : id
          },
          dataType: 'json',
          success: function(response) {
            notyf.success(response.message);
            setTimeout(function() {
              window.location.href = "employee-list.php";
            }, 1000);
          },
          error: function(xhr, status, error) {
            var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
            notyf.error(errorMessage);
          }
        });
    }

  </script>

</body>

</html>