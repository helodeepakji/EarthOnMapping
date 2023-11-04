<?php

include '../config/config.php';
header("content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getTaskEfficiency')) {
    if ($_GET['user_id']) {
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ?");
        $efficiency->execute([$_GET['user_id']]);
        $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($efficiency as $value) {
            $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
            $task->execute([$value['task_id']]);
            $task = $task->fetch(PDO::FETCH_ASSOC);

            $userslist = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $userslist->execute([$_GET['user_id']]);
            $userslist = $userslist->fetch(PDO::FETCH_ASSOC);
            $value['first_name'] = $userslist['first_name'];
            $value['last_name'] = $userslist['last_name'];

            $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
            $project->execute([$value['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);
            $value['task_name'] = $task['area_sqkm'];
            $value['project_name'] = $project['project_name'];
            $data[] = $value;
        }

        http_response_code(200);
        echo json_encode($data);

    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getTaskEfficiencyByTasKId')) {
    if ($_GET['task_id']) {

        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ?");
        $efficiency->execute([$_GET['task_id']]);
        $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        $profileData = [];

        foreach ($efficiency as $row) {
            $sql = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $sql->execute([$row["user_id"]]);
            $sql = $sql->fetch(PDO::FETCH_ASSOC);

            $taskID = $row["task_id"];
            $projectID = $row["project_id"];
            $profile = $row["profile"];
            $efficiency = $row["efficiency"];
            $profileData[$profile] = $sql['first_name'] . ' ' . $sql['last_name'];
            $profileData[$profile . '_efficiency'] = $efficiency;
            $profileData["Task"] = $taskID;
            $profileData["Project"] = $projectID;
        }

        $data[] = $profileData;
        http_response_code(200);
        echo json_encode($data);

    }
}

if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getProjectEfficiency')){
    $method = $_GET['method'];
    if($method == 'all'){
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ?");
        $efficiency->execute([$_GET['user_id']]);
    }
    
    if($method == 'project'){
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ? AND `user_id` = ?");
        $efficiency->execute([$_GET['product_id'],$_GET['user_id']]);
    }
    
    if($method == 'task'){
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `user_id` = ?");
        $efficiency->execute([$_GET['task_id'],$_GET['user_id']]);
    }

    $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($efficiency as $value) {
        $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
        $task->execute([$value['task_id']]);
        $task = $task->fetch(PDO::FETCH_ASSOC);

        $userslist = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $userslist->execute([$_GET['user_id']]);
        $userslist = $userslist->fetch(PDO::FETCH_ASSOC);
        $value['first_name'] = $userslist['first_name'];
        $value['last_name'] = $userslist['last_name'];

        $project = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
        $project->execute([$value['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);
        $value['task_name'] = $task['area_sqkm'];
        $value['project_name'] = $project['project_name'];
        $data[] = $value;
    }

    http_response_code(200);
    echo json_encode($data);

}

if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getMonthEfficiency')){
    $method = $_GET['method'];
    if($method == 'today'){
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? AND DATE(created_at) = CURDATE()");
        $efficiency->execute([$_GET['user_id']]);
        $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
        
        $users = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $users->execute([$_GET['user_id']]);
        $users = $users->fetch(PDO::FETCH_ASSOC);
        $data = [];
        $employee = [];
        $qc = [];
        $qa = [];
        $vector = [];

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
            }else if($value['profile'] == 'qc'){
                $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                $totalqctime += $task_estimated_hour;
                $qc[] = $value;
                $totalQcArea += $task['area_sqkm'];
            }else if($value['profile'] == 'qa'){

                if($project['vector'] == 1){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.02);
                }else{
                    $task_estimated_hour = ($task['estimated_hour']) * (0.05);
                }
                $totalqatime += $task_estimated_hour;
                $qa[] = $value;
                $totalQaArea += $task['area_sqkm'];
            }else{
                $task_estimated_hour = ($task['estimated_hour']) * (0.03);
                $totalvectortime += $task_estimated_hour;
                $vector[] = $value;
                $totalVectorArea += $task['area_sqkm'];
            }
        }

        $data['user'] = $users;
        $data['vector'] = $vector;
        $data['employee'] = $employee;
        $data['qc'] = $qc;
        $data['qa'] = $qa;
        $data['totalArea'] = ["totalEmployeeArea" => $totalEmployeeArea, "totalQcArea" => $totalQcArea,"totalQaArea" => $totalQaArea,"totalVectorArea" => $totalVectorArea];
        $data['totalTime'] = ["totalEmployeeTime" => $totalprotime, "totalQcTime" => $totalqctime,"totalQaTime" => $totalqatime,"totalVectorTime" => $totalvectortime];
    }
    
    if($method == 'monthly'){
        $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        AND created_at <= NOW()");
        $efficiency->execute([$_GET['user_id']]);
        $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);

        $users = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $users->execute([$_GET['user_id']]);
        $users = $users->fetch(PDO::FETCH_ASSOC);
        $data = [];
        $employee = [];
        $qc = [];
        $qa = [];
        $vector = [];
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
            }else if($value['profile'] == 'qc'){
                $task_estimated_hour = ($task['estimated_hour']) * (0.20);
                $totalqctime += $task_estimated_hour;
                $qc[] = $value;
                $totalQcArea += $task['area_sqkm'];
            }else if($value['profile'] == 'qa'){

                if($project['vector'] == 1){
                    $task_estimated_hour = ($task['estimated_hour']) * (0.02);
                }else{
                    $task_estimated_hour = ($task['estimated_hour']) * (0.05);
                }
                $totalqatime += $task_estimated_hour;
                $qa[] = $value;
                $totalQaArea += $task['area_sqkm'];
            }else{
                $task_estimated_hour = ($task['estimated_hour']) * (0.03);
                $totalvectortime += $task_estimated_hour;
                $vector[] = $value;
                $totalVectorArea += $task['area_sqkm'];
            }
        }
    }

    $data['user'] = $users;
    $data['vector'] = $vector;
    $data['employee'] = $employee;
    $data['qc'] = $qc;
    $data['qa'] = $qa;
    $data['totalArea'] = ["totalEmployeeArea" => $totalEmployeeArea, "totalQcArea" => $totalQcArea,"totalQaArea" => $totalQaArea,"totalVectorArea" => $totalVectorArea];
    $data['totalTime'] = ["totalEmployeeTime" => $totalprotime, "totalQcTime" => $totalqctime,"totalQaTime" => $totalqatime,"totalVectorTime" => $totalvectortime];


    http_response_code(200);
    echo json_encode($data);

}

?>