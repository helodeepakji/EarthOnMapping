
<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'uploadAttechment')) {

    if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
        $attachment = basename($_FILES['attachment']['name']);
        $uploadPath = '../../upload/attachment/' . $attachment;
        move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
        $sql = $conn->prepare("INSERT INTO `attachment`(`task_id`, `attachment`) VALUES (? , ?)");
        $result = $sql->execute([$_POST['task_id'],$attachment]);
        if($result){
            http_response_code(200);
            echo json_encode(array("message" => 'File is uploaded', "status" => 200));
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else{
        http_response_code(400);
        echo json_encode(array("message" => 'File is not available', "status" => 500));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteAttechment')) {

    if (($_POST['id'] != '') && ($_POST['task_id'] != '')) {


        $sql = $conn->prepare("SELECT * FROM `attachment` WHERE `id` = ? AND `task_id` = ?");
        $sql->execute([$_POST['id'],$_POST['task_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);

        $attachment_path = '../../upload/attachment/' . $sql['attachment'];
        if (file_exists($attachment_path)) {
            unlink($attachment_path);
        }

        $sql = $conn->prepare("DELETE FROM `attachment` WHERE `id` = ? AND `task_id` = ?");
        $result = $sql->execute([$_POST['id'],$_POST['task_id']]);
        if($result){
            http_response_code(200);
            echo json_encode(array("message" => 'File is deleted', "status" => 200));
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else{
        http_response_code(400);
        echo json_encode(array("message" => 'Fill all required filed', "status" => 500));
    }
}



?>

