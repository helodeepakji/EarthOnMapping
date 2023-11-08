<?php

    session_start();
    include '../config/config.php';
    date_default_timezone_set('Asia/Kolkata');
    header("content-Type: application/json");
    $user_id = $_SESSION['userId'];

    function useBreak($conn, $user_id , $task_id){
        $updateBreak = $conn->prepare("UPDATE `break` SET `logged` = 1 WHERE `user_id` = ? AND `task_id` = ? ");
        $updateBreak->execute(array($user_id,$task_id));
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskVector')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            foreach ($task_id as $key => $value) {
                $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                $check->execute([$task_id[$key], $project_id[$key],'assign_vector']);
                $check = $check->fetch(PDO::FETCH_ASSOC);
                if($check){
                    $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                    $taskupdate->execute(['vector_in_progress', $task_id[$key], $project_id[$key]]);
    
                    $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ?, ? , ?)');
                    $sql->execute([$user_id , $task_id[$key], $project_id[$key],'assign_vector' , 'vector_in_progress' , 'In Progress By Vector' , ]);
                }else{
                    http_response_code(400);
                    echo json_encode(["message" => "Vector is already progress"]);
                    exit;
                }
            }

            http_response_code(200);
            echo json_encode(["message" => "Vector in progress"]);
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskQa')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            foreach ($task_id as $key => $value) {
                $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                $check->execute([$task_id[$key], $project_id[$key],'assign_qa']);
                $check = $check->fetch(PDO::FETCH_ASSOC);
                if($check){
                    $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                    $taskupdate->execute(['qa_in_progress', $task_id[$key], $project_id[$key]]);

                    $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ? , ? , ?)');
                    $sql->execute([$user_id , $task_id[$key], $project_id[$key],'assign_qa' , 'qa_in_progress' , 'In Progress By Qa']);
                }else{
                    http_response_code(400);
                    echo json_encode(["message" => "Qa is already progress"]);
                    exit;
                }
            }
            http_response_code(200);
            echo json_encode(["message" => "Qa in progress"]);
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskQa')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            foreach ($task_id as $key => $value) {

                $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
                $project->execute([$project_id[$key]]);
                $project = $project->fetch(PDO::FETCH_ASSOC);
  
                $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                $check->execute([$task_id[$key], $project_id[$key],'qa_in_progress']);
                $check = $check->fetch(PDO::FETCH_ASSOC);
                if($check){
                    
                    if($project['vector'] == 1){
                        $task_estimated_hour = ($check['estimated_hour']) * (0.02);
                    }else{
                        $task_estimated_hour = ($check['estimated_hour']) * (0.05);
                    }

                    $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                    $checkWork->execute([$task_id[$key], $project_id[$key],'qa_in_progress']);
                    $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                    if($checkWork){
                        $givenTimestamp = strtotime($checkWork['created_it']);
                        $currentTimestamp = time();
                        $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                        $hours = floor($timeDifferenceInSeconds / 3600);
                        $minutes = floor(($timeDifferenceInSeconds % 3600) / 60);

                        $checkBreak = $conn->prepare("SELECT SUM(time) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `logged` = 0");
                        $checkBreak->execute([$user_id , $task_id[$key]]);
                        $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                        if($checkBreak['total_time']){
                            $total_minutes = $hours * 60 + $minutes;
                            $total_minutes = $total_minutes - $checkBreak['total_time'];
                            $hours = floor($total_minutes / 60);
                            $minutes = $total_minutes % 60;
                        }

                        $taken = $hours.'H '.$minutes.'M';
                        $temp = $hours + ($minutes/60);
                        $temp = $temp / $number;
                        $effciency = ($task_estimated_hour/$temp)*100;

                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['ready_for_vector', $task_id[$key], $project_id[$key]]);

                        $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                        $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'qa_in_progress' , 'ready_for_vector' , 'Complete By Qa' , 100 , $taken]);

                        
                        useBreak($conn, $user_id , $task_id[$key]);
                        
                        $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)');
                        $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'qa' , $effciency]);

                        $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
                        $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'qa']);

                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Qa Work Log not found."]);
                        exit;
                    }
                }else{
                    http_response_code(400);
                    echo json_encode(["message" => "Qa is not in progress"]);
                    exit;
                }
            }
            http_response_code(200);
            echo json_encode(["message" => "Qa in progress"]);
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskVector')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            foreach ($task_id as $key => $value) {
  
                $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                $check->execute([$task_id[$key], $project_id[$key],'vector_in_progress']);
                $check = $check->fetch(PDO::FETCH_ASSOC);
                if($check){
                    
                    $task_estimated_hour = ($check['estimated_hour']) * (0.03);

                    $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                    $checkWork->execute([$task_id[$key], $project_id[$key],'vector_in_progress']);
                    $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                    if($checkWork){
                        $givenTimestamp = strtotime($checkWork['created_it']);
                        $currentTimestamp = time();
                        $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                        $hours = floor($timeDifferenceInSeconds / 3600);
                        $minutes = floor(($timeDifferenceInSeconds % 3600) / 60);

                        $checkBreak = $conn->prepare("SELECT SUM(time) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `logged` = 0");
                        $checkBreak->execute([$user_id , $task_id[$key]]);
                        $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                        if($checkBreak['total_time']){
                            $total_minutes = $hours * 60 + $minutes;
                            $total_minutes = $total_minutes - $checkBreak['total_time'];
                            $hours = floor($total_minutes / 60);
                            $minutes = $total_minutes % 60;
                        }

                        $taken = $hours.'H '.$minutes.'M';
                        $temp = $hours + ($minutes/60);
                        $temp = $temp / $number;
                        $effciency = ($task_estimated_hour/$temp)*100;

                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['complete', $task_id[$key], $project_id[$key]]);

                        useBreak($conn, $user_id , $task_id[$key]);

                        $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                        $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'vector_in_progress' , 'complete' , 'Complete By Vector' , 100 , $taken]);

                        
                        $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency`) VALUES (? , ? , ? , ? , ?)');
                        $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'vector' , $effciency]);

                        $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ?");
                        $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'vector']);

                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Vector Work Log not found."]);
                        exit;
                    }
                }else{
                    http_response_code(400);
                    echo json_encode(["message" => "Vector is not in progress"]);
                    exit;
                }
            }
            http_response_code(200);
            echo json_encode(["message" => "Vector in progress"]);
        }
    }

?>