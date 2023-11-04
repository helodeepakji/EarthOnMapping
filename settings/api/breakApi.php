<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['project_id'] != '') && ($_POST['task_id'] != '') && ($_POST['time'] != '')) {

        $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
        $result = $sql->execute([$user_id, $_POST['task_id'], $_POST['project_id'], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $_POST['time'], $_POST['remarks']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $_POST['time']));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went worrg', "status" => 500));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addVectorBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'vector' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $augTime = floor($time/count($chech_assign));

            foreach ($chech_assign as $data) {
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $data['task_id'], $data['project_id'], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $augTime , $_POST['remarks']]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $augTime));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addQaBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'qa' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $augTime = floor($time/count($chech_assign));
            foreach ($chech_assign as $data) {
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $data['task_id'], $data['project_id'], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $augTime , $_POST['remarks']]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $augTime ));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}



?>