<?php

    session_start();
    $user_id = $_SESSION['userId'];
    include '../config/config.php';
    header("content-Type: application/json");

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'vectorComplete')) {
        $sql = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
        $sql->execute([$_POST['task_id'],$_POST['project_id']]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if($result){

            $task = $conn->prepare("UPDATE `tasks` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ?");
            $result2 = $task->execute(['complete',$_POST['task_id'],$_POST['project_id']]);
            if($result2){
                http_response_code(200);
                echo json_encode(array("message" => 'Task Complete', "status" => 500));
            }

        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }

?>