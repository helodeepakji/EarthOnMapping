<?php
    include '../config/config.php';
    header("content-Type: application/json");

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'updateHoliday')) {

        $id = $_POST['holiday_id'];
        $newDate = $_POST['date'];
        $newSummary = $_POST['summary'];
        if($_FILES["image"]["name"]){
            $image = basename($_FILES["image"]["name"]);
            $uploadPath = '../../images/holiday/' . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
        }else{
            $image = null;
        }
    

        $sql = $conn->prepare("UPDATE `holiday` SET `date` = ?, `summary` = ?, `image` = ? WHERE `id` = ?");
        $result = $sql->execute([$newDate, $newSummary, $image, $id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Holiday updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update holiday']);
        }

    }

    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['type'] == 'getHoliday') {
        $id = $_GET['id'];
        $sql = $conn->prepare("SELECT * FROM `holiday` WHERE `id` = ?");
        $sql->execute([$id]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete holiday']);
        }
    }
    

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['type'] == 'deleteHoliday') {
        $id = $_POST['id'];
    
        $sql = $conn->prepare("DELETE FROM `holiday` WHERE `id` = ?");
        $result = $sql->execute([$id]);
        
    
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Holiday deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete holiday']);
        }
    }
?>