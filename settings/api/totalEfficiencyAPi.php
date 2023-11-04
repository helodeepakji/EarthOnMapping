<?php

    include '../config/config.php';
    header("content-Type: application/json");

    function getWorkEfficiency($conn ,$task_id , $user_id ){

        $chech_work_log = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND (`change_type` = 'qc_failure_ressignment' OR `change_type` = 'qa_failure_ressignment') ORDER BY `created_it` DESC");
        $chech_work_log->execute([$task_id]);
        $chech_work_log = $chech_work_log->fetch(PDO::FETCH_ASSOC);
        if($chech_work_log){
            $chech_work_id = $chech_work_log['id'];
        }else{
            $chech_work_id = 0;
        }

        $log_work = $conn->prepare("SELECT * FROM work_log WHERE `prev_status` = 'in_progress' AND `task_id` = ? AND `user_id` = ? AND `id` < ?");
        $log_work->execute([$task_id, $user_id,$chech_work_id]);
        $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);

        if(!$log_work){
            $log_work = $conn->prepare("SELECT * FROM work_log WHERE `prev_status` = 'in_progress' AND `task_id` = ? AND `user_id` = ? AND `id` > ?");
            $log_work->execute([$task_id, $user_id,$chech_work_id]);
            $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);
            $noAfter = true;
        }

        foreach ($log_work as $entry) {
            $parts = explode(' ', $entry['taken_time']);
            $hours = intval(str_replace('H', '', $parts[0]));
            $minutes = intval(str_replace('M', '', $parts[1]));

            $total_percentage += intval($entry['work_percentage']);
            $total_hours += $hours;
            $total_minutes += $minutes;
        }

        $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
        $task->execute([$task_id]);
        $task = $task->fetch(PDO::FETCH_ASSOC);
        $percentage_hour = (intval($task['estimated_hour']) * $total_percentage)/100;
        
        // Convert excess minutes to hours
        $extra_hours = floor($total_minutes / 60);
        $total_hours += $extra_hours;
        $total_minutes %= 60;

        $total_hours += $total_minutes/60;

        if($total_hours == 0){
            return 0;
        }

        return ["efficency" => ($percentage_hour / $total_hours)*100,"time" => $total_hours,"work_percentage"=>$total_percentage , "noAfter" => $noAfter];
    }
    
    function getAfterFailureWorkEfficiency($conn ,$task_id , $user_id ){
        
        $chech_work_log = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND (`change_type` = 'qc_failure_ressignment' OR `change_type` = 'qa_failure_ressignment') ORDER BY `created_it` DESC");
        $chech_work_log->execute([$task_id]);
        $chech_work_log = $chech_work_log->fetch(PDO::FETCH_ASSOC);
        
        if(!$chech_work_log){
            return 0;
        }

        $log_work = $conn->prepare("SELECT * FROM work_log WHERE `prev_status` = 'in_progress' AND `task_id` = ? AND `id` > ?");
        $log_work->execute([$task_id , $chech_work_log['id']]);
        $log_work = $log_work->fetchAll(PDO::FETCH_ASSOC);

        if($log_work){
            foreach ($log_work as $entry) {
                $parts = explode(' ', $entry['taken_time']);
                $hours = intval(str_replace('H', '', $parts[0]));
                $minutes = intval(str_replace('M', '', $parts[1]));
    
                $total_percentage += intval($entry['work_percentage']);
                $total_hours += $hours;
                $total_minutes += $minutes;
                
                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                $task->execute([$task_id]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
                $percentage_hour = (intval($task['estimated_hour']) * $total_percentage)/100;
                
                // Convert excess minutes to hours
                $extra_hours = floor($total_minutes / 60);
                $total_hours += $extra_hours;
                $total_minutes %= 60;
        
                $total_hours += $total_minutes/60;
        
            }
            return ["efficency" => ($percentage_hour / $total_hours)*100, "time" => $total_hours ,"work_percentage"=>$total_percentage];
        }else{
            return 0;
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getTaskEfficiency')) {
        if($_GET['user_id']){

            $assign = $conn->prepare("SELECT * FROM `assign` WHERE `user_id` = ? AND `status` = 'complete' AND `role` = 'employee'");
            $assign->execute([$_GET['user_id']]);
            $assign = $assign->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            $temp = null;
            foreach ($assign as $value) {

                if($temp == $value['task_id']){
                    break;
                }else{
                    $temp = $value['task_id'];
                }

                $userslist = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $userslist->execute([$_GET['user_id']]);
                $userslist = $userslist->fetch(PDO::FETCH_ASSOC);
                $value['first_name'] = $userslist['first_name'];
                $value['last_name'] = $userslist['last_name'];

                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                $task->execute([$value['task_id']]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
                
                $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
                $project->execute([$value['project_id']]);
                $project = $project->fetch(PDO::FETCH_ASSOC);


                $beforeData_effcency = getWorkEfficiency($conn,$value['task_id'],$_GET['user_id'])['efficency'];
                $beforeData_percentage = getWorkEfficiency($conn,$value['task_id'],$_GET['user_id'])['work_percentage'];
                $noAfter = getWorkEfficiency($conn,$value['task_id'],$_GET['user_id'])['noAfter'];

                if($noAfter){
                    $afterData_effcency = 0;
                    $afterData_percentage = 0;
                }else{
                    $afterData_effcency = getAfterFailureWorkEfficiency($conn,$value['task_id'],$_GET['user_id'])['efficency'];
                    $afterData_percentage = getAfterFailureWorkEfficiency($conn,$value['task_id'],$_GET['user_id'])['work_percentage'];
                }
                
                if($beforeData_percentage){
                    $total_effcency = $beforeData_effcency - ($afterData_effcency/$beforeData_percentage);
                }else{
                    if($afterData_percentage){
                        $total_effcency = $afterData_effcency;
                    }else{
                        $total_effcency = 0;
                    }
                }
                


                $value['task_name'] = $task['summary'];
                $value['task_estimated_hour'] = intval($task['estimated_hour']);
                $value['project_name'] = $project['project_name'];
                $value['before'] = getWorkEfficiency($conn,$value['task_id'],$_GET['user_id']);
                if($noAfter){
                    $value['after'] = 0;
                }else{
                    $value['after'] = getAfterFailureWorkEfficiency($conn,$value['task_id'],$_GET['user_id']);
                }
                $value['total_efficiny'] = $total_effcency;
                $data[] = $value;
            }

            http_response_code(200);
            echo json_encode($data);
        }else{
            http_response_code(404);
            echo json_encode(["error" => "user id not found"]);
        }
    }
?>