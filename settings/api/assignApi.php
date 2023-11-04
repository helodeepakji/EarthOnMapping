<?php

include '../config/config.php';
date_default_timezone_set('Asia/Kolkata');
$currentDateTime = date('Y-m-d H:i:s');
header("content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addAssign')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['role'] != '') && ($_POST['user_id'] != '')){

        $conn->beginTransaction();
        try {
            $project = $_POST['project_id'];
            $task = $_POST['task_id'];
            foreach ($task as $key => $value) {
                $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                $result = $check->execute([$_POST['user_id'], $project[$key] ,$value,$_POST['role'], "assign"]);
                if($result){
                    if($_POST['role'] == 'qa'){
                        $status = 'assign_qa';
                    }else if($_POST['role'] == 'qc'){
                        $status = 'assign_qc';
                    }else if($_POST['role'] == 'employee'){
                        $status = 'ready';
                    }else if($_POST['role'] == 'vector'){
                        $status = 'assign_vector';
                    }
        
                    $sql2 = $conn->prepare('UPDATE `tasks` SET `status` = ? , `update_at` = ? WHERE `task_id` = ?');
                    $result2 = $sql2->execute([$status ,$currentDateTime , $value]);

                }else{
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went wrong', "status" => 500));
                }
            }

            if($result2){
                $conn->commit();
                http_response_code(200);
                echo json_encode(array("message" => 'Assign Task successfull...', "status" => 200));
            }else{
                $conn->rollback();
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }


        } catch (PDOException $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong", "status" => 500));
        }


    }else{
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'reAssignTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['user_id'] != '')){
        
        $assign = $conn->prepare('SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `isActive` = 1');
        $assign->execute([$_POST['task_id'],$_POST['project_id'] ]);
        $assign = $assign->fetch(PDO::FETCH_ASSOC);

        if($assign){

            $check = $conn->prepare("UPDATE `assign` SET `isActive`= 0 , `status` = 'complete' WHERE `task_id` = ? AND `project_id` = ?");
            $result = $check->execute([$_POST['task_id'],$_POST['project_id']]);
            if($result){
                $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], $assign['role'], "assign"]);
                if($result){
                    $task = $conn->prepare("UPDATE `tasks` SET `is_reassigned`= 1 , `update_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
                    $task->execute([$currentDateTime , $_POST['task_id'] , $_POST['project_id']]);

                    $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`) VALUES (? , ? , ? , 'in_progress' , 'in_progress' , 'assign'  ,'ressigned')");
                    $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id']]);

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

            $assign = $conn->prepare("UPDATE `assign` SET `status` = 'complete' WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
            $assign = $assign->execute([$_POST['task_id'],$_POST['project_id'],$roll]);

            if($assign){
                $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status`) VALUES (? , ? , ? , ? , ?)');
                $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], "employee", "assign"]);
                if($result){
                    if($roll == 'qc'){
                        $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? , `update_at` = ? , `is_qc_failed` = 1  WHERE `task_id` = ? AND `project_id` = ?');
                        $result2 = $sql->execute(["ready", $currentDateTime ,$_POST['task_id'],$_POST['project_id']]);
                        
                        $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`) VALUES (? , ? , ? , 'in_progress' , 'in_progress' , 'assign'  ,'qc_failure_ressignment')");
                        $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id']]);
                    }else{
                        $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? ,`update_at` = ? , `is_qa_failed` = 1  WHERE `task_id` = ? AND `project_id` = ?');
                        $result2 = $sql->execute(["ready", $currentDateTime ,$_POST['task_id'],$_POST['project_id']]);

                        $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`) VALUES (? , ? , ? , 'in_progress' , 'in_progress' , 'assign'  ,'qa_failure_ressignment')");
                        $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id']]);
                    }
                    

                    http_response_code(200);
                    echo json_encode(array("message" => 'Re-Assign Task successfull.', "status" => 200));
                }else{
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went wrong', "status" => 500));
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