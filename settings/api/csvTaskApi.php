<?php

include '../config/config.php';
header("content-Type: application/json");

require '../phpOffice/vendor/autoload.php'; // Load the PhpSpreadsheet library

function isValidFileExtension($fileName, $validExtensions) {
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    return in_array($ext, $validExtensions);
}

try {
    $flag = 1;
    if (isset($_POST['project_id'])) {

        $fileInfo = $_FILES['csvFile'];
        $fileName = $fileInfo['name'];

        $validExtensions = ['xlsx', 'csv'];

        // Check if the uploaded file has a valid extension
        if (!isValidFileExtension($fileName, $validExtensions)) {
            http_response_code(500);
            echo json_encode(["message" => "File must in .xlsx or .csv format"]);
            exit();
        }


        $tempFile = $_FILES["csvFile"]["tmp_name"];
        $targetFile = "../../upload/csvTaskFile/" . basename($_FILES["csvFile"]["name"]);
        if (move_uploaded_file($tempFile, $targetFile)) {

            // Load the Excel file
            $inputFileName = $targetFile;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

            // Select the first worksheet in the Excel file
            $worksheet = $spreadsheet->getActiveSheet();

            // Loop through rows and insert data into the database
            $fulldata = [];
            $already = [];
            foreach ($worksheet->getRowIterator() as $row) {

                if($flag == 1){
                    $flag = 2;
                    continue;
                }

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE);

                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }

                $check = $conn->prepare("SELECT * FROM `tasks` WHERE task_id = :taskid");
                $check->bindParam(':taskid', $data[0]);
                $check->execute();
                $result = $check->fetch(PDO::FETCH_ASSOC);
                if(!$result){
                    $sql = $conn->prepare("INSERT INTO `tasks`(`task_id`, `project_id`, `area_sqkm`, `estimated_hour`) VALUES (? , ? , ? , ?)");
                    $sql->execute([$data[0], $_POST['project_id'] , floatval($data[2]), floatval($data[1])]);
                }else{
                    $already[] = $data[0];
                }
                $fulldata[] = $data;
            }
            http_response_code(200);
            echo json_encode(["data" => $fulldata , "task_id" => $already , "project_id" => $_POST['project_id']]);
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>