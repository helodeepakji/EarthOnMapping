<?php

$current_page = 'dashbord';

session_start();
include 'settings/config/config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
  $user_id = $_SESSION['userId'];
}

$resetFlag = 0;
$user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
$user->execute([$user_id]);
$user = $user->fetch(PDO::FETCH_ASSOC);
if (password_verify('Default', $user['password'])) {
  $resetFlag = 1;
}


$attendence = $conn->prepare("SELECT * FROM `attendence` WHERE `date` = CURDATE() AND `user_id` = ?");
$attendence->execute([$user_id]);
$attendence = $attendence->fetch(PDO::FETCH_ASSOC);
$attendence_clockIn_date = DateTime::createFromFormat('H:i:s', $attendence['clock_in_time']);
$attendence_clockOut_date = DateTime::createFromFormat('H:i:s', $attendence['clock_out_time']);

$projects = $conn->prepare("SELECT * FROM `projects` ORDER BY `projects`.`complexity` ASC");
$projects->execute();
$projects = $projects->fetchAll(PDO::FETCH_ASSOC);


$assigns = $conn->prepare("SELECT * FROM `assign` JOIN tasks ON assign.task_id = tasks.task_id AND tasks.project_id = assign.project_id WHERE `assign`.`user_id` = ? AND `assign`.`isActive` = 1 AND `assign`.`status` = 'assign'");
$assigns->execute([$user_id]);
$assigns = $assigns->fetchAll(PDO::FETCH_ASSOC);


function getTimeAgo($givenTimestamp)
{

  $givenUnixTimestamp = strtotime($givenTimestamp);
  $currentUnixTimestamp = time();
  $timeDifference = $currentUnixTimestamp - $givenUnixTimestamp;
  $days = floor($timeDifference / (60 * 60 * 24));
  $hours = floor(($timeDifference - ($days * 60 * 60 * 24)) / (60 * 60));
  $minutes = floor(($timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60)) / 60);
  $seconds = $timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60) - ($minutes * 60);
  $timeAgo = "";
  if ($days > 0) {
    $timeAgo .= $days . " days ";
    return $timeAgo;
  }
  if ($hours > 0) {
    $timeAgo .= $hours . " hr ";
  }
  if ($minutes > 0) {
    $timeAgo .= $minutes . " min ";
  }
  if ($timeAgo == "") {
    $timeAgo = "just now";
  }

  return $timeAgo;
}
?>
<?php
$title = 'DashBord || EOM ';
include 'settings/header.php' ?>
<style>
  .company_update {
    height: -webkit-fill-available;
    overflow: auto;
  }

  .clickable-row {
    cursor: pointer;
  }

  .medium {
    background-color: yellow !important;
  }

  .higher {
    background-color: #ffacac !important;
  }

  .lower {
    background-color: #c6f6c1 !important;
  }

  .birthday-list {
    background: white;
    padding: 10px;
    border-radius: 15px;
    border: 1px dotted black;
  }

  .overflorbox {
    height: 88vh;
    overflow: auto;
  }

  .read_more_text {
    display: none;
  }

  .read_more_btn {
    font-weight: 700;
  }

  .late_login {
    background-color: red;
    padding: 3px 8px;
    border-radius: 5px;
  }
  .morning{
    background-color: black;
  }
  .evening{
    background-color: #7a7a7a;
  }
  .general{
    background-color: #739700;
  }
