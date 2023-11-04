<?php

include '../config/config.php';
header("content-Type: application/json");


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addProject')) {

    if (($_POST['project_name'] != '') && ($_POST['summary'] != '') && ($_POST['description'] != '') && ($_POST['original_estimation'] != '') && ($_POST['complexity'] != '') && ($_POST['vector'] != '') && ($_POST['start_date'] != '') && ($_POST['end_date'] != '')) {

        if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {

            $attachment = basename($_FILES['attachment']['name']);
            $uploadPath = '../../upload/attachment/' . $attachment;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
        }else{
            $attachment = null;
        }

        if($_POST['estimated_hour']){
            $estimated_hour = $_POST['estimated_hour'];
        }else{
            $date1 = new DateTime($_POST['start_date']);
            $date2 = new DateTime($_POST['end_date']);
            $interval = $date1->diff($date2);
            $daysDifference = $interval->days;
            $estimated_hour = $daysDifference * 8;
        }

        $project = array($_POST['project_name'] , $_POST['summary'] , $_POST['description'] , $_POST['original_estimation'] , $_POST['complexity'] ,$_POST['vector'], $attachment ,$_POST['start_date'], $_POST['end_date'] , $estimated_hour);
        
        $check = $conn->prepare('INSERT INTO `projects`(`project_name`, `summary`, `description`, `original_estimation`, `complexity`,`vector`, `attachment`,`start_date`, `end_date` , `estimated_hour` ) VALUES (? , ? , ? , ? , ?, ? , ? , ? , ? , ?)');
        $result = $check->execute($project);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project Added...', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getAllProduct')) {
    $sql = $conn->prepare('SELECT * FROM `projects`');
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'No project found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getProduct') {
    $sql = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
    $sql->execute([$_GET['id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
    http_response_code(200);
    echo json_encode($result[0]);
    } else {
    http_response_code(404);
    echo json_encode(array("message" => 'No project found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'deleteProject') {
    $sql = $conn->prepare("DELETE FROM `projects` WHERE `project_id` = ?");
    $result = $sql->execute([$_POST['id']]);
    if ($result) {
        $sql = $conn->prepare("DELETE FROM `tasks` WHERE `project_id` = ?");
        $result = $sql->execute([$_POST['id']]);

        $sql = $conn->prepare("DELETE FROM `assign` WHERE `project_id` = ?");
        $result = $sql->execute([$_POST['id']]);

        $sql = $conn->prepare("DELETE FROM `efficiency` WHERE `project_id` = ?");
        $result = $sql->execute([$_POST['id']]);

        http_response_code(200);
        echo json_encode(array("message" => 'Delete project successfull...', "status" => 404));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 404));
    }
}


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'updateProject')) {

    $project_name = $_POST['project_name'];
    $summary = $_POST['summary'];
    $description = $_POST['description'];
    $original_estimation= $_POST['original_estimation'];
    $complexity= $_POST['complexity'];
    $vector= $_POST['vector'];
    $start_date= $_POST['start_date'];
    $end_date= $_POST['end_date'];
    $project_id = $_POST['product_id'];
    $estimated_hour = $_POST['estimated_hour'];;
    
    $sql = $conn->prepare("UPDATE `projects` SET `project_name` = ?, `summary` = ?, `description`= ?, `original_estimation` = ?, `complexity`= ?, `vector` = ?, `start_date`= ?, `end_date` = ? , `estimated_hour` = ? WHERE `project_id` = ?");
    $result = $sql->execute([$project_name, $summary, $description,$original_estimation , $complexity, $vector , $start_date, $end_date, $estimated_hour, $project_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'project updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update project']);
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'totalEstimatedTime')) {
    $project_id = $_GET['project_id']; 

    if(($project_id != '')){
        $sql = $conn->prepare('SELECT SUM(estimated_hour) as total_estimated_hour FROM tasks WHERE `project_id` = ?');
        $sql->execute([$project_id ]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        
        $sql2 = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
        $sql2->execute([$project_id ]);
        $result2 = $sql2->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            http_response_code(200);
            echo json_encode(['total_estimated_hour' => $result['total_estimated_hour'],'project_estimated_hour' => $result2['estimated_hour'],'ava_time' =>  $result2['estimated_hour'] - $result['total_estimated_hour']]);
        }
    }else {
        http_response_code(404);
        echo json_encode(array("message" => 'No projects found with project_id ' . $project_id, "status" => 404));
    }
}




?>