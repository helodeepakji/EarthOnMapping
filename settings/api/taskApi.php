<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];
$current_time = date("H:i:s");
$current_date = date("Y-m-d");
$currentDateTime = date("Y-m-d H:i:s");


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['area_sqkm'] != '') && ($_POST['estimated_hour'] != '') && ($_POST['complexity'] != '')) {


        if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
            $attachment = basename($_FILES['attachment']['name']);
            $uploadPath = '../../upload/attachment/' . $attachment;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
        }else{
            $attachment = null;
        }

        $project = array($_POST['task_id'], $_POST['project_id'] , $_POST['estimated_hour'] , $_POST['area_sqkm'] ,$_POST['complexity'] , $attachment ,$_POST['start_date'], $_POST['end_date'] );
        
        $check = $conn->prepare('INSERT INTO `tasks` (`task_id`, `project_id` ,`estimated_hour`, `area_sqkm`, `complexity`, `attachment`,`start_date`, `end_date`) VALUES (? ,? , ? , ? , ? , ? , ? , ?)');
        $result = $check->execute($project);

        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull task Added...', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'changeSummary')) {

    if (($_POST['task_id'] != '') && ($_POST['summary'] != '')) {

        
        $check = $conn->prepare("UPDATE `tasks` SET  `summary` = ? WHERE `task_id` = ?");
        $result = $check->execute([$_POST['summary'],$_POST['task_id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull task Added...', "status" => 200 ,"summary" => $_POST['summary']));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getAllTask')) {
    $sql = $conn->prepare('SELECT * FROM `tasks`');
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

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'inProgress')) {
    $sql = $conn->prepare('SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `user_id` = ?');
    $result = $sql->execute([$_GET['task_id'],$_GET['project_id'],$user_id]);
    if ($result) {
        $sql = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
        $sql->execute([$_GET['task_id'],$_GET['project_id']]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        switch ($result['status']) {
            case 'ready':
                $status = 'in_progress';
                $remarks = "In Progress By Employee";
                break;
            case 'assign_qa':
                $status = 'qa_in_progress';
                $remarks = "In Progress By QA";
                break;
            case 'assign_qc':
                $status = 'qc_in_progress';
                $remarks = "In Progress By QC";
                break;
            case 'assign_vector':
                $status = 'vector_in_progress';
                $remarks = "In Progress By Vector";
                break;
            
        }
        $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? , `update_at` = ? WHERE `task_id` = ? AND `project_id` = ?');
        $result2 = $sql->execute([$status, $currentDateTime ,$_GET['task_id'],$_GET['project_id']]);

        $sql3 = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `date`, `time`, `prev_status`, `next_status`, `remarks`) VALUES (? , ? , ? , ? , ? , ?, ? , ?)');
        $result3 = $sql3->execute([$user_id,$_GET['task_id'],$_GET['project_id'],$current_date,$current_time,$result['status'],$status,$remarks]);

        http_response_code(200);
        echo json_encode(array("message" => 'Successfull in progress', "status" => 200, "next_status" => $status));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'No task found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getTask') {
    $sql = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getTaskByProjectId') {
    $sql = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ?");
    $sql->execute([$_GET['project_id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
    http_response_code(200);
    echo json_encode($result);
    } else {
    http_response_code(404);
    echo json_encode(array("message" => 'No task found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'deleteTask') {
    $sql = $conn->prepare("DELETE FROM `tasks` WHERE `task_id` = ?");
    $result = $sql->execute([$_POST['task_id']]);
    if ($result) {
        $sql = $conn->prepare("DELETE FROM `assign` WHERE `task_id` = ?");
        $result = $sql->execute([$_POST['task_id']]);
        
        $sql = $conn->prepare("DELETE FROM `comments` WHERE `task_id` = ?");
        $result = $sql->execute([$_POST['task_id']]);
        
        $sql = $conn->prepare("DELETE FROM `work_log` WHERE `task_id` = ?");
        $result = $sql->execute([$_POST['task_id']]);

        http_response_code(200);
        echo json_encode(array("message" => 'Delete task successfull...', "status" => 404));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'updateTask') {

    $complexity = $_POST['complexity'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $task_id = $_POST['task_id'];
    $project_id = $_POST['project_id'];

    foreach($task_id as $key => $value){
        if($start_date){
            $startDate = date('Y-m-d', strtotime($start_date[$key]));
        }
        if($endDate){
            $endDate = date('Y-m-d', strtotime($end_date[$key]));
        }

        $sql = $conn->prepare("UPDATE `tasks` SET `complexity` = ?, `start_date` = ?, `end_date` = ? WHERE `task_id` = ?");
        $result = $sql->execute([$complexity[$key], $startDate, $endDate , $task_id[$key]]);
    }

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Update task successful...', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 500));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'UpdateTask') {
    
    $complexity = $_POST['complexity'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $task_id = $_POST['task_id'];
    $area_sqkm = $_POST['area_sqkm'];
    $project_id = $_POST['project_id'];
    $estimated_hour = $_POST['estimated_hour'];


    $sql = $conn->prepare("UPDATE `tasks` SET  `project_id` = ?,`area_sqkm` = ? , `complexity` = ?, `start_date` = ? , `end_date` = ? ,`estimated_hour` = ? WHERE `task_id` = ?");
    $result = $sql->execute([$project_id, $area_sqkm ,$complexity, $start_date, $end_date, $estimated_hour ,$task_id]);

    if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
        $attachment = basename($_FILES['attachment']['name']);
        $uploadPath = '../../upload/attachment/' . $attachment;
        move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
        $sql = $conn->prepare("UPDATE `tasks` SET  `attachment` = ?  WHERE `task_id` = ?");
        $result = $sql->execute([$attachment,$task_id]);
    }
    

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Update task successful...', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 500));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'totalEstimatedTime')) {
    $task_id = $_GET['task_id']; 

    if(($task_id != '')){
        $sql2 = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
        $sql2->execute([$task_id ]);
        $result2 = $sql2->fetch(PDO::FETCH_ASSOC);

        $project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
        $project->execute([$result2['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);

        if(($result2['status'] == 'ready') || ($result2['status'] == 'in_progress')){

            if($result2['is_qc_failed'] == 1){
                $testSql = $conn->prepare("SELECT * FROM `work_log` WHERE `change_type` = 'qc_failure_ressignment' AND `task_id` = ? ORDER BY `id` DESC;");
                $testSql->execute([$task_id]);
                $testSql = $testSql->fetch(PDO::FETCH_ASSOC);

                $sql_query = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `id` > ? AND (`change_type` IS NULL OR `change_type` = 'ressigned') AND `prev_status` = 'in_progress'");
                $sql_query->execute([$task_id , $testSql['id']]);
                $log_work = $sql_query->fetchAll(PDO::FETCH_ASSOC);

            }else{
                $log_work = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND (`change_type` IS NULL OR `change_type` = 'ressigned') AND `prev_status` = 'in_progress'");
                $log_work->execute([$task_id ]);
                $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);
            }


            foreach ($log_work as $entry) {
                if(($entry['change_type'] == 'qc_failure_ressignment') || ($entry['change_type'] == 'qa_failure_ressignment')){
                    break;
                }
    
                $parts = explode(' ', $entry['taken_time']);
                $hours =  str_replace('H', '', $parts[0]);
                $minutes = str_replace('M', '', $parts[1]);
    
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
            
        }else if(($result2['status'] == 'assign_qc') || ($result2['status'] == 'qc_in_progress')){
            $log_work_test = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'assign_qc' ORDER BY `id` DESC");
            $log_work_test->execute([$task_id ]);
            $log_work_test = $log_work_test->fetch(PDO::FETCH_ASSOC);
            
            $log_work = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'qc_in_progress' AND `id` > ? ORDER BY `id` DESC");
            $log_work->execute([$task_id , $log_work_test['id']]);
            $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);
            foreach ($log_work as $entry) {
                if($entry['change_type']){
                    break;
                }
    
                $parts = explode(' ', $entry['taken_time']);
                $hours = str_replace('H', '', $parts[0]);
                $minutes = str_replace('M', '', $parts[1]);
    
                $total_hours += $hours;
                $total_minutes += $minutes;
            }

        }else if(($result2['status'] == 'assign_qa') || ($result2['status'] == 'qa_in_progress')){
            $log_work_test = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'assign_qa' ORDER BY `id` DESC");
            $log_work_test->execute([$task_id ]);
            $log_work_test = $log_work_test->fetch(PDO::FETCH_ASSOC);
            
            $log_work = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'qa_in_progress' AND `id` > ? ");
            $log_work->execute([$task_id , $log_work_test['id']]);
            $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);
            foreach ($log_work as $entry) {
                if($entry['change_type']){
                    break;
                }
    
                $parts = explode(' ', $entry['taken_time']);
                $hours = str_replace('H', '', $parts[0]);
                $minutes = str_replace('M', '', $parts[1]);
    
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
        }else if(($result2['status'] == 'assign_vector') || ($result2['status'] == 'vector_in_progress')){
            $log_work_test = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'assign_vector' ORDER BY `id` DESC");
            $log_work_test->execute([$task_id ]);
            $log_work_test = $log_work_test->fetch(PDO::FETCH_ASSOC);
            
            $log_work = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ?  AND `prev_status` = 'vector_in_progress' AND `id` > ? ");
            $log_work->execute([$task_id , $log_work_test['id']]);
            $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);
            foreach ($log_work as $entry) {
                if($entry['change_type']){
                    break;
                }
    
                $parts = explode(' ', $entry['taken_time']);
                $hours = str_replace('H', '', $parts[0]);
                $minutes = str_replace('M', '', $parts[1]);
    
                $total_hours += $hours;
                $total_minutes += $minutes;
            }
        }
        
        // Convert excess minutes to hours
        $extra_hours = floor($total_minutes / 60);
        $total_hours += $extra_hours;
        $total_minutes %= 60;        

        if(($result2['status'] == 'ready') || ($result2['status'] == 'in_progress')){
            if($result2['is_qc_failed'] == 1){
                $part_time = 0.375;
            }else{
                $part_time = 0.75;
            }
        }else if(($result2['status'] == 'assign_qc') || ($result2['status'] == 'qc_in_progress')){
            if($result2['is_qa_failed'] == 1){
                $part_time = 0.10;
            }else{
                $part_time = 0.20;
            }
        }else if(($result2['status'] == 'assign_qa') || ($result2['status'] == 'qa_in_progress')){
            if($project['vector'] == 1){
                $part_time = 0.02;
            }else{
                $part_time = 0.05;
            }
        }else if(($result2['status'] == 'ready_for_vector') || ($result2['status'] == 'vector_in_progress')){
            $part_time = 0.03;
        }else{
            $part_time = 1;
        }


        $diff_hours = ($result2['estimated_hour']*$part_time)-$total_hours;
        $diff_hours = floor($diff_hours);
        $extra_minutes = ((($result2['estimated_hour']*$part_time)-$total_hours) - $diff_hours) * 60;
        
        // Convert excess minutes to hours
        $extra_hours = floor($total_minutes / 60);
        $total_hours += $extra_hours;
        $total_minutes %= 60;
        
        $diff_minutes = $extra_minutes;
        if ($total_minutes > 0) {
            if ($diff_hours > 0) {
                $diff_hours--;
                $diff_minutes = 60 - $total_minutes;
            } else {
                $diff_minutes = $total_minutes;
            }
        }

        if ($result2) {
            http_response_code(200);
            echo json_encode(['total_estimated_hour' => $total_hours."H ".$total_minutes."M",'task_estimated_hour' => $result2['estimated_hour']*$part_time, 'avi_time' => ['hour' => $diff_hours,'minute' => $diff_minutes]]);
        }
    }else {
        http_response_code(404);
        echo json_encode(array("message" => 'No task found with task_id ' . $task_id, "status" => 404));
    }
}

?>