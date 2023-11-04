<?php

require_once 'vendor/autoload.php';

use Shuchkin\SimpleXLSXGen;

// Create a new Excel workbook
$workbook = new SimpleXLSXGen();

// Create a new worksheet
// $worksheet = $workbook->addSheet('Sheet1');

// Add data to the worksheet

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $data = $_POST['data'];;
    
    $workbook->addSheet($data);
    
    $workbook->downloadAs('example.xlsx');
}

?>