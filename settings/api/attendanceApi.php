<?php

session_start();
$user_id = $_SESSION['userId'];
include '../config/config.php';
$currentDate = date('Y-m-d');
header("content-Type: application/json");


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'clockOut')) {
    $sql = $conn->prepare('SELECT * FROM `attendence` WHERE date= CURDATE() AND `user_id`= ?');
    $sql->execute([$user_id]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        if ($result['clock_out_time']) {
            http_response_code(404);
            echo json_encode(array("message" => 'already clockout', "status" => 404));
            exit;
        } else {
            $sql = $conn->prepare('UPDATE attendence SET clock_out_time = CURRENT_TIMESTAMP WHERE `date` = CURDATE() AND `user_id` = ?');
            $sql->execute([$user_id]);
            http_response_code(200);
            echo json_encode(array("message" => 'clockOut successful', "status" => 404));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'clockin first', "status" => 404));
    }
}



if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'clockIn')) {
    $sql = $conn->prepare("SELECT * FROM `attendence` WHERE `date` = ? AND `user_id` = ?");
    $sql->execute([$user_id]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    if ($result) {

        http_response_code(404);
        echo json_encode(array("message" => 'already clockin', "status" => 404));
        exit;

    } else {
        $sql = $conn->prepare("INSERT INTO `attendence`(`user_id`) VALUES ( ? )");
        $sql->execute([$user_id]);
        http_response_code(200);
        echo json_encode(array("message" => 'clockIn successful', "status" => 404));

    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addRegularisation')) {
    $sql = $conn->prepare("UPDATE `attendence` SET `clock_out_time` = ? , `remarks` = ?, `regularisation` = 1 WHERE `id` = ? AND `user_id` = ?");
    $result = $sql->execute([$_POST['clockout_time'], $_POST['remarks'], $_POST['attendance_id'], $user_id]);
    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Add Regularisation successful', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'approveAttendance')) {
    $sql = $conn->prepare("UPDATE `attendence` SET `regularisation` = 0 WHERE `id` = ?");
    $result = $sql->execute([$_POST['id']]);
    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Add Regularisation successful', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getMonth')) {
    if ($_GET['startDate'] != '' && $_GET['endDate'] != '') {
        $startdate = $_GET['startDate'];
        $enddate = $_GET['endDate'];

        if ($startdate > $enddate) {
            http_response_code(400);
            echo json_encode(["message" => "First Date is always Greater then Second Date.", "status" => 400]);
            exit;
        }

        $startDateObj = new DateTime($startdate);
        $endDateObj = new DateTime($enddate);
        $currentDateObj = $startDateObj;
        $attendanceArray = [];

        $users = $conn->prepare('SELECT * FROM `users` ORDER BY `id` DESC');
        $users->execute();
        $users = $users->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            $data = [];
            $currentDateObj = new DateTime($startdate);
            
            $date = [];
            $date[] = 'Date';
            $data[] = $user['first_name'].' '.$user['last_name'];
            while ($currentDateObj <= $endDateObj) {
                $currentDate = $currentDateObj->format('Y-m-d');

                $date[] = $currentDate;

                $attendances = $conn->prepare("SELECT * FROM `attendence` WHERE `user_id` = ? AND `date` = ?");
                $attendances->execute([$user['id'], $currentDate]);
                $attendance = $attendances->fetch(PDO::FETCH_ASSOC);
        
                if ($attendance) {
                    $data[] = '1';
                } else {
                    $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = ?");
                    $holiday->execute([$currentDate]);
                    $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
        
                    if ($holiday) {
                        $data[] = 'holiday';
                    } else {
                        $leave = $conn->prepare("SELECT * FROM `leaves` WHERE `form_date` <= ? AND `end_date` >= ? AND `user_id` = ? AND `status` = 'approve'");
                        $leave->execute([$currentDate, $currentDate, $user['id']]);
                        $leave = $leave->fetch(PDO::FETCH_ASSOC);
        
                        if ($leave) {
                            $data[] = 'leave';
                        } else {
                            if (date("w", strtotime($currentDate)) == 0) {
                                $data[] = 'week off';
                              }else{
                                $data[] = '0';
                              }
                        }
                    }
                }
        
                $currentDateObj->modify('+1 day');
            }
            $attendanceArray['attendance'][] = $data;
        }
        $attendanceArray['date'] = $date;
        echo json_encode($attendanceArray);

    } else {
        http_response_code(400);
        echo json_encode(["message" => "Start Date and End Date is required.", "status" => 400]);
    }
}

?>