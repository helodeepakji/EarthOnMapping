<?php

  $current_page = 'login';
  if($_SERVER["REQUEST_METHOD"] == "POST"){
  $employee_id = $_POST['employee_id'];
  $userpassword = $_POST['password'];

  include 'settings/config/config.php';
  $check = $conn->prepare('SELECT * FROM `users` WHERE `employee_id` = ?');
  $check->execute([$employee_id]);
  $result = $check->fetchAll(PDO::FETCH_ASSOC);
  $result = $result[0];
  if($result){
    if(password_verify($userpassword, $result['password'])){
      session_start();
      $_SESSION['loggedin'] = true;
      $_SESSION['userDetails'] = $result;
      $_SESSION['userId'] = $result['id'];
      $_SESSION['userType'] = $result['user_type'];
      setcookie('userId',$result['id'],time() + 3600*720);


      $check = $conn->prepare("SELECT * FROM `attendence` WHERE `date` = ?  AND `user_id` = ?");
      $check->execute([date("Y-m-d"),$result['id']]);
      $check = $check->fetch(PDO::FETCH_ASSOC);
      if(!$check){
        $attendance = $conn->prepare("INSERT INTO `attendence`(`user_id`) VALUES ( ? )");
        $attendance->execute([$result['id']]);
      }

      header("location:index.php");
    }else {
      echo "<script>alert('Password is wrong.....')</script>";
    }
  }else {
    echo "<script>alert('User not found Check the Employee Id.......')</script>";
  }
 }
  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/plugin/font-awesome-all.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/plugin/bootstrap.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/test.css">
    <style>
      main {
          padding: 0;
          background: #ffffff;
          color: white;
          height: 100vh;
      }
      .container {
          background-color: #ffffff;
      }
      .row{
        align-items: center;
      }
    </style>
</head>
<body> 

  <main>
    <div class="container pt-4">
      <div class="Login_page">
        <section>
            <div class="container-fluid">
              <div class="row">
                <div class="col-sm-6 text-black">
                  <h1 class="mb-3">Welcome to EOM</h1>
                  <div class="d-flex align-items-center ">
                    
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="width: 23rem;">
          
                      <h3 class="fw-normal mb-2 pb-1" style="letter-spacing: 1px;">Log in</h3>
          
                      <div class="form-outline mb-2 form-group">
                      <label class="form-label" for="employee_id">Employee Id</label>
                        <input type="text" id="employee_id" name="employee_id" class="employee_id form-control form-control-md" required/>
                        
                      </div>
          
                      <div class="form-outline mb-4 form-group">
                      <label class="form-label" for="pass">Password</label>
                        <input type="password" id="pass" name="password" class="pass form-control form-control-md" required/>
                        
                      </div>
          
                      <div class="pt-1 mb-2 form-group">
                        <button class="btn btn-info w-100" style="color:white;font-weight:700" type="submit">Login</button>
                      </div>
                      <p id="wm" style="display: none; color: red;"></p>
                      <!-- <p class="small mb-3 pb-lg-2"><a class="text-muted" href="#!">Forgot password?</a></p> -->
                      <p>Don't have an account? <a href="register.php" class="link-info">Register here</a></p>
          
                    </form>
          
                  </div>
          
                </div>
                <div class="col-sm-6 px-0 d-none d-sm-block ">
                    <img src="images/login2.jpg"
                    alt="Login image"  style="object-fit: cover; object-position: left;">
                </div>
              </div>
            </div>
          </section>
    </div>
    </div>
  </main>

  <?php include 'settings/footer.php' ?>

  <script>
    function validateForm() {
      var name = document.getElementById("name").value;
      var employee_id = document.getElementById("employee_id").value;
      var password = document.getElementById("pass").value;
      var confirmPassword = document.getElementById("confirm-password").value;
      var wm=document.getElementById("wm").values;

      if (name === "" || employee_id === "" || password === "" || confirmPassword === "") {
        document.getElementById("warning-message").style.display = "block";
        name.innerHTML="name is requred"
        return false;
      }
      if(pass.length <=8){
       wm.innerHTML="faield"

      }

      return true;
    }
  </script>
    

</body>
</html>