</style>
<div class="modal" id="myModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Upload Post</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="uploadPost" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Caption </label>
              <span id="captionerror" style="color:red"></span>
              <div class="input-group">
                <textarea class="form-control" name="caption" id="caption" required></textarea>
                <input type="hidden" class="form-control" name="type" value="postUpload" required>
              </div>
            </div>
            <label for="fileInput" class="form-label file">Select a Post</label>
            <div class="input-group">
              <input type="file" class="form-control Attachment-input" id="inputGroupFile04"
                aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="post" required>
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Post</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="editModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Post</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="editPostForm" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Caption </label>
              <span id="captionerror" style="color:red"></span>
              <div class="input-group">
                <textarea class="form-control" name="caption" id="edit_caption" required></textarea>
                <input type="hidden" class="form-control" name="type" value="editPost" required>
                <input type="hidden" class="form-control" name="id" id="post_id" value="" required>
              </div>
            </div>
            <label for="fileInput" class="form-label file">Select a Post</label>
            <div class="input-group">
              <input type="file" class="form-control Attachment-input" id="inputGroupFile04"
                aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="post">
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Update</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal" id="changePassword" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Change Password</h4>
      </div>
      <form id="changePasswordForm" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Password </label>
              <div class="input-group">
                <input type="password" class="form-control" name="password" id="password" required>
                <input type="hidden" class="form-control" name="type" value="changePassword" required>
                <input type="hidden" class="form-control" name="old_password" value="Default" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Confirm Password </label>
              <div class="input-group">
                <input type="password" class="form-control" name="cpassword" id="cpassword" value="" required>
              </div>
            </div>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Change Password</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<main style="margin-top: 80px;">
  <div class="">
    <div style="width:100%;text-align: end">
      <?php

      if ($_SESSION['userType'] == 'admin') {
        echo '
        <a data-bs-toggle="modal" style="margin:0 20px" href="#myModal" class="btn btn-primary">Post Upload</a>
        ';
      }

      ?>
    </div>
    <div class="">
      <div class="row justify-content-center">
        <div class="col border col-5 p-25  box2 overflorbox">

          <h2 class="heading m-4">Attendance</h2>
          <?php
          if ($_SESSION['userType'] == 'admin') {
            ?>
            <div class="border d-block p-3 w-100 shadow ">
              <div class=" scroll-bar">
                <table class="table table-striped " id="dataTable">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Date</th>
                      <th scope="col">Name</th>
                      <th scope="col">Time</th>
                    </tr>
                  </thead>
                  <tbody class="">
                    <?php
                    $attendenceLists = $conn->prepare("SELECT * FROM `attendence` WHERE `date` >= CURDATE() - INTERVAL 7 DAY AND `date` <= CURDATE() ORDER BY `created_at` DESC");
                    $attendenceLists->execute();
                    $attendenceLists = $attendenceLists->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($attendenceLists as $attendenceList) {
                      $attend_user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                      $attend_user->execute([$attendenceList['user_id']]);
                      $attend_user = $attend_user->fetch(PDO::FETCH_ASSOC);
                      if ($attendenceList['clock_out_time']) {
                        $clock_in = date("h:i A", strtotime($attendenceList['clock_in_time']));
                        $clock_out = date("h:i A", strtotime($attendenceList['clock_out_time']));
                      } else {
                        $clock_out = '';
                      }

                      $clock_in_time = strtotime($attendenceList['clock_in_time']);

                      if ($clock_in_time >= strtotime('5:00 AM') && $clock_in_time <= strtotime('8:00 AM')) {
                        $late_login = '<br><span class="badge badge-danger late_login morning">Morning</span>';
                        if($clock_in_time >= strtotime('6:45 AM')){
                          $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
                        }else{
                          $late_login_status = '';
                        }
                      } else if ($clock_in_time >= strtotime('12:00 PM') && $clock_in_time <= strtotime('3:00 PM')) {
                        $late_login = '<br><span class="badge badge-danger late_login evening">Evening</span>';
                        if($clock_in_time > strtotime('2:45 PM')){
                          $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
                        }else{
                          $late_login_status = '';
                        }
                      } else {
                        $late_login = '<br><span class="badge badge-danger late_login general">General</span>';
                        if($clock_in_time >= strtotime('9:15 AM')){
                          $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
                        }else{
                          $late_login_status = '';
                        }
                      }


                      echo '
                          <tr>
                            <th style="color:transparent;width:0;font-size:0px">'.$attendenceList['id'].'</th>
                            <th class> ' . date("d-m-y", strtotime($attendenceList['date'])) . ' '.$late_login.' '.$late_login_status.'</th>
                            <th class>' . $attend_user['first_name'] . ' ' . $attend_user['last_name'] . '</th>
                            <th class="'.($late_login_status != '' ? 'text-danger' : '').'">' . date("h:i A", strtotime($attendenceList['clock_in_time'])) . ' <p>' . $clock_out . '</p></th>
                          </tr>                  
                      ';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php } else { ?>
            <div class="border d-block p-3 w-100 shadow ">
              <div class="d-flex mb-2">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="cake"
                  class="birthday-cake">
                <div class="ms-2">
                  <p class="fw-bold ">
                    <?php echo $user['first_name'] . ' ' . $user['last_name'] ?>
                  </p>
                  <p>Clock In : <span style="color:grey">
                      <?php echo $attendence_clockIn_date->format("g:i A"); ?>
                    </span></p>
                  <p>Clock Out : <span id="clock_out" style="color:grey">
                      <?php
                      if (!empty($attendence_clockOut_date)) {
                        echo $attendence_clockOut_date->format("g:i A");
                        $flag = 1;
                      } else {
                        echo "";
                        $flag = 0;
                      }
                      ?>
                    </span></p>
                </div>
              </div>
              <div class="d-flex gap-3 birthday text-center" style="justify-content: flex-end;">
                <button class="btn btn-primary" disabled>
                  Clock In
                </button>
                <button class="btn btn-primary" id="clockout_btn" onclick="clockOut()" <?php if ($flag)
                  echo "disabled" ?>>
                    Clock Out
                  </button>
                </div>
              </div>
          <?php } ?>

          <h2 class="heading m-4">Upcomming Birthday</h2>
          <div class="border d-block p-3 w-100 shadow ">
            <div class="d-flex mb-2">
              <img src="images/cake1.png" alt="cake" class="birthday-cake" style="  ">
              <div class="ms-2">
                <p class="fw-bold ">Celebrating Birthday</p>
                <p>Next Up Comming Birthday</p>
              </div>
            </div>
            <div class="d-flex gap-3 birthday text-center">
              <?php
              $birthdays = $conn->prepare("SELECT * FROM `users` WHERE `dob` >= CURDATE() ORDER BY `users`.`dob` ASC LIMIT 3");
              $birthdays->execute();
              $birthdays = $birthdays->fetchAll(PDO::FETCH_ASSOC);
              foreach ($birthdays as $birthday) {
                echo '<div class="birthday-list">
                          <img src="images/pixel1.jpg" alt="" class="birthday-img ">
                          <p>' . $birthday['first_name'] . ' ' . $birthday['last_name'] . '</p>
                          <b>(' . date('M j, D', strtotime($birthday['dob'])) . ')</b>
                        </div>';
              }
              ?>
            </div>
          </div>

          <h2 class="heading m-4">Upcomming Holidays</h2>
          <div class="border d-block  w-100 UP-Leave shadow ">
            <div class="d-flex justify-content-between">
              <div>
                <?php
                $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` > CURDATE() LIMIT 1");
                $holiday->execute();
                $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
                $date = new DateTime($holiday['date']);
                $formattedDate = $date->format('M d, D');

                ?>
                <p class="fw-bold">Holiday</p>
                <div class="mt-5">
                  <h4>
                    <?php echo $holiday['summary']; ?>
                  </h4>
                  <p><span>
                      <?php echo $formattedDate ?>
                    </span></p>
                </div>
              </div>
              <div>
                <img src="images/holiday/<?php echo $holiday['image']; ?>" width="200px" alt="">
              </div>
            </div>
          </div>
          <?php
          if ($_SESSION['userType'] == 'admin') {
            ?>
            <h2 class="heading m-4">Projects List</h2>
          <?php } else { ?>
            <h2 class="heading m-4">Tasks List</h2>
          <?php } ?>
          <div class="border d-block shadow mt-3 ">

            <div class="border issue-scroller p-2">
              <div class=" scroll-bar" id="">

                <?php
                if ($_SESSION['userType'] == 'admin') {
                  ?>
                  <p class="fw-bold">Projects </p>
                  <table class="table table-striped ">
                    <tbody class="">
                      <?php
                      $i = 1;
                      foreach ($projects as $project) {
                        echo '
                                <tr class="clickable-row" data-href="project-details.php?project_id=' . base64_encode($project['project_id']) . '">
                                  <th class>#' . $i . '</th>
                                  <th class>' . $project['project_id'] . '</th>
                                  <td>' . $project['project_name'] . '</td>
                                  <td ><span class="close ' . $project['complexity'] . '">' . strtoupper($project['complexity']) . '</span></td>
                                  <td>' . $project['summary'] . '</td>  
                                </tr>                  
                            ';
                        $i++;
                      }
                      ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <p class="fw-bold">Tasks </p>
                  <table class="table table-striped ">
                    <tbody class="">
                      <?php
                      $i = 1;
                      foreach ($assigns as $assign) {

                        echo '
                                  <tr class="clickable-row" data-href="task-details.php?task_id=' . base64_encode($assign['task_id']) . '">
                                    <th class>#' . $i . '</th>
                                    <th class>' . $assign['task_id'] . '</th>
                                    <td>' . $assign['area_sqkm'] . 'Sqkm</td>
                                    <td ><span class="close ' . $assign['complexity'] . '">' . strtoupper($assign['complexity']) . '</span></td>
                                    <td>' . $assign['summary'] . '</td>  
                                  </tr>                  
                              ';
                        $i++;
                      }
                      ?>
                    </tbody>
                  </table>
                <?php } ?>
              </div>
            </div>
          </div>

        </div>
        <div class="col-sm-6 border p-25  company_update overflorbox">
          <div class="">

            <h2 class="heading m-4">Company Update</h2>
          </div>
          <?php
          $posts = $conn->prepare("SELECT * FROM `posts` ORDER BY `created_at` DESC");
          $posts->execute();
          $posts = $posts->fetchAll(PDO::FETCH_ASSOC);
          foreach ($posts as $post) {

            $postuser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $postuser->execute([$post['user_id']]);
            $postuser = $postuser->fetch(PDO::FETCH_ASSOC);

            $userimage = $postuser['image'] != '' ? 'images/users/' . $postuser['image'] : 'images/pix1.jpg';

            if ($_SESSION['userType'] == 'admin') {
              $action = '<i data-bs-toggle="modal" href="#editModal" class="fas fa-edit" style="margin:0 10px;color:green;cursor: pointer;" onclick="editPost(' . $post['id'] . ')"></i><i class="fas fa-trash" style="color:#871111;cursor: pointer;" onclick="deletePost(' . $post['id'] . ')"></i>';
            } else {
              $action = "";
            }

            if (strlen($post['caption']) > 200) {
              $readMore = '<span id="dots_' . $post['id'] . '">...</span> <span class="read_more_text" id="more_' . $post['id'] . '">
                ' . substr($post['caption'], 200, strlen($post['caption'])) . '</span><a class="read_more_btn" onclick="postReadMore(' . $post['id'] . ')" id="read_more_' . $post['id'] . '">Read more</a>';
            } else {
              $readMore = '';
            }


            echo '
           
                <div class=" border p-3 mb-3 w-100  shadow " id="post_' . $post['id'] . '">
                    <div class="d-flex"     style="justify-content: space-between; ">
                    <div class="d-flex">
                      <div class="company">
                        <img src="' . $userimage . '" alt="" class="mt-1 shadow">
                      </div>
                      <div class="ms-2 mb-2">
                        <p><b>' . $postuser['first_name'] . ' ' . $postuser['last_name'] . '</b></p>
                        <p> <i> ' . getTimeAgo($post['created_at']) . ' ago </i></p>
                      </div>
                      </div>
                      <div style="text-align:right">' . $action . '</div>
                    </div>
                    <div>
                      <p class=" mb-4">' . substr($post['caption'], 0, 200) . '' . $readMore . '</p>
                      <div class=" ">
                        <img src="images/posts/' . $post['image'] . '" width="100%" height="350px" style="object-fit: cover;" alt="">
                      </div>
                    </div>
                  </div>
                
                ';
          }
          ?>
        </div>
      </div>
    </div>
</main>


<?php include 'settings/footer.php' ?>
<script>
  $(document).ready(function() {
    dataTable.order([0, 'desc']).draw();
    dataTable.page.len(5).draw();
  });
  var notyf = new Notyf({
    position: {
      x: 'right',
      y: 'top'
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    if (<?php echo $resetFlag ?> == 1) {
      $("#changePassword").modal("show");
    }
  });


  document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.clickable-row');
    rows.forEach(row => {
      row.addEventListener('click', function () {
        window.location.href = this.dataset.href;
      });
    });
  });

  function postReadMore(id) {
    var dots = document.getElementById("dots_" + id);
    var moreText = document.getElementById("more_" + id);
    var btnText = document.getElementById("read_more_" + id);

    if (dots.style.display === "none") {
      dots.style.display = "inline";
      btnText.innerHTML = "Read more";
      moreText.style.display = "none";
    } else {
      dots.style.display = "none";
      btnText.innerHTML = "Read less";
      moreText.style.display = "inline";
    }
  }

  function clockOut() {
    $.ajax({
      url: 'settings/api/attendanceApi.php',
      type: 'POST',
      data: { type: 'clockOut' },
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        $("#clock_out").text(response.clockOut);
        $("#clockout_btn").prop("disabled", true);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  $('#uploadPost').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    var caption = $('#caption').val();
    $.ajax({
      url: 'settings/api/postApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  })

  $('#editPostForm').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/postApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  })

  function deletePost(id) {
    Notiflix.Confirm.show(
      'Confirmation',
      'Do you want to delete this post?',
      'Yes',
      'No',
      function () {
        $.ajax({
          url: 'settings/api/postApi.php',
          type: 'POST',
          data: {
            type: 'deleteUpload',
            id: id
          },
          dataType: 'json',
          success: function (response) {
            notyf.success(response.message);
            $('#post_' + id).remove();
          },
        });
      });
  }

  function editPost(id) {
    $.ajax({
      url: 'settings/api/postApi.php',
      type: 'GET',
      data: {
        type: 'getPost',
        id: id
      },
      dataType: 'json',
      success: function (response) {
        $('#post_id').val(id);
        $("#edit_caption").val(response.caption);
      },
    });
  }


  $("#changePasswordForm").submit(function (e) {
    e.preventDefault();
    var formdata = new FormData(this);
    var password = $('#password').val();
    var cpassword = $('#cpassword').val();
    if (password == cpassword) {
      $.ajax({
        url: 'settings/api/userApi.php',
        type: 'Post',
        data: formdata,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          notyf.success(response.message);
          $('#changePassword').modal('hide');
        },
        error: function (xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    } else {
      console.log(password);
      console.log(cpassword);
      notyf.error("Password & Confirm Password is not matched");
    }
  })

</script>


</body>

</html>