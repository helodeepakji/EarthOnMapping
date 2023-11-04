<?php

session_start();
$user_id = $_SESSION['userId'];
include '../config/config.php';
header("content-Type: application/json");


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'postUpload')) {

    if(($_POST['caption'] != '') && ($_FILES['post']['name'] != '')){

        $image = basename($_FILES['post']['name']);
        $uploadPath = '../../images/posts/' . $image;
        move_uploaded_file($_FILES['post']['tmp_name'], $uploadPath);
        $sql = $conn->prepare("INSERT INTO `posts`(`user_id`, `caption`, `image`) VALUES (?, ?, ?)");
        $result =  $sql->execute([$user_id,$_POST['caption'],$image]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'postuploaded', "status" => 200));
            
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'something went error', "status" => 500));
        }
    
    }else{
        http_response_code(404);
        echo json_encode(array("message" => 'Fill all required field.', "status" => 404));
    }

}


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'editPost')) {

    if(($_POST['caption'] != '') && ($_POST['id'] != '')){

        $sql = $conn->prepare("UPDATE `posts` SET `caption` = ? WHERE `id` = ?");
        $result =  $sql->execute([$_POST['caption'],$_POST['id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'post updated', "status" => 200));

            if($_FILES['post']['name'] != ''){
                $image = basename($_FILES['post']['name']);
                $uploadPath = '../../images/posts/' . $image;
                move_uploaded_file($_FILES['post']['tmp_name'], $uploadPath);

                $sql = $conn->prepare("UPDATE `posts` SET `image` = ? WHERE `id` = ?");
                $result =  $sql->execute([$image,$_POST['id']]);
            }
            exit;
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'something went error', "status" => 500));
        }
    
    }else{
        http_response_code(404);
        echo json_encode(array("message" => 'Fill all required field.', "status" => 404));
    }

}


if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteUpload')){
    $sql = $conn->prepare("DELETE FROM `posts` WHERE `id` = ?");
    $result =  $sql->execute([$_POST['id']]);
    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Post Delete Successfull', "status" => 200));   
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'something went error', "status" => 500));
    }
}



if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getPost')){
    $sql = $conn->prepare("SELECT * FROM `posts` WHERE `id` = ?");
    $sql->execute([$_GET['id']]);
    $result =  $sql->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        http_response_code(200);
        echo json_encode($result);   
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'something went error', "status" => 500));
    }
}