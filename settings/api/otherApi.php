<?php
session_start();
include '../config/config.php';
$user_id = $_SESSION['userId'];
date_default_timezone_set('Asia/Kolkata');
$currentDateTime = date('Y-m-d H:i:s');
header("content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getLastLog')) {

    if ($_GET['task_id'] && $_GET['project_id']) {
        $sql = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? ORDER BY `id` DESC");
        $sql->execute([$_GET['task_id'],$_GET['project_id']]);
        $data = $sql->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $givenTimestamp = strtotime($data['updated_at']);
            $currentTimestamp = time();
            $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
            $hours = floor($timeDifferenceInSeconds / 3600);
            $minutes = floor(($timeDifferenceInSeconds % 3600) / 60);

            $checkBreak = $conn->prepare("SELECT SUM(time) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `logged` = 0");
            $checkBreak->execute([$user_id , $_GET['task_id']]);
            $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
            if($checkBreak['total_time']){
                $total_minutes = $hours * 60 + $minutes;
                $total_minutes = $total_minutes - $checkBreak['total_time'];
                $hours = floor($total_minutes / 60);
                $minutes = $total_minutes % 60;
            }

            http_response_code(200);
            echo json_encode(["hour" => $hours, "minutes" => $minutes]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Something went wrong", "error" => 500]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "task id is required", "error" => 404]);
    }

}

if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'getContinueWork')){
    if ($_POST['task_id'] && $_POST['project_id'] && $_POST['pause_id']) {
        $contionues = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `next_status` = 'Pause Work' AND `id` = ?");
        $contionues->execute([$_POST['task_id'],$_POST['project_id'],$_POST['pause_id']]);
        $contionues = $contionues->fetch(PDO::FETCH_ASSOC);
        if($contionues){
            $getLastLog = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ? AND `next_status` = ? AND `id` < ? ORDER BY `work_log`.`id` DESC;");
            $getLastLog->execute([$_POST['task_id'],$_POST['project_id'],$contionues['prev_status'],$contionues['prev_status'],$_POST['pause_id']]);
            $getLastLog = $getLastLog->fetch(PDO::FETCH_ASSOC);
            if($getLastLog){
                $updateLog = $conn->prepare("UPDATE `work_log` SET `updated_at` = ? WHERE `id` = ? AND `task_id` = ? AND `project_id` = ?");
                $result = $updateLog->execute([$currentDateTime,$getLastLog['id'],$_POST['task_id'],$_POST['project_id']]);
                if($result){
                    http_response_code(200);
                    echo json_encode(["message" => "Work Start Continue..","status" => 200]);
                    $delete = $conn->prepare("DELETE FROM `work_log` WHERE `id` = ?");
                    $delete->execute([$_POST['pause_id']]);
                }else{
                    http_response_code(500);
                    echo json_encode(["message" => "Something went wrong","status" => 500]);
                }
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Last log not found","status" => 400]);
            }
        }else{
            http_response_code(400);
            echo json_encode(["message" => "Work Pause not found","status" => 400]);
        }
    }
}

?>