<?php

    include 'settings/config/config.php';
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if(($_POST['first_name'] != '') && ($_POST['last_name'] != '') && ($_POST['employee_id'] != '') && ($_POST['address'] != '') && ($_POST['phone'] != '') && ($_POST['password'] != '') ){

            $check = $conn->prepare('SELECT * FROM `users` WHERE `phone` = ? OR `employee_id` = ? ');
            $check->execute([$_POST['phone'],$_POST['employee_id']]);
            $userdata = $check->fetchAll(PDO::FETCH_ASSOC);
            if(!$userdata){
                $hashpassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $user = array($_POST['first_name'], $_POST['last_name'], $_POST['employee_id'], $_POST['address'], $_POST['phone'], $hashpassword);
                $sql = $conn->prepare('INSERT INTO `users` (`first_name` ,`last_name`, `employee_id`, `address`, `phone`, `password`) VALUES (? , ? , ? , ? , ? , ? )');
                $result = $sql->execute($user);
                if($result){
                    $lastInsertId = $conn->lastInsertId();
                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['userDetails'] = $user;
                    $_SESSION['userId'] = $lastInsertId;
                    $_SESSION['userType'] = 'user';
                    setcookie('userId',$lastInsertId,time() + 3600*720);

                    $check = $conn->prepare("SELECT * FROM `attendence` WHERE `date` = ?  AND `user_id` = ?");
                    $check->execute([date("Y-m-d"),$_SESSION['userId']]);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
                    if(!$check){
                        $attendance = $conn->prepare("INSERT INTO `attendence`(`user_id`) VALUES ( ? )");
                        $attendance->execute([$_SESSION['userId']]);
                    }

                    header("location:index.php");
                }else{
                    echo "<script>alert('Something went wrong...')</script>";
                }
            }else{
                echo "<script>alert('User already exist...')</script>";
            }
        }else{
            echo "<script>alert('Fill all requied fields...')</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/plugin/font-awesome-all.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="assets/plugin/bootstrap.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    
    <link rel="stylesheet" href="css/test.css">
    <style>
        section{
            height: 100vh;
        }
        .container{
            background: none;
        }

        form {
            display: flex;
            flex-wrap: wrap;
        }

        .form-outline.mb-2 {
            width: 50%;
            padding: 10px;
        }
    </style>
</head>

<body>

    <section class="vh-110 bg-image"
        style="background-image: url('images/register.webp');">
        <div class="mask d-flex align-items-center h-100 gradient-custom-3">
            <div class="container h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-9 col-lg-7 col-xl-6">
                        <div class="card" style="border-radius: 15px;">
                            <div class="card-body p-5">
                                <h2 class="text-uppercase text-center mb-3">Create an account</h2>

                                <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example1cg" >First Name</label>
                                        <input type="text" name="first_name" id="form3Example1cg" class="form-control form-control-md" />
                                    </div>

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example1cg" >Last Name</label>
                                        <input type="text" name="last_name" id="form3Example1cg" class="form-control form-control-md" />
                                    </div>

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example3cg" > Employee Id</label>
                                        <input type="employee_id" name="employee_id" id="form3Example3cg" class="form-control form-control-md" />
                                    </div>

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example1cg">Address</label>
                                        <input type="text"  name="address" id="form3Example1cg" class="form-control form-control-md" />
                                    </div>

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example1cg" >Phone</label>
                                        <input type="number" name="phone" id="form3Example1cg" class="form-control form-control-md" />
                                    </div>

                                    <div class="form-outline mb-2">
                                        <label class="form-label" for="form3Example4cg" >Password</label>
                                        <input type="password" name="password" id="form3Example4cg"
                                            class="form-control form-control-md" />
                                    </div>
                                    <div class="form-outline mb-2">
                                    <label for="datepicker" class="me-3 form-label">Date Of Birth</label>
                                    <input type="text" class="form-control Attachment-input" id="datepicker" placeholder="Select a date" name="dob" required>
                   
                                    </div>

                                   

                                    <!-- <div class="form-check d-flex justify-content-center mb-2">
                                        <input class="form-check-input me-2" type="checkbox" value=""
                                            id="form2Example3cg" />
                                        <label class="form-check-label" for="form2Example3g">
                                            I agree all statements in <a href="#!" class="text-body"><u>Terms of
                                                    service</u></a>
                                        </label>
                                    </div> -->

                                    <div class="d-flex justify-content-center" style="align-items: center;    width: 100%;">
                                        <button type="submit"
                                            class="btn btn-success btn-block btn-lg gradient-custom-4 text-body w-100" style="color: white !important;">Register</button>
                                    </div>

                                    <p class="text-center text-muted mt-2 mb-0">Have already an account? <a href="login.php"
                                            class="fw-bold text-body"><u>Login here</u></a></p>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    


    <?php include 'settings/footer.php' ?>
<script>
      let date = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
</script>
</body>

</html>