<?php

$current_page = 'task-efficiency';
include 'settings/config/config.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $userDetails = $_SESSION['userDetails'];
    $user_id = $_SESSION['userId'];
}

$projectList = $conn->prepare("SELECT * FROM `projects`");
$projectList->execute();
$projectList = $projectList->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$output = $conn->prepare("SELECT DISTINCT `task_id` FROM `efficiency` ORDER BY `task_id`;");
$output->execute();
$output = $output->fetchAll(PDO::FETCH_ASSOC);

foreach ($output as $value) {

    $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ?");
    $efficiency->execute([$value['task_id']]);
    $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);

    $profileData = [];

    foreach ($efficiency as $row) {
        $sql = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $sql->execute([$row["user_id"]]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        
        $projectDetail = $conn->prepare("SELECT * FROM `projects` WHERE `project_id` = ?");
        $projectDetail->execute([$row["project_id"]]);
        $projectDetail = $projectDetail->fetch(PDO::FETCH_ASSOC);

        $profile = $row["profile"];
        if($profile == "employee") {
            $work_log_status = 'ready';
        }else if($profile == "qc"){
            $work_log_status = 'assign_qc';
        }else if($profile == "qa"){
            $work_log_status = 'assign_qa';
        }else if($profile == "vector"){
            $work_log_status = 'assign_vector';
        }

        $complate_time = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ?  ORDER BY `created_it` DESC");
        $complate_time->execute([$row["task_id"], $row["project_id"] , $work_log_status]);
        $complate_time = $complate_time->fetch(PDO::FETCH_ASSOC);

        $efficiency = $row["efficiency"];
        $profileData[$profile] = $sql['first_name'].' '.$sql['last_name'];
        $profileData[$profile.'_efficiency'] = $efficiency;
        $profileData[$profile.'_complete_time'] = $row["created_at"];
        $profileData[$profile.'_start_time'] = $complate_time['created_it'];
        $profileData["Task"] = $row["task_id"];
        $profileData["Project"] = $row["project_id"];
        $profileData["Project_name"] = $projectDetail['project_name'];
        $assign = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `role` = 'employee'");
        $assign->execute([$profileData["Task"]]);
        $assign = $assign->fetch(PDO::FETCH_ASSOC);
        if($assign){
            $profileData["assign_date"] = $assign['created_at'];
        }
    }

    $data[] = $profileData;
}

?>

<?php
$title = 'Task Efficiency || EOM ';
include 'settings/header.php'
    ?>
<style>
    a {
        text-decoration: none;
    }
