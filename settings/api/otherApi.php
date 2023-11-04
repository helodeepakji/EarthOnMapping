<?php
session_start();
include '../config/config.php';
$user_id = $_SESSION['userId'];
date_default_timezone_set('Asia/Kolkata');
header("content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getLastLog')) {

    if ($_GET['task_id']) {
        $sql = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? ORDER BY `id` DESC");
        $sql->execute([$_GET['task_id']]);
        $data = $sql->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $givenTimestamp = strtotime($data['created_it']);
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

?>