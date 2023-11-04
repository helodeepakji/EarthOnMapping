<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];
$current_time = date("H:i:s");
$currentDate = date('Y-m-d');

function useBreak($conn, $user_id , $task_id){
    $updateBreak = $conn->prepare("UPDATE `break` SET `logged` = 1 WHERE `user_id` = ? AND `task_id` = ?");
    $updateBreak->execute(array($user_id,$task_id));
}

function qaEfficiencyAdd($conn ,$user_id , $task_id , $project_id, $type){
    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
    $task->execute([$task_id]);
    $task = $task->fetch(PDO::FETCH_ASSOC);

    $task_estimated_hour = intval($task['estimated_hour']) * (0.05);

    if($type == "addQaEfficiency"){

        $check_assign_user = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `project_id` = ? AND `profile` = 'qc' ORDER BY `id` DESC");
        $check_assign_user->execute([$task_id , $project_id]);
        $check_assign_user = $check_assign_user->fetch(PDO::FETCH_ASSOC);

        if($check_assign_user){
            $update_assign = $conn->prepare("UPDATE `efficiency` SET `efficiency` = ? WHERE `task_id` = ? AND `project_id` = ? AND `profile` = 'qc' AND `user_id` = ?");
            $update_assign->execute([$check_assign_user['efficiency']/2,$task_id , $project_id, $check_assign_user['user_id']]);
        }

        $check_assign = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qa'  ORDER BY `id` DESC");
        $check_assign->execute([$task_id , $project_id]);
        $check_assign = $check_assign->fetch(PDO::FETCH_ASSOC);

        if($check_assign){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = 'qa_in_progress' AND `project_id` = ? AND  `id` > ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calcute as $entry) {
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
                
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
                if($entry['change_type']){
                    break;
                }
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,'qa', $efficiency]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'failureTask')) {

    $taken_time = $_POST['hour'].'H '.$_POST['minute'].'M';
    $task =  $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
    $task->execute([$_POST['task_id'],$_POST['project_id'] ]);
    $task = $task->fetch(PDO::FETCH_ASSOC);
    if($task){

        $assign = $conn->prepare("UPDATE `assign` SET `status` = 'complete' WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
        $assign = $assign->execute([$_POST['task_id'],$_POST['project_id'],'qa']);

        if($assign){
            $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
            $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], "qc", "assign"]);

            $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? , `is_qa_failed` = 1  WHERE `task_id` = ? AND `project_id` = ?');
            $result2 = $sql->execute(["assign_qc",$_POST['task_id'],$_POST['project_id']]);

            $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type` ,`taken_time` , `work_percentage`) VALUES (? , ? , ? , 'qa_in_progress' , 'qa_in_progress' , 'qa assign'  ,'qa_failure_ressignment' , ? , ?)");
            $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id'],$taken_time , '100']);

            useBreak($conn, $user_id , $_POST['task_id']);

            qaEfficiencyAdd($conn, $user_id , $_POST['task_id'],$_POST['project_id'] , "addQaEfficiency");

        }

        http_response_code(200);
        echo json_encode(array("message" => 'Re-Assign Task successfull.', "status" => 200));
    }else{
        http_response_code(400);
        echo json_encode(array("message" => 'Task not found.', "status" => 400));
    }
}

?>