<?php

$current_page = 'employee-list';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';
$sql = $conn->prepare("SELECT * FROM `users` Where `user_type` != 'admin'");
$sql->execute();
$users = $sql->fetchAll(PDO::FETCH_ASSOC);

?>
<?php 
  $title = 'Employee List || EOM ';
  include 'settings/header.php' 
?>

  <main style="margin-top: 58px;">
    <div class="container pt-5">
      <div class="container">
        <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <div>
            <p class="fw-bold">Employees</p>
          </div>
        </div>
        <table id="dataTable" class="display">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Employee Id</th>
              <th scope="col">First Name</th>
              <th scope="col">Last Name</th>
              <th scope="col">Date of Birth</th>
              <th scope="col">Phone</th>
              <th scope="col">Address</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
              foreach($users as $user){
                  $id = base64_encode($user['id']);
                  echo '
                    <tr id="row_'.$user['id'].'">
                      <th scope="row">'.$i.'</th>
                      <td>'.$user['employee_id'].'</td>
                      <td>'.$user['first_name'].'</td>
                      <td>'.$user['last_name'].'</</td>
                      <td>'.$user['dob'].'</</td>
                      <td>'.$user['phone'].'</td>
                      <td>'.$user['address'].'</td>
                      <td style="display: flex;"> <a class="btn btn-primary" href="create-employee.php?edit='.$id.'" style="margin:0 10px">Edit</a><a class="btn btn-danger" onclick="deleteUser('.$user['id'].')">Delete</a></td>
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

    function deleteUser(id){
      $.ajax({
        url: 'settings/api/userApi.php',
        type: 'GET',
        data : {
          type : 'deleteUser',
          id : id
        },
        success: function(response) {
          notyf.success(response.message);
          $("#row_"+id).remove();
        }
      });
    }
  </script>

</body>

</html>