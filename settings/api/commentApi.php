<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addComment')) {

    if(($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['comment'] != '')){

        $sql = $conn->prepare('INSERT INTO `comments`(`user_id`, `task_id`, `project_id`, `comment`) VALUES (? , ? , ? , ?)');
        $result = $sql->execute([$user_id,$_POST['task_id'],$_POST['project_id'],$_POST['comment']]);
        if($result){

            $userSql = $conn->prepare('SELECT `first_name`, `last_name` FROM `users` WHERE `id` = ?');
            $userSql->execute([$user_id]);
            $userSql = $userSql->fetch(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array("message" => 'Add Comment Successfull', "status" => 200 , "comment" => $_POST['comment'], "first_name" => $userSql['first_name'], "last_name" => $userSql['last_name']));
    
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Something went worrg', "status" => 500));
        }
    }else{
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}


if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getComment')) {

    if($_GET['id'] != ''){
        $sql = $conn->prepare('SELECT * FROM `comments` WHERE `id` = ?');
        $sql->execute([$_GET['id']]);
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        if($result){
            http_response_code(200);
            echo json_encode(array("data" => $result, "status" => 200 ));    
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Something went worrg', "status" => 500));
        }
    }else{
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

?>