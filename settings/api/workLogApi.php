<?php

session_start();
date_default_timezone_set('Asia/Kolkata'); 
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];
$current_time = date("H:i:s");
$currentDate = date('Y-m-d');
$currentDateTime = date('Y-m-d H:i:s');

function useBreak($conn, $user_id , $task_id){
    $updateBreak = $conn->prepare("UPDATE `break` SET `logged` = 1 WHERE `user_id` = ? AND `task_id` = ? ");
    $updateBreak->execute(array($user_id,$task_id));
}

function qaEfficiencyAdd($conn ,$user_id , $task_id , $project_id, $type){
    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
    $task->execute([$task_id]);
    $task = $task->fetch(PDO::FETCH_ASSOC);

    $project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
    $project->execute([$task['project_id']]);
    $project = $project->fetch(PDO::FETCH_ASSOC);

    if($project['vector'] == 1){
        $task_estimated_hour = ($task['estimated_hour']) * (0.02);
    }else{
        $task_estimated_hour = ($task['estimated_hour']) * (0.05);
    }


    if($type == "QacompleteTask"){
        $check_assign = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qa'  ORDER BY `id` DESC");
        $check_assign->execute([$task_id , $project_id]);
        $check_assign = $check_assign->fetch(PDO::FETCH_ASSOC);
        if($check_assign){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = 'qa_in_progress' AND `project_id` = ? AND  `id` > ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calcute as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
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

    if($type == "VectorcompleteTask"){
        $task_estimated_hour = ($task['estimated_hour']) * (0.03);
        $check_assign = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_vector'  ORDER BY `id` DESC");
        $check_assign->execute([$task_id , $project_id]);
        $check_assign = $check_assign->fetch(PDO::FETCH_ASSOC);
        if($check_assign){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = 'vector_in_progress' AND `project_id` = ? AND  `id` > ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calcute as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,'vector', $efficiency]);
        }
    }

    

}
function qcEfficiencyAdd($conn ,$user_id , $task_id , $project_id, $type){
    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
    $task->execute([$task_id]);
    $task = $task->fetch(PDO::FETCH_ASSOC);

    if($task['is_qa_failed'] == 1){
        $task_estimated_hour = ($task['estimated_hour']) * (0.10);
    }else{
        $task_estimated_hour = ($task['estimated_hour']) * (0.20);
    }

    if($type == "QccompleteTask"){
        $check_assign = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qc'  ORDER BY `id` DESC");
        $check_assign->execute([$task_id , $project_id]);
        $check_assign = $check_assign->fetch(PDO::FETCH_ASSOC);
        if($check_assign){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = 'qc_in_progress' AND `project_id` = ? AND  `id` > ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calcute as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,'qc', $efficiency]);
        }
    }

    if($type == "reassignQccompleteTask"){
        $check_previous_assign_user = $conn->prepare("SELECT * FROM `assign` WHERE `role` = 'qc' AND `task_id` = ? AND `project_id` = ? AND `user_id` = ?");
        $check_previous_assign_user->execute([$task_id, $project_id, $user_id]);
        $check_previous_assign_user = $check_previous_assign_user->fetchAll(PDO::FETCH_ASSOC);
        if(count($check_previous_assign_user) == 2){
            $check_previous_assign_user = $check_previous_assign_user[0];
            $logs = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `prev_status` = 'qc_in_progress' AND `change_type` IS NULL;");
            $logs->execute([$user_id,$task_id]); 
            $logs = $logs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($logs as $entry) {
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                // $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;            
                
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                
            $percentage_hour = ($task_estimated_hour * 100)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;
            
            $efficiencySql = $conn->prepare("UPDATE `efficiency` SET `efficiency` = ? WHERE `user_id` = ? AND `task_id` = ?  AND `project_id` = ? AND `profile` = 'qc'");
            $efficiencySql->execute([$efficiency , $user_id , $task_id , $project_id]);
        }else{
            $check_log = $conn->prepare("SELECT * FROM `work_log` WHERE `change_type` = 'qa_failure_ressignment' AND `task_id` = ? AND `project_id` = ? ORDER BY `id` DESC;");
            $check_log->execute([$task_id, $project_id]);
            $check_log = $check_log->fetch(PDO::FETCH_ASSOC);
            $logs = $conn->prepare("SELECT * FROM `work_log` WHERE `prev_status` = 'qc_in_progress' AND `task_id` = ? AND `project_id` = ? AND `id` > ?");
            $logs->execute([$task_id, $project_id, $check_log['id']]);
            $logs = $logs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($logs as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }

            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $check_effinciny = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `project_id` = ? AND `profile` = 'qc'");
            $check_effinciny->execute([$task_id , $project_id ]);
            $check_effinciny = $check_effinciny->fetch(PDO::FETCH_ASSOC);

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,'qc', $efficiency + $check_effinciny['efficiency']]);

        }
    }
}
function efficiencyAdd($conn ,$user_id , $task_id , $project_id, $type){

    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
    $task->execute([$task_id]);
    $task = $task->fetch(PDO::FETCH_ASSOC);

    $project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
    $project->execute([$task['project_id']]);
    $project = $project->fetch(PDO::FETCH_ASSOC);
    
    if(($task['status'] == 'qc_in_progress') || ($task['status'] == 'ready_for_qa')){
        $profile = 'qc';
        $task_estimated_hour = ($task['estimated_hour']) * (0.20);
    }else if(($task['status'] == 'qa_in_progress') || ($task['status'] == 'ready_for_vector') || ($task['status'] == 'complete')){
        $profile = 'qa';

        if($project['vector'] == 1){
            $task_estimated_hour = ($task['estimated_hour']) * (0.02);
        }else{
            $task_estimated_hour = ($task['estimated_hour']) * (0.05);
        }

    }else{
        $profile = 'employee';
        if($task['is_qc_failed'] == 1){
            $task_estimated_hour = ($task['estimated_hour']) * (0.375);
        }else{
            $task_estimated_hour = ($task['estimated_hour']) * (0.75);
        }
    }

    $check_assign = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `change_type` = 'ressigned' ORDER BY `id` DESC");
    $check_assign->execute([$task_id , $project_id]);
    $check_assign = $check_assign->fetch(PDO::FETCH_ASSOC);
    

    if($type ==  'reAssign'){
        if($check_assign){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` < ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);
            foreach ($calcute as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,$profile, $efficiency]);
        }

    }
    
    if($type == "completeTask"){
        // if($check_assign){
            if(!$check_assign){
                $check_assign['id'] = 0;
            }
            
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
            $calcute->execute([$task_id , $project_id ,$check_assign['id']]);
            $calcute = $calcute->fetchAll(PDO::FETCH_ASSOC);
            foreach ($calcute as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;
            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;

            if ($total_hours != 0) {
                $efficiency = ($percentage_hour / $total_hours)*100;
            }else{
                $efficiency = 0;
            }

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,$profile, $efficiency]);
    }

    if($type == "failure_Task"){
        
        $check_effinciny = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `project_id` = ? AND `profile` = ? ");
        $check_effinciny->execute([$task_id,  $project_id , 'employee']);
        $check_effinciny = $check_effinciny->fetchAll(PDO::FETCH_ASSOC);
        if($check_effinciny){
            foreach ($check_effinciny as $value) {
                $updateEfficincy = $conn->prepare("UPDATE `efficiency` SET `efficiency`= ? WHERE `task_id` = ? AND `project_id` = ? AND `profile` = ? AND `user_id` = ?");
                $updateEfficincy->execute([$value['efficiency']/2,$task_id,  $project_id , 'employee' ,$value['user_id']]);
            }
        }

        $sql = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qc' ORDER BY `id` DESC");
        $sql->execute([$task_id,$project_id]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if($sql){
            $calcute = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'qc_in_progress' AND `id` > ?;");
            $calcute->execute([$task_id , $project_id , $sql['id']]);
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

            if($task['status'] == 'ready'){
                $profile = 'qc';
            }else if($task['status'] == 'ready_for_qc'){
                $profile = 'qa';
            }

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,$profile, $efficiency]);
        }
    }

    if($type == "completeReassignedTask"){

        $check_previous_assign_user = $conn->prepare("SELECT * FROM `assign` WHERE `role` = 'employee' AND `task_id` = ? AND `project_id` = ? AND `user_id` = ?");
        $check_previous_assign_user->execute([$task_id, $project_id, $user_id]);
        $check_previous_assign_user = $check_previous_assign_user->fetchAll(PDO::FETCH_ASSOC);
        if(count($check_previous_assign_user) == 2){
            $check_previous_assign_user = $check_previous_assign_user[0];
            $logs = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `prev_status` = 'in_progress' AND `change_type` IS NULL;");
            $logs->execute([$user_id,$task_id]); 
            $logs = $logs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($logs as $entry) {
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                // $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
                
                
            }
            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            $task_estimated_hour = ($task['estimated_hour']) * (0.75);
                
            $percentage_hour = ($task_estimated_hour * 100)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;
            
            $efficiencySql = $conn->prepare("UPDATE `efficiency` SET `efficiency` = ? WHERE `user_id` = ? AND `task_id` = ?  AND `project_id` = ? AND `profile` = 'employee'");
            $efficiencySql->execute([$efficiency , $user_id , $task_id , $project_id]);

            
        }else{
            $check_log = $conn->prepare("SELECT * FROM `work_log` WHERE `change_type` = 'qc_failure_ressignment' AND `task_id` = ? AND `project_id` = ? ORDER BY `id` DESC;");
            $check_log->execute([$task_id, $project_id]);
            $check_log = $check_log->fetch(PDO::FETCH_ASSOC);
            $logs = $conn->prepare("SELECT * FROM `work_log` WHERE `prev_status` = 'in_progress' AND `task_id` = ? AND `project_id` = ? AND `id` > ?");
            $logs->execute([$task_id, $project_id, $check_log['id']]);
            $logs = $logs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($logs as $entry) {
                if($entry['change_type']){
                    break;
                }
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
            }

            $extra_hours = floor($total_minutes / 60);
            $total_hours += $extra_hours;
            $total_minutes %= 60;
            $total_hours += $total_minutes/60;

            $percentage_hour = ($task_estimated_hour * $total_percentage)/100;
            $efficiency = ($percentage_hour / $total_hours)*100;

            $check_effinciny = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `project_id` = ? AND `profile` = 'employee'");
            $check_effinciny->execute([$task_id , $project_id ]);
            $check_effinciny = $check_effinciny->fetch(PDO::FETCH_ASSOC);

            $efficiencySql = $conn->prepare("INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)");
            $efficiencySql->execute([$user_id , $task_id , $project_id ,$profile, $efficiency + $check_effinciny['efficiency']]);

        }       
    }

}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'nextStatus')) {
    if($_GET['task_id']){
        $sql = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
        $sql->execute([$_GET['task_id']]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            switch ($result['status']) {
                case 'pending':
                    $nextStatus = 'ready';
                    break;

                case 'ready':
                    $nextStatus = 'in_progress';
                    break;

                case 'in_progress':
                    $nextStatus = 'ready_for_qc';
                    break;
                
                case 'ready_for_qc':
                    $nextStatus = 'assign_qc';
                    break;
                case 'assign_qc':
                    $nextStatus = 'qc_in_progress';
                    break;
                case 'qc_in_progress':
                    $nextStatus = 'ready_for_qa';
                    break;
                case 'ready_for_qa':
                    $nextStatus = 'assign_qa';
                    break;
                case 'assign_qa':
                    $nextStatus = 'qa_in_progress';
                    break;
                case 'qa_in_progress':
                    $sql2 = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ? AND `vector` = 1");
                    $sql2->execute([$result['project_id']]);
                    $project = $sql2->fetch(PDO::FETCH_ASSOC);

                    if($project){
                        $nextStatus = 'ready_for_vector';
                    }else{
                        $nextStatus = 'complete';
                    }

                    break;
                case 'ready_for_vector':
                    $nextStatus = 'complete';
                    break;
            }
            http_response_code(200);
            echo json_encode(["nextStatus" => $nextStatus, "prevStatus" => $result['status']]);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => 'No task found', "status" => 404));
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addLogWork')) {

    $time = $current_time;
    $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `date`, `time`, `prev_status`, `next_status`, `remarks`) VALUES (? , ? , ? , ? , ? , ?, ? , ?)');
    $result = $sql->execute([$user_id, $_POST['task_id'],$_POST['project_id'], $currentDate, $time, $_POST['prev_status'], $_POST['next_status'], $_POST['remarks']]);
    if($result){
        $sql2 = $conn->prepare('UPDATE `tasks` SET `status` = ? WHERE `task_id` = ?');
        $result2 = $sql2->execute([$_POST['next_status'],$_POST['task_id']]);

        if(($_POST['next_status'] == 'ready_for_qa')||($_POST['next_status'] == 'ready_for_qc')){
            $sql3 = $conn->prepare("UPDATE `assign` SET `status` = 'complete' , `updated_at` = ? WHERE `task_id` = ?");
            $result3 = $sql3->execute([$currentDateTime , $_POST['task_id']]);
        }

        http_response_code(200);
        echo json_encode(array("message" => 'Status Change Successfull', "status" => 200 , "next_status" => $_POST['next_status']));
    }else{
        http_response_code(500);
        echo json_encode(array("message" => 'Something went worrg', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addPauseLogWork')) {

    $time = $current_time;

    $sql3 = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
    $sql3->execute([$_POST['task_id']]);
    $tasks = $sql3->fetch(PDO::FETCH_ASSOC);

    $prev_status = $tasks['status'];

    if($tasks['status'] == 'qc_in_progress'){
        $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qc' ORDER BY `id` DESC;");
        $checkQC->execute([$_POST['task_id'],$_POST['project_id']]);
        $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
        if($checkQC){
          $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ? AND `change_type` IS NULL");
          $totalWorkPercentage->execute([$_POST['task_id'],$_POST['project_id'],$checkQC['id']]);
          $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
        }
  
    }else if($tasks['status'] == 'qa_in_progress'){
        $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qa' ORDER BY `id` DESC;");
        $checkQC->execute([$_POST['task_id'],$_POST['project_id']]);
        $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
        if($checkQC){
          $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ? AND `change_type` IS NULL");
          $totalWorkPercentage->execute([$_POST['task_id'],$_POST['project_id'],$checkQC['id']]);
          $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
        }
  
    }else if($tasks['status'] == 'in_progress'){
          $chech_work_log = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND (change_type = 'qc_failure_ressignment' OR change_type = 'qa_failure_ressignment') ORDER BY `id` DESC");
          $chech_work_log->execute([$_POST['task_id'],$_POST['project_id']]);
          $chech_work_log = $chech_work_log->fetch(PDO::FETCH_ASSOC);
      
          if(!$chech_work_log){
              $chech_work_log['id'] = 0;
          }
          $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
          $totalWorkPercentage->execute([$_POST['task_id'],$_POST['project_id'],$chech_work_log['id']]);
          $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
    }else if($tasks['status'] == 'vector_in_progress'){
        $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_vector' ORDER BY `id` DESC;");
        $checkQC->execute([$_POST['task_id'],$_POST['project_id']]);
        $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
        if($checkQC){
          $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ? AND `change_type` IS NULL");
          $totalWorkPercentage->execute([$_POST['task_id'],$_POST['project_id'],$checkQC['id']]);
          $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
        }
    }
        
        
        
    $taken_time = $_POST['hour'].'H '.$_POST['minute'].'M';
    if($_POST['work_percentage'] == 100){
        switch ($_POST['status']) {
        
            case 'in_progress':
                $next_status = 'ready_for_qc';
                break;
            
            case 'qc_in_progress':
                $next_status = 'ready_for_qa';
                break;
                
            case 'qa_in_progress':
                $sql2 = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ? AND `vector` = 1");
                $sql2->execute([$tasks['project_id']]);
                $project = $sql2->fetch(PDO::FETCH_ASSOC);

                if($project['vector'] == 1){
                    $next_status = 'ready_for_vector';
                }else{
                    $next_status = 'complete';
                }

                break;
            case 'vector_in_progress':
                $next_status = 'complete';
                break;
        }

        $sql = $conn->prepare("UPDATE `tasks` SET  `status` = ?  WHERE `task_id` = ?");
        $result = $sql->execute([$next_status,$_POST['task_id']]);
        
        $sql = $conn->prepare("UPDATE `assign` SET  `status` = 'complete' ,  `updated_at` = ?  WHERE `task_id` = ? AND `user_id` = ? AND `isActive` = 1");
        $result = $sql->execute([$currentDateTime , $_POST['task_id'],$user_id]);
    }else{
        $next_status = $_POST['status'];
    }

    $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `date`, `time`, `prev_status`, `next_status`, `remarks` , `work_percentage`,`taken_time`) VALUES (? , ? , ? , ? , ? , ?, ? , ? ,? , ?)');
    $result = $sql->execute([$user_id, $_POST['task_id'],$_POST['project_id'], $currentDate, $time, $_POST['status'], $next_status, $_POST['remarks'] , $_POST['work_percentage'] - $totalWorkPercentage['total_percentage'],$taken_time]);

    useBreak($conn,$user_id,$_POST['task_id']);

    if(($_POST['work_percentage'] == 100) && ($tasks['is_qc_failed'] == 0)){
         //  add eficincy in table
        if($next_status == 'ready_for_qa'){
            if($tasks['is_qa_failed'] == 1){
                qcEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"reassignQccompleteTask");
            }else{
                qcEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"QccompleteTask");
            }
        }else if(($next_status == 'ready_for_vector') || ($next_status == 'complete')){
            if($prev_status == 'vector_in_progress'){
                qaEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"VectorcompleteTask");
            }else{
                qaEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"QacompleteTask");
            }

        }else if($next_status == 'ready_for_qc'){
            efficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"completeTask");
        }
         // done
    }

    if(($_POST['work_percentage'] == 100) && ($tasks['is_qc_failed'] == 1)){
        if($next_status == 'ready_for_qa'){
            qcEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"QccompleteTask");
        }else if(($next_status == 'ready_for_vector') || ($next_status == 'complete')){
            if($prev_status == 'vector_in_progress'){  
                qaEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"VectorcompleteTask");
            }else{
                qaEfficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"QacompleteTask");
            }
        }else{
            efficiencyAdd($conn,$user_id,$_POST['task_id'],$_POST['project_id'],"completeReassignedTask");
        }
    }

    if($result){
        http_response_code(200);
        echo json_encode(array("message" => 'Add Log Successfull', "status" => 200));
    }else{
        http_response_code(500);
        echo json_encode(array("message" => 'Something went worrg', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'reAssignTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['user_id'] != '')){
        
        $assign = $conn->prepare('SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `isActive` = 1');
        $assign->execute([$_POST['task_id'],$_POST['project_id'] ]);
        $assign = $assign->fetch(PDO::FETCH_ASSOC);
        
        $checkTask = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
        $checkTask->execute([$_POST['task_id']]);
        $checkTask = $checkTask->fetch(PDO::FETCH_ASSOC);
        
        if($assign['user_id'] == $_POST['user_id']){
            http_response_code(400);
            echo json_encode(array("message" => "This User already working on this Task", "status" => 400));
            exit;
        }
        
        if($assign){
            $past_assigned_user = $assign['user_id'];

            $check = $conn->prepare("UPDATE `assign` SET `isActive`= 0 , `status` = 'complete' ,  `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
            $result = $check->execute([$currentDateTime, $_POST['task_id'],$_POST['project_id']]);
            if($result){
                $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], $assign['role'], "assign"]);
                if($result){
                    if($checkTask['status'] != 'ready'){
                        $task = $conn->prepare("UPDATE `tasks` SET `is_reassigned`= 1 WHERE `task_id` = ? AND `project_id` = ?");
                        $task->execute([$_POST['task_id'],$_POST['project_id']]);

                        $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`) VALUES (? , ? , ? , 'in_progress' , 'in_progress' , 'assign'  ,'ressigned')");
                        $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id']]);
                        
                    }

                    if($checkTask['status'] != 'ready'){
                        efficiencyAdd($conn,$past_assigned_user,$_POST['task_id'],$_POST['project_id'],"reAssign");
                    }

                    http_response_code(200);
                    echo json_encode(array("message" => 'Re-Assign Task successfull.', "status" => 200));
                }else{
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went wrong', "status" => 500));
                }
            }

        }
    }else{
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'failureTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['user_id'] != '')){

        $taken_time = $_POST['hour'].'H '.$_POST['minute'].'M';
        $task =  $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
        $task->execute([$_POST['task_id'],$_POST['project_id'] ]);
        $task = $task->fetch(PDO::FETCH_ASSOC);
        if($task){

            
            $roll = '';
            switch ($task['status']) {
                case 'qa_in_progress':
                    $roll = 'qa';
                    break;
                    
                case 'qc_in_progress':
                    $roll = 'qc';
                    break;
            }

            $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
            $chech_assign->execute([$_POST['task_id'],$_POST['project_id'],$roll]);
            $chech_assign = $chech_assign->fetch(PDO::FETCH_ASSOC);
            if($chech_assign){

                

                $assign = $conn->prepare("UPDATE `assign` SET `status` = 'complete' ,  `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
                $assign = $assign->execute([$currentDateTime , $_POST['task_id'],$_POST['project_id'],$roll]);

                if($assign){
                    $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                    $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], "employee", "assign"]);
                    if($result){
                        if($roll == 'qc'){
                            $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? , `is_qc_failed` = 1  WHERE `task_id` = ? AND `project_id` = ?');
                            $result2 = $sql->execute(["ready",$_POST['task_id'],$_POST['project_id']]);
                            
                            $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`,`taken_time` , `work_percentage`) VALUES (? , ? , ? , 'qc_in_progress' , 'qc_in_progress' , 'assign'  ,'qc_failure_ressignment', ? , ?)");
                            $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id'],$taken_time , '100']);
                            useBreak($conn,$user_id,$_POST['task_id']);
                        }     
                        
                        efficiencyAdd($conn,$chech_assign['user_id'],$_POST['task_id'],$_POST['project_id'],"failure_Task");

                        http_response_code(200);
                        echo json_encode(array("message" => 'Re-Assign Task successfull.', "status" => 200));
                    }else{
                        http_response_code(500);
                        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
                    }
                }
            }


        }else{
            http_response_code(400);
            echo json_encode(array("message" => "Task not found", "status" => 400));
        }
    }else{
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

?>