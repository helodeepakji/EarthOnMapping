<?php

$current_page = 'leave';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';
$leaves = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'approve' ORDER BY `leaves`.`created_at` DESC");
$leaves->execute();
$leaves = $leaves->fetchAll(PDO::FETCH_ASSOC);
$countAprrove = count($leaves);

$leavespending = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'pending' ORDER BY `leaves`.`created_at` DESC");
$leavespending->execute();
$leavespending = $leavespending->fetchAll(PDO::FETCH_ASSOC);
$countPending = count($leavespending);


$leavesCancel = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'cancel' ORDER BY `leaves`.`created_at` DESC");
$leavesCancel->execute();
$leavesCancel = $leavesCancel->fetchAll(PDO::FETCH_ASSOC);
$countCancel = count($leavesCancel);

?>

<?php 
  $title = 'Leaves Application || EOM ';
  include 'settings/header.php' 
?>

  <main style="margin-top: 100px;">
  <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
      <a href="leave-application.php" style="display: flex;align-items: center;margin: 0 10px">
        <button type="button" class="btn btn-primary position-relative">
        Pending
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countPending ?>
          <span class="visually-hidden">Pending</span>
        </span>
      </button></a>
      <a href="#" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative">
        Approve
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countAprrove ?>
          <span class="visually-hidden">Approve</span>
        </span>
        </button>
      </a>
      <a href="leave-application-cancel.php" style="display: flex;align-items: center;margin: 0 10px">
        <button type="button" class="btn btn-primary position-relative">
        Cancel
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countCancel ?>
          <span class="visually-hidden">Cancel</span>
        </span>
        </button>
      </a>
    </div>
    <div class="container pt-5">
      <div class="container">
        <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <div>
            <p class="fw-bold">Leave Application</p>
          </div>
        </div>
        <table id="dataTable" class="display">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">First Name</th>
              <th scope="col">Last Name</th>
              <th scope="col">Leave Type</th>
              <th scope="col">Reason</th>
              <th scope="col">From Date</th>
              <th scope="col">End Date</th>
              <th scope="col">Status</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
              foreach($leaves as $leave){
                $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $user->execute([$leave['user_id']]);
                $user = $user->fetch(PDO::FETCH_ASSOC);

                  echo '
                    <tr id="row_'.$leave['leave_id'].'">
                      <th scope="row">'.$i.'</th>
                      <td>'.$user['first_name'].'</td>
                      <td>'.$user['last_name'].'</</td>
                      <td>'.$leave['leave_type'].'</td>
                      <td>'.$leave['region'].'</td>
                      <td>'.$leave['form_date'].'<br><span style="color:red">'.$leave['formdate_session'].'</span></td>
                      <td>'.$leave['end_date'].'<br><span style="color:red">'.$leave['enddate_session'].'</span></</td>
                      <td id="row_status_'.$leave['leave_id'].'">'.strtoupper($leave['status']).'</td>
                      <td> <a class="btn btn-danger" onclick="cancelLeave('.$leave['leave_id'].')">Cancel</a></td>
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

    function cancelLeave(leave_id){
      $.ajax({
        url: 'settings/api/leaveApi.php',
        data: {
          type : 'cancelLaves',
          leave_id : leave_id
        },
        dataType: 'json',
        success: function(response) {
          notyf.success(response.message);
          $('#row_'+leave_id).remove();
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