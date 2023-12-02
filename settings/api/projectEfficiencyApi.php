<?php
    session_start();
    include '../config/config.php';
    header("content-Type: application/json");

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getFullProjectEfficiency')){
        if($_GET['project_id'] != ''){
            $project_id = $_GET['project_id'];
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ? AND `user_id` = ?");
                $efficiency->execute([$project_id, $_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ?");
                $efficiency->execute([$project_id]);
            }
            $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            $employee = [];
            $qc = [];
            $qa = [];
            $vector = [];
            $protakentime = 0;
            $qctakentime = 0;
            $qatakentime = 0;
            $vectortakentime = 0;
            $totalprotime = 0;
            $totalqctime = 0;
            $totalqatime = 0;
            $totalvectortime = 0;
    
            foreach ($efficiency as $value) {
                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                $task->execute([$value['task_id']]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
    
                $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
                $project->execute([$value['project_id']]);
                $project = $project->fetch(PDO::FETCH_ASSOC);
        
        
        
                $value['task_name'] = $task['area_sqkm'];
                $value['project_name'] = $project['project_name'];
    
                if($value['profile'] == 'employee'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.75);
                    $totalprotime += $task_estimated_hour;
                    $employee[] = $value;
                    $totalEmployeeArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $protakentime += 0;
                    }else{
                        $protakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else if($value['profile'] == 'qc'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                    $totalqctime += $task_estimated_hour;
                    $qc[] = $value;
                    $totalQcArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $qctakentime += 0;
                    }else{
                        $qctakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else if($value['profile'] == 'qa'){
    
                    if($project['vector'] == 1){
                        $task_estimated_hour = ($task['estimated_hour']) * (0.02);
                    }else{
                        $task_estimated_hour = ($task['estimated_hour']) * (0.05);
                    }
                    $totalqatime += $task_estimated_hour;
                    $qa[] = $value;
                    $totalQaArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $qatakentime += 0;
                    }else{
                        $qatakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else{
                    $task_estimated_hour = ($task['estimated_hour']) * (0.03);
                    $totalvectortime += $task_estimated_hour;
                    $vector[] = $value;
                    $totalVectorArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $vectortakentime += 0;
                    }else{
                        $vectortakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }
            }
            $data['totalTime'] = ["totalEmployeeTime" => $totalprotime, "totalQcTime" => $totalqctime,"totalQaTime" => $totalqatime,"totalVectorTime" => $totalvectortime];
            $data['totalTakenTime'] = ["totalEmployeeTime" => $protakentime, "totalQcTime" => $qctakentime,"totalQaTime" => $qatakentime,"totalVectorTime" => $vectortakentime];
            http_response_code(200);
            echo json_encode($data);
            exit;
        }
        if($_GET['task_id'] != ''){
            $task_id = $_GET['task_id'];
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `user_id` = ?");
                $efficiency->execute([$task_id, $_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ?");
                $efficiency->execute([$task_id]);
            }
            $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            $employee = [];
            $qc = [];
            $qa = [];
            $vector = [];
            $protakentime = 0;
            $qctakentime = 0;
            $qatakentime = 0;
            $vectortakentime = 0;
            $totalprotime = 0;
            $totalqctime = 0;
            $totalqatime = 0;
            $totalvectortime = 0;
    
            foreach ($efficiency as $value) {
                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                $task->execute([$value['task_id']]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
    
                $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
                $project->execute([$value['project_id']]);
                $project = $project->fetch(PDO::FETCH_ASSOC);
        
        
        
                $value['task_name'] = $task['area_sqkm'];
                $value['project_name'] = $project['project_name'];
    
                if($value['profile'] == 'employee'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.75);
                    $totalprotime += $task_estimated_hour;
                    $employee[] = $value;
                    $totalEmployeeArea += $task['area_sqkm'];
                    $protakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                }else if($value['profile'] == 'qc'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                    $totalqctime += $task_estimated_hour;
                    $qc[] = $value;
                    $totalQcArea += $task['area_sqkm'];
                    $qctakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                }else if($value['profile'] == 'qa'){
    
                    if($project['vector'] == 1){
                        $task_estimated_hour = ($task['estimated_hour']) * (0.02);
                    }else{
                        $task_estimated_hour = ($task['estimated_hour']) * (0.05);
                    }
                    $totalqatime += $task_estimated_hour;
                    $qa[] = $value;
                    $totalQaArea += $task['area_sqkm'];
                    $qatakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                }else{
                    $task_estimated_hour = ($task['estimated_hour']) * (0.03);
                    $totalvectortime += $task_estimated_hour;
                    $vector[] = $value;
                    $totalVectorArea += $task['area_sqkm'];
                    $vectortakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                }
            }
            $data['totalTime'] = ["totalEmployeeTime" => $totalprotime, "totalQcTime" => $totalqctime,"totalQaTime" => $totalqatime,"totalVectorTime" => $totalvectortime];
            $data['totalTakenTime'] = ["totalEmployeeTime" => $protakentime, "totalQcTime" => $qctakentime,"totalQaTime" => $qatakentime,"totalVectorTime" => $vectortakentime];
            http_response_code(200);
            echo json_encode($data);
            exit;
        }
        if($_GET['start_date'] != '' && $_GET['end_date'] != ''){
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `created_at` BETWEEN ? AND ? AND `user_id` = ?");
                $efficiency->execute([$start_date, $end_date , $_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `created_at` BETWEEN ? AND ?");
                $efficiency->execute([$start_date, $end_date]);
            }
            $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            $employee = [];
            $qc = [];
            $qa = [];
            $vector = [];
            $protakentime = 0;
            $qctakentime = 0;
            $qatakentime = 0;
            $vectortakentime = 0;
            $totalprotime = 0;
            $totalqctime = 0;
            $totalqatime = 0;
            $totalvectortime = 0;
    
            foreach ($efficiency as $value) {
                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                $task->execute([$value['task_id']]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
    
                $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
                $project->execute([$value['project_id']]);
                $project = $project->fetch(PDO::FETCH_ASSOC);
        
        
        
                $value['task_name'] = $task['area_sqkm'];
                $value['project_name'] = $project['project_name'];
    
                if($value['profile'] == 'employee'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.75);
                    $totalprotime += $task_estimated_hour;
                    $employee[] = $value;
                    $totalEmployeeArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $protakentime += 0;
                    }else{
                        $protakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else if($value['profile'] == 'qc'){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                    $totalqctime += $task_estimated_hour;
                    $qc[] = $value;
                    $totalQcArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $qctakentime += 0;
                    }else{
                        $qctakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else if($value['profile'] == 'qa'){
    
                    if($project['vector'] == 1){
                        $task_estimated_hour = ($task['estimated_hour']) * (0.02);
                    }else{
                        $task_estimated_hour = ($task['estimated_hour']) * (0.05);
                    }
                    $totalqatime += $task_estimated_hour;
                    $qa[] = $value;
                    $totalQaArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $qatakentime += 0;
                    }else{
                        $qatakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }else{
                    $task_estimated_hour = ($task['estimated_hour']) * (0.03);
                    $totalvectortime += $task_estimated_hour;
                    $vector[] = $value;
                    $totalVectorArea += $task['area_sqkm'];
                    if($value['efficiency'] / 100 <= 0){
                        $vectortakentime += 0;
                    }else{
                        $vectortakentime += $task_estimated_hour / ($value['efficiency'] / 100);
                    }
                }
            }
            $data['totalTime'] = ["totalEmployeeTime" => $totalprotime, "totalQcTime" => $totalqctime,"totalQaTime" => $totalqatime,"totalVectorTime" => $totalvectortime];
            $data['totalTakenTime'] = ["totalEmployeeTime" => $protakentime, "totalQcTime" => $qctakentime,"totalQaTime" => $qatakentime,"totalVectorTime" => $vectortakentime];
            http_response_code(200);
            echo json_encode($data);
            exit;
        }
    }

?>