</style>
<main style="margin-top: 100px;">
    <div class="p-2">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex" style="justify-content: space-between;">
                    <h4 class="card-title">Task Efficiency</h4>
                    <div style="display: flex;">
                        <select name="project" id="projectSelect" class="form-control" style="margin :0 15px;">
                            <option value="">Select Project</option>
                            <?php
                            foreach ($projectList as $project) {
                            echo '<option value="' . $project['project_id'] . ' (' . $project['project_name'] . ')">' . $project['project_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary w-20" id="download-btn" style="width: 100px;">Download</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <!-- <h4 class="card-title">Total Efficiency</h4> -->
                </div>
                <div class="card-body">
                    <table id="myTable" class="display">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Task</th>

                                <th>Pro Name</th>
                                <th>Pro Efficiency</th>
                                <th>Pro Time</th>

                                <th>QC Name</th>
                                <th>QC Efficiency</th>
                                <th>QC Time</th>

                                <th>QA Name</th>
                                <th>QA Efficiency</th>
                                <th>QA Time</th>

                                <th>Vector Name</th>
                                <th>Vector Efficiency</th>
                                <th>Vector Time</th>

                                <th>Task Efficiency</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            <?php
                                foreach ($data as $value) {

                                    if ($value['qc_complete_time'] != '') {
                                        $timestamp = strtotime($value['qc_complete_time']);
                                        $Qccomplete = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $Qccomplete = '';
                                    }
                                    
                                    if ($value['qc_start_time'] != '') {
                                        $timestamp = strtotime($value['qc_start_time']);
                                        $QcStart = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $QcStart = '';
                                    }
                                    
                                    if ($value['employee_complete_time'] != '') {
                                        $timestamp = strtotime($value['employee_complete_time']);
                                        $Procomplete = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $Procomplete = '';
                                    }
                                    
                                    if ($value['employee_start_time'] != '') {
                                        $timestamp = strtotime($value['employee_start_time']);
                                        $Prostart = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $Prostart = '';
                                    }
                                    
                                    
                                    if ($value['qa_complete_time'] != '') {
                                        $timestamp = strtotime($value['qa_complete_time']);
                                        $QaComplete = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $QaComplete = '';
                                    }
                                    
                                    if ($value['qa_start_time'] != '') {
                                        $timestamp = strtotime($value['qa_start_time']);
                                        $Qastart = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $Qastart = '';
                                    }
                                    
                                    
                                    if ($value['vector_complete_time'] != '') {
                                        $timestamp = strtotime($value['vector_complete_time']);
                                        $VectorComplete = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $VectorComplete = '';
                                    }
                                    
                                    if ($value['vector_start_time'] != '') {
                                        $timestamp = strtotime($value['vector_start_time']);
                                        $Vectorstart = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $Vectorstart = '';
                                    }

                                    $timestamp = strtotime($value['assign_date']);
                                    if ($timestamp !== false) {
                                        $AssigDate = date('j M, Y h:i A', $timestamp);
                                    } else {
                                        $AssigDate = '';
                                    }

                                    if($value['vector'] != ''){
                                        $vectorData = '<td>'.$value['vector'].'</td>
                                        <td>
                                            <div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$value['vector_efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success " style="width: '.$value['vector_efficiency'].'%">'.$value['vector_efficiency'].'%</div></div>
                                            <br>
                                            '.round($value['vector_efficiency'], 2).'%
                                        </td>
                                        <td style="color:red">'. $Vectorstart .'<br><span style="color:green">'. $VectorComplete .'</span></td>';
                                    }else{
                                        $vectorData = '<td></td><td></td><td></td>';
                                    }
                                    
                                    $project_efficiency = ($value['employee_efficiency'] + $value['qc_efficiency'] + $value['qa_efficiency'] + $value['vector_efficiency'])/4;

                                    echo 
                                    '
                                    <tr>
                                        <td>'.$value["Project"].' ('.$value["Project_name"].')</td>
                                        <td>'.$value["Task"].'</td>

                                        <td>'.$value['employee'].'</td>
                                        <td>
                                            <div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$value['employee_efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success " style="width: '.$value['employee_efficiency'].'%">'.$value['employee_efficiency'].'%</div></div>
                                            <br>
                                            '.round($value['employee_efficiency'], 2).'%
                                        </td>
                                        <td style="color:red">'.$Prostart.'<br><span style="color:green">'.$Procomplete .'</span></td>
                                        
                                        <td>'.$value['qc'].'</td>
                                        <td>
                                            <div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$value['qc_efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success " style="width: '.$value['qc_efficiency'].'%">'.$value['qc_efficiency'].'%</div></div>
                                            <br>
                                            '.round($value['qc_efficiency'], 2).'%
                                        </td>
                                        <td style="color:red">'. $QcStart .'<br><span style="color:green">'. $Qccomplete .'</span></td>
                                        
                                        <td>'.$value['qa'].'</td>
                                        <td>
                                            <div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$value['qa_efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success " style="width: '.$value['qa_efficiency'].'%">'.$value['qa_efficiency'].'%</div></div>
                                            <br>
                                            '.round($value['qa_efficiency'], 2).'%
                                        </td>
                                        <td style="color:red">'. $Qastart .'<br><span style="color:green">'. $QaComplete .'</span></td>
                                        

                                        '.$vectorData.'
                                        
                                        <td><div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$project_efficiency.'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success " style="width: '.$project_efficiency.'%">'.$project_efficiency.'%</div></div></td>
                                    </tr>
                                    ';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'settings/footer.php' ?>
<script>
    var notyf = new Notyf({
        position: {
            x: 'right',
            y: 'top'
        }
    });

    $(document).ready(function() {
        dataTable.order([1, 'desc']).draw();
    })

    $(".select2").select2();
    $(".select2-selection__rendered").addClass("form-control");
    $(".select2-selection--single").css("border", "0");
    $('#myTable').DataTable();


    $('#download-btn').click(() => {
        var data = JSON.parse('<?php echo json_encode($data) ?>');
        const extractedDataArray = [];
        const extractedData = {
            "task": "Task Id",
            "project": "Project",
            "employee": "Production ",
            "employee_efficiency": "Production Efficiency",
            "qc": "Qc",
            "qc_efficiency": "Qc Efficiency",
            "qa": "Qa",
            "qa_efficiency": "Qa Efficiency",
            "vector": "Vector",
            "vector_efficiency": "Vector Efficiency",
            "project_efficiency": "Project Efficiency",
        };

        extractedDataArray.push(extractedData);
        data.forEach(element => {
            var project_efficiency = (parseFloat(element.employee_efficiency) + parseFloat(element.qc_efficiency) + parseFloat(element.qa_efficiency) + parseFloat(element.vector_efficiency))/4;
            const extractedData = {
                "task": element.Task,
                "project": element.Project,
                "employee": element.employee,
                "employee_efficiency": element.employee_efficiency,
                "qc": element.qc,
                "qc_efficiency": element.qc_efficiency,
                "qa": element.qa,
                "qa_efficiency": element.qa_efficiency,
                "vector": element.vector,
                "vector_efficiency": element.vector_efficiency,
                "project_efficiency": project_efficiency,
            };
            extractedDataArray.push(extractedData);
        });
        console.log(extractedDataArray);
        downloadExcel(extractedDataArray);
    });


    function downloadExcel(data) {
        $.ajax({
            url: "settings/downloadExcel.php",
            type: 'POST',
            data: {
                data: data
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (result) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(result);
                a.href = url;
                a.download = "example.xlsx"; // Set the desired file name
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                notyf.success("Excel File Download SuccessFull");
            }
        });
    }

  $('#projectSelect').change(function () {
    var projectInput, projectValue;
    projectInput = document.getElementById("projectSelect");
    projectValue = projectInput.value;
    dataTable.column(2).search(projectValue).draw();
  });
</script>
</body>

</html>