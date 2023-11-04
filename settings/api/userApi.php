<?php

include "../config/config.php";
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
session_start();
$response = [];

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "insertdata")) {

    $phone = $_POST['phone'];
    $employee_id = $_POST['employee_id'];

    $check = $conn->prepare("SELECT * FROM `users` WHERE phone = :phone  OR employee_id = :employee_id");
    $check->bindParam(':phone', $phone);
    $check->bindParam(':employee_id', $employee_id);
    $check->execute();
    $result = $check->fetchAll();
    if (!$result) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $dob = $_POST['dob'];
        $user_type = 'user';
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = $conn->prepare("INSERT INTO `users`( `first_name`, `last_name`, `address`, `employee_id`, `phone`, `dob` ,`password` , `user_type`) VALUES (:first_name,:last_name,:address, :employee_id,:phone,:dob,:password,:user_type)");
        $sql->bindParam(':first_name', $first_name);
        $sql->bindParam(':last_name', $last_name);
        $sql->bindParam(':address', $address);
        $sql->bindParam(':employee_id', $employee_id);
        $sql->bindParam(':phone', $phone);
        $sql->bindParam(':dob', $dob);
        $sql->bindParam(':password', $password);
        $sql->bindParam(':user_type', $user_type);
        $result = $sql->execute();
        if ($result) {
            $response['status'] = 200;
            $response['message'] = "user add successfull";
        } else {
            http_response_code(500);
            $response['status'] = 500;
            $response['message'] = "Something Went Wrong....";
        }
    } else {
        http_response_code(404);
        $response['status'] = 404;
        $response['message'] = "User Already exist.";
    }
}


if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "resetPassword")) {

    if($_POST['user_id'] != ''){

        $check = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $check->execute([$_POST['user_id']]);
        $user = $check->fetch(PDO::FETCH_ASSOC);
        if($user){
            $hashedPassword = password_hash('Default', PASSWORD_DEFAULT);
            $sql = $conn->prepare("UPDATE `users` SET `password`=  ? WHERE `id` = ?");
            $result = $sql->execute([$hashedPassword,$_POST["user_id"]]);
            if ($result) {
                $response["status"] = 200; 
                $response["message"] = "Successfull Change Password";
            }
        } else {
            http_response_code(500);
            $response["status"] = 500;
            $response["message"] = "Something Went Wrong";
        }
    }else {
        http_response_code(404);
        $response['status'] = 404;
        $response['message'] = "User Id is required.";
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "updatedata")) {

    $id = $_POST['id'];
    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = $id");
    $user->execute();
    $user = $user->fetch(PDO::FETCH_ASSOC);

    if($_FILES['profile']['name'] != ''){
        unlink('../../images/users/' . $user['profile']);
        $image = basename($_FILES['profile']['name']);
        $sql = $conn->prepare("UPDATE `users` SET `profile`= ? WHERE `id` = ?");
        $result = $sql->execute([$image,$_POST['id']]);
        $uploadPath = '../../images/users/' . $image;
        move_uploaded_file($_FILES['profile']['tmp_name'], $uploadPath);
    }

    if($_POST['phone'] != ''){
        $phone = $_POST['phone'];
        $employee_id = $_POST['employee_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $user_type = $_POST['user_type'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $sql = $conn->prepare("UPDATE `users` SET `first_name`= :first_name , `last_name` = :last_name,`dob` = :dob,`employee_id` = :employee_id,`phone` = :phone, `address` = :address , `user_type` = :user_type WHERE `id` = :id");
        $sql->bindParam(':first_name', $first_name);
        $sql->bindParam(':last_name', $last_name);
        $sql->bindParam(':address', $address);
        $sql->bindParam(':employee_id', $employee_id);
        $sql->bindParam(':user_type', $user_type);
        $sql->bindParam(':dob', $dob);
        $sql->bindParam(':phone', $phone);
        $sql->bindParam(':id', $id);
        $result = $sql->execute();
    }

    if ($result) {
        $response['status'] = 200;
        $response['message'] = "user update successfull";
    } else {
        http_response_code(500);
        $response['status'] = 500;
        $response['message'] = "Something Went Wrong....";
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_GET['type'] == "getUserData")) {
    $sql = $conn->prepare('SELECT `id`, `first_name`, `last_name`, `address`, `employee_id`, `phone` , `user_type` FROM `users` WHERE `id` = ?');
    $sql->execute([$_GET['id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if($result){
        http_response_code(200);
        $response['status'] = 200;
        $response['data'] = $result[0];
    }else{
        http_response_code(400);
        $response['status'] = 400;
        $response['message'] = "User don't exist....";
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_GET['type'] == "getAllUserData")) {
    $sql = $conn->prepare("SELECT `id`, `first_name`, `last_name`, `address`, `employee_id`, `phone` FROM `users` WHERE `user_type` = 'user' ");
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if($result){
        http_response_code(200);
        $response['status'] = 200;
        $response['data'] = $result;
    }else{
        http_response_code(400);
        $response['status'] = 400;
        $response['message'] = "User don't exist....";
    }

}

if (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_GET['type'] == "getAllTeamLeaderData")) {
    $sql = $conn->prepare("SELECT `id`, `first_name`, `last_name`, `address`, `employee_id`, `phone` FROM `users` WHERE `user_type` = 'teamleader' ");
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if($result){
        http_response_code(200);
        $response['status'] = 200;
        $response['data'] = $result;
    }else{
        http_response_code(400);
        $response['status'] = 400;
        $response['message'] = "User don't exist....";
    }

}

if (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_GET['type'] == "deleteUser")) {
    $sql = $conn->prepare("DELETE FROM `users` WHERE `id` = ?");
    $result = $sql->execute([$_GET['id']]);  
    if($result){
        http_response_code(200);
        $response['status'] = 200;
        $response['data'] = "User Remove Successfull";
    }else{
        http_response_code(400);
        $response['status'] = 400;
        $response['message'] = "User don't exist....";
    }

}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "changePassword")) {

    if($_POST['old_password'] != '' && $_POST['password'] != '' && $_POST['cpassword'] != ''){
        if($_POST['password'] == $_POST['cpassword']){
            $check = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
            $check->execute([$_SESSION['userId']]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            if($result){
                if(password_verify($_POST['old_password'], $result['password'])){
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = $conn->prepare('UPDATE `users` SET `password`= ? WHERE `id` = ?');
                    $result = $sql->execute([$hashedPassword , $_SESSION['userId']]);
                    if ($result) {
                        $response['status'] = 200;
                        $response['message'] = "Password is Changed";
                    } else {
                        http_response_code(500);
                        $response['status'] = 500;
                        $response['message'] = "Something Went Wrong....";
                    }
                }else{
                    http_response_code(500);
                    $response["status"] = 400;
                    $response["message"] = "Old Password is incorrect";
                }
            }else{
                http_response_code(400);
                $response["status"] = 400;
                $response["message"] = "User is not found.";
            }
        }else{
            http_response_code(400);
            $response["status"] = 400;
            $response["message"] = "Password And Confirm Password is not matched";
        }
    }else{
        http_response_code(400);
        $response["status"] = 400;
        $response["message"] = "Fill All Required Field";
    }
}


echo json_encode($response);

?>