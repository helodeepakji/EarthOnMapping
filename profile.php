<?php
$current_page = 'profile';
include 'settings/config/config.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userId = $_SESSION['userId'];
}
$sql = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
$sql->execute([$userId]);
$result = $sql->fetch(PDO::FETCH_ASSOC);
?>


<?php
$title = 'Profile || EOM ';
include 'settings/header.php'
  ?>

<style>
  .container {
    background-color: #ffffff;
  }
</style>

<main style="margin-top: 100px;">
  <div class="container ">
    <section style="background-color: #eee;">
      <div class="container py-5">


        <div class="row">
          <div class="col-lg-4">
            <div class="card mb-4">
              <div class="card-body text-center">

                <label for="profile">
                  <?php
                  if (($result['profile'] == '') || !($result['profile'])) {
                    echo '
                        <img id="profile_image" src="images/profile.webp" alt="avatar"
                      class="rounded-circle img-fluid" style="width: 150px;">
                      ';
                  } else {
                    echo '
                      <img id="profile_image" src="images/users/' . $result['profile'] . '" alt="avatar"
                      class="rounded-circle img-fluid" style="width: 150px;">
                      ';
                  }
                  ?>
                  <i class="fas fa-edit" style="position: absolute;"></i>
                </label>
                <input type="file" name="profile" id="profile" style="display:none" accept="image/*">
                <h5 class="my-3">
                  <?php echo $result['first_name'] . ' ' . $result['last_name'] ?>
                </h5>
              </div>
            </div>
            <div class="card col-md-12">
              <div class="card-body">
                <h3 class="d-flex" style="font-size: 20px">Change Password</h3>
                <hr>
                <form id="changePassword">
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" class="form-control" name="old_password" required>
                        <input type="hidden" class="form-control" name="type" value="changePassword" required>
                      </div>
                    </div>
                  </div>
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control" name="password" required="">
                      </div>
                    </div>
                  </div>
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" name="cpassword" required="">
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Change Password</button>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <!-- <div class="d-flex justify-content-around">
                  <p>Designation: </p>
                  <p>Department: </p>
                  <p>EMP ID: </p>
              </div>
              <div class="d-flex justify-content-around">
                  <p>D-Name </p>
                  <p>Dept_Name </p>
                  <p>Id </p>
              </div> -->
            <div class="card mb-4 name-card">
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Full Name:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['first_name'] . ' ' . $result['last_name'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Phone:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['phone'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Address:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['address'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Designation:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">Johnatan Smith</p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Department:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">Johnatan Smith</p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">EMP ID:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">#
                      <?php echo $result['employee_id'] ?>
                    </p>
                  </div>
                </div>

              </div>
            </div>
            <div class="row project">
              <!-- <div class="col-md-6">
                <div class="card mb-4 mb-md-0">
                  <div class="card-body"> -->
              <!-- <h1 class="d-flex justify-content-center">Timeline</h1>
                      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quidem quis cumque eius provident quasi suscipit debitis sint nostrum nobis, pariatur commodi eaque eveniet numquam rerum autem distinctio ipsam voluptatum maxime.</p> -->
              <!-- <p class="mb-4"><span class="text-primary font-italic me-1">assigment</span> Project Status
                    </p>
                    <p class="mb-1" style="font-size: .77rem;">Web Design</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Website Markup</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">One Page</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 89%" aria-valuenow="89"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Mobile Template</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 55%" aria-valuenow="55"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Backend API</p>
                    <div class="progress rounded mb-2" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 66%" aria-valuenow="66"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div> -->
              <!-- </div>
                </div>
              </div> -->
              <div class="col-md-12">
                <div class="card mb-4 mb-md-0">
                  <div class="card-body">
                    <h1 class="d-flex justify-content-center">Goal</h1>
                    <div>
                      <!-- <img src="data:image/png;base64,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" alt=""> -->
                      <canvas id="myChart"></canvas>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<?php include 'settings/footer.php' ?>

<script>
  // const myChart = new Chart(document.getElementById("myChart"), {
  //   type: "line",
  //   data: {
  //     labels: ["January", "February", "March", "April", "May", "June"],
  //     datasets: [{
  //       data: [10, 40, 30, 45, 40, 60],
  //       backgroundColor: "rgba(0, 0, 255, 0.5)",
  //       borderColor: "blue"
  //     }]
  //   },
  //   options: {
  //     title: {
  //       text: "A Simple Graph"
  //     }
  //   }
  // });

  var notyf = new Notyf({
    position: {
      x: 'right',
      y: 'top'
    }
  });

  $('#changePassword').submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: "settings/api/userApi.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  $('#profile').change(() => {
    const fileInput = document.getElementById("profile");
    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const formData = new FormData();
      formData.append("profile", file);
      formData.append("type", 'updatedata');
      formData.append("id", <?php echo $userId ?>);
      $.ajax({
        url: "settings/api/userApi.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $('#profile_image').attr("src", "images/users/" + file.name)
        }
      });
    }
  });
</script>

</body>

</html>