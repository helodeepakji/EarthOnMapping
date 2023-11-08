<?php

include '../config/config.php';
date_default_timezone_set('Asia/Kolkata');
$currentDateTime = date('Y-m-d H:i:s');
header("content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addAssign')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['role'] != '') && ($_POST['user_id'] != '')) {

        $conn->beginTransaction();
        try {
            $project = $_POST['project_id'];
            $task = $_POST['task_id'];
            foreach ($task as $key => $value) {
                $sql2 = $conn->prepare('DELETE FROM `assign` WHERE `role` = ? AND `status` = ? AND `task_id` = ?');
                $result = $sql2->execute([$_POST['role'], "assign" , $value]);
                if ($result) {
                    
                    $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                    $result2 = $check->execute([$_POST['user_id'], $project[$key], $value, $_POST['role'], "assign"]);

                } else {
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went wrong', "status" => 500));
                }
            }

            if ($result2) {
                $conn->commit();
                http_response_code(200);
                echo json_encode(array("message" => 'Assign Task successfull...', "status" => 200));
            } else {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }


        } catch (PDOException $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong", "status" => 500));
        }


    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}


?>