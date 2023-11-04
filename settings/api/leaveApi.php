<?php

include '../config/config.php';
header("content-Type: application/json");

session_start();
$user_id = $_SESSION['userId'];
$user_type = $_SESSION['userType'];

if(!$user_id){
    http_response_code(400);
    echo json_encode(array("message" => "Login First....", "status" => 400));
}


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addLeave')) {

    if (($_POST['leave_type'] != '') && ($_POST['form_date'] != '') && ($_POST['end_date'] != '') && ($_POST['formdate_session'] != '') && ($_POST['enddate_session'] != '') && ($_POST['contact_detail'] != '') && ($_POST['region'] != '')) {

        if (isset($_FILES["upload"]) && $_FILES["upload"]["error"] == UPLOAD_ERR_OK) {

            $upload = basename($_FILES['upload']['name']);
            $uploadPath = '../../upload/attachment/' . $upload;
            move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath);
        }else{
            $upload = null;
        }

        $leave = array($_POST['leave_type'] ,$user_id ,$_POST['form_date'] , $_POST['end_date'] , $_POST['formdate_session'] , $_POST['enddate_session'] , $upload , $_POST['contact_detail'], $_POST['region'] );
        
        $check = $conn->prepare('INSERT INTO `leaves`(`leave_type`, `user_id`, `form_date`, `end_date`, `formdate_session`, `enddate_session`, `upload`, `contact_detail`, `region` ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ?)');
        $result = $check->execute($leave);

        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Leave Added...', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getAllLeave')) {
    $sql = $conn->prepare('SELECT * FROM `leaves`');
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'No task found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getleave') {
    $sql = $conn->prepare("SELECT * FROM `leaves` WHERE `leave_id` = ?");
    $sql->execute([$_GET['id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
    http_response_code(200);
    echo json_encode($result[0]);
    } else {
    http_response_code(404);
    echo json_encode(array("message" => 'No task found', "status" => 404));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getleaveById') {
    $sql = $conn->prepare("SELECT * FROM `leaves` WHERE `leave_id` = ?");
    $sql->execute([$_GET['leave_id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
    http_response_code(200);
    echo json_encode($result);
    } else {
    http_response_code(404);
    echo json_encode(array("message" => 'No task found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'deleteLeave') {
    $sql = $conn->prepare("DELETE FROM `leaves` WHERE `leave_id` = ?");
    $result = $sql->execute([$_POST['id']]);
    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Delete leave successfull...', "status" => 404));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 404));
    }
}


if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'approveLeave')) {
    
     
    $check = $conn->prepare("UPDATE `leaves` SET `status` = 'approve' WHERE leave_id = ? ");
    $result = $check->execute([$_GET['leave_id']]);

    if($user_type != 'admin'){
        exit;
    }

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'successfull Leave Status Change.', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
    }
    
}


if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'cancelLaves')) {
    
     
    $check = $conn->prepare("UPDATE `leaves` SET `status` = 'cancel' WHERE leave_id = ? ");
    $result = $check->execute([$_GET['leave_id']]);

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'successfull Leave Status Change...', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
    }
    
}



?>