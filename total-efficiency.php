<?php

$current_page = 'total-efficiency';
include 'settings/config/config.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $userDetails = $_SESSION['userDetails'];
    $user_id = $_SESSION['userId'];
}

$userslist = $conn->prepare("SELECT * FROM `users` WHERE `user_type` = 'user'");
$userslist->execute();
$userslist = $userslist->fetchAll(PDO::FETCH_ASSOC);

$projectlist = $conn->prepare("SELECT * FROM `projects`");
$projectlist->execute();
$projectlist = $projectlist->fetchAll(PDO::FETCH_ASSOC);

$tasklist = $conn->prepare("SELECT * FROM `tasks`");
$tasklist->execute();
$tasklist = $tasklist->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$title = 'Profile Efficiency || EOM ';
include 'settings/header.php'
    ?>
<style>
    a {
        text-decoration: none;
    }

    .col-12.col-sm-3 {
        border: 0.1px solid black;
        display: grid;
    }

    .block {
        padding: 10px;
    }

    hr {
        margin: 0;
    }
    li {
        list-style: decimal;
        margin-bottom: 5px; 
    }
    .block.taskdata {
        height: 400px;
        overflow: auto;
    }

</style>
<main style="margin-top: 100px;">
    <div class="container ">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h4 class="card-title">Project Efficiency</h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-lg-3 p-2">
                            <select name="user_id" class="form-control select2" id="user_id">
                                <option value="" default>Select User</option>
                                <?php
                                foreach ($userslist as $user) {
                                    echo '<option value="' . $user['id'] . '">' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-3 p-2">
                            <select name="method" id="method" class="form-control">
                                <option value="all">All</option>
                                <option value="today">Today</option>
                                <option value="monthly">Monthly</option>
                                <option value="project">Project</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="col-lg-3 p-2">
                            <select name="product_id" class="form-control" id="product_id" style="display:none">
                                <option value="" default>Select Project</option>
                                <?php
                                foreach ($projectlist as $value) {
                                    echo '<option value="' . $value['project_id'] . '">' . $value['project_name'] . '</option>';
                                }
                                ?>
                            </select>
                            <div id="msg_task_id" style="display:none;">
                                <select name="task_id" id="task_id" class="form-control select2" style="width: 100%;">
                                    <option value="" default>Select Task</option>
                                    <?php
                                    foreach ($tasklist as $value) {
                                        echo '<option value="' . $value['task_id'] . '">' . $value['task_id'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3" style="display: flex; align-items: center;justify-content: space-evenly;">
                            <button class="btn btn-primary" id="search-btn" style=" width: 40%;">Search</button>
                            <button class="btn btn-primary" id="download-btn" style=" width: 40%;">Download</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container ">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <!-- <h4 class="card-title">Total Efficiency</h4> -->
                </div>
                <div class="card-body">
                    <table id="myTable" class="display">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Role</th>
                                <th>Efficiency</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                    <div class="dataView" style="display : none">
                        <h4 id="name"></h4>
                        <div class="row">
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Pro</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="prodata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalprodata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Qc</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="qcdata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalqcdata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Qa</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="qadata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalqadata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Vector</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="vectordata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalvectordata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12">
                                <div class="tex"><h4 id="total_active_time">Token Active Time : </h4></div>
                                <div class="tex"><h4 id="total_working_time">Total Task Time : </h4></div>
                                <div class="tex"><h4 id="total_worked_time">Total Taken Time : </h4></div>
                                <div class="tex"><h4 id="total_remaning_time">Total Remaining Time : </h4></div>
                            </div>
                        </div>
                    </div>
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

    $(".select2").select2();
    $(".select2-selection__rendered").addClass("form-control");
    $(".select2-selection--single").css("border", "0");
    $('#myTable').DataTable();


    $("#search-btn").click(() => {
        var user_id = $("#user_id").val();
        var method = $("#method").val();
        var product_id = $("#product_id").val();
        var task_id = $("#task_id").val();
        if (method == 'today' || method == 'monthly') {
            Notiflix.Loading.standard();
            $('#myTable_wrapper').css("display", "none");
            $('.dataView').css("display", "block");
            $.ajax({
                url: 'settings/api/efficiencyAPi.php',
                data: {
                    type: 'getMonthEfficiency',
                    user_id: user_id,
                    method: method,
                    product_id: product_id,
                    task_id: task_id
                },
                dataType: 'json',
                success: function (response) {
                    Notiflix.Loading.remove();
                    $('#prodata').html('');
                    $('#qcdata').html('');
                    $('#qadata').html('');
                    $('#vectordata').html('');

                    $('#total_working_time').text('Total Task Time :  '+(response.total_working_time).toFixed(2) + ' hr.');
                    $('#total_worked_time').text('Total Taken Time :  '+(response.total_worked_time).toFixed(2) + ' hr.');
                    $('#total_active_time').text('Token Active Time : '+(response.active_time).toFixed(2) + ' hr.');
                    $('#total_remaning_time').text('Total Remaining Time : '+(response.active_time - response.total_worked_time).toFixed(2)  + ' hr.');

                    console.log(response);
                    var pro = response.employee;
                    var qa = response.qa;
                    var qc = response.qc;
                    var vector = response.vector;
                    pro.forEach(element => {
                        $('#prodata').append(`<li> ${element.task_id} </li>`);
                    });
                    // $('.totalprodata .efficiency').text((temp/count).toFixed(2));
                    $('.totalprodata .efficiency').text(((response.totalTime.totalEmployeeTime/response.totalTakenTime.totalEmployeeTime)*100).toFixed(2));
                    

                    qa.forEach(element => {
                        $('#qadata').append(`<li> ${element.task_id} </li>`);
                    });
                    // $('.totalqadata .efficiency').text((temp/count).toFixed(2));
                    $('.totalqadata .efficiency').text(((response.totalTime.totalQaTime/response.totalTakenTime.totalQaTime)*100).toFixed(2));

                    qc.forEach(element => {
                        $('#qcdata').append(`<li> ${element.task_id} </li>`);
                    });
                    // $('.totalqcdata .efficiency').text((temp/count).toFixed(2));
                    $('.totalqcdata .efficiency').text(((response.totalTime.totalQcTime/response.totalTakenTime.totalQcTime)*100).toFixed(2));
                    
                    vector.forEach(element => {
                        $('#vectordata').append(`<li> ${element.task_id} </li>`);
                    });

                    // $('.totalvectordata .efficiency').text((temp/count).toFixed(2));
                    $('.totalvectordata .efficiency').text(((response.totalTime.totalVectorTime/response.totalTakenTime.totalVectorTime)*100).toFixed(2));

                    $('.totalprodata .taken_time').text((response.totalTakenTime.totalEmployeeTime).toFixed(2) + ' hr');
                    $('.totalqadata .taken_time').text((response.totalTakenTime.totalQaTime).toFixed(2) + ' hr');
                    $('.totalqcdata .taken_time').text((response.totalTakenTime.totalQcTime).toFixed(2) + ' hr');
                    $('.totalvectordata .taken_time').text((response.totalTakenTime.totalVectorTime).toFixed(2) + ' hr');

                    $('#name').text(`${response.user.first_name} ${response.user.last_name}`);
                    $('.totalprodata .area_sqkm').text(response.totalArea.totalEmployeeArea ?? 0 + ' sqkm');
                    $('.totalqadata .area_sqkm').text(response.totalArea.totalQaArea ?? 0 + ' sqkm');
                    $('.totalqcdata .area_sqkm').text(response.totalArea.totalQcArea ?? 0 + ' sqkm');
                    $('.totalvectordata .area_sqkm').text(response.totalArea.totalVectorArea ?? 0 + ' sqkm');
                    
                    $('.totalprodata .total_time').text((response.totalTime.totalEmployeeTime).toFixed(2) + ' hr');
                    $('.totalqadata .total_time').text((response.totalTime.totalQaTime).toFixed(2) + ' hr');
                    $('.totalqcdata .total_time').text((response.totalTime.totalQcTime).toFixed(2) + ' hr');
                    $('.totalvectordata .total_time').text((response.totalTime.totalVectorTime).toFixed(2) + ' hr');
                        
  
                },
                error: function(xhr, status, error) {
                    Notiflix.Loading.remove();
                    var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                    notyf.error(errorMessage);
                }
            });
        } else {
            $('.dataView').css("display", "none");
            $('#myTable_wrapper').css("display", "block");
            Notiflix.Loading.standard();
            $.ajax({
                url: 'settings/api/efficiencyAPi.php',
                data: {
                    type: 'getProjectEfficiency',
                    user_id: user_id,
                    method: method,
                    product_id: product_id,
                    task_id: task_id
                },
                dataType: 'json',
                success: function (response) {
                    Notiflix.Loading.remove();
                    console.log(response);
                    var table = $('#myTable').DataTable();
                    table.clear().draw();
                    response.forEach(element => {
                        var efficiency = 0;
                        if (element.efficiency > 100) {
                            efficiency = 100;
                        } else {
                            efficiency = element.efficiency;
                        }

                        if (efficiency > 50) {
                            var progress = `<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="${element.efficiency}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: ${element.efficiency}%">${element.efficiency}%</div></div>`;
                        } else {
                            var progress = `<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="${element.efficiency}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: ${element.efficiency}%">${element.efficiency}%</div></div>`;
                        }

                        var rowData = [
                            element.first_name + ' ' + element.last_name,
                            element.task_name + 'sqkm' + ' (#' + element.task_id + ')',
                            element.project_name + ' (#' + element.project_id + ')',
                            element.profile,
                            progress,
                            '<a href="view-efficiency.php?task_id=' + element.task_id + '"><i class="fas fa-eye"></i> view</a>'
                        ];
                        table.row.add(rowData).draw();
                    });
                }
            });
        }
    });

    $('#download-btn').click(() => {
        var user_id = $("#user_id").val();
        var method = $("#method").val();
        var product_id = $("#product_id").val();
        var task_id = $("#task_id").val();
        $.ajax({
            url: 'settings/api/efficiencyAPi.php',
            data: {
                type: 'getProjectEfficiency',
                user_id: user_id,
                method: method,
                product_id: product_id,
                task_id: task_id
            },
            dataType: 'json',
            success: function (response) {
                const extractedDataArray = [];
                const extractedData = {
                    "first_name": "First Name",
                    "last_name": "Last Name",
                    "task_id": "Task Id",
                    "area_sqkm": "Area Sqkm",
                    "project_id": "Project Id",
                    "project_name": "Project Name",
                    "total_efficiency": "Total Efficiency"
                };
                extractedDataArray.push(extractedData);
                console.log(response);
                for (const data of response) {
                    const extractedData = {
                        "first_name": data.first_name,
                        "last_name": data.last_name,
                        "task_id": data.task_id,
                        "area_sqkm": data.task_name,
                        "project_id": data.project_id,
                        "project_name": data.project_name,
                        "total_efficiency": data.efficiency
                    };
                    extractedDataArray.push(extractedData);
                }
                // console.log(extractedDataArray);
                downloadExcel(extractedDataArray);
            }
        });
    });

    $('#method').change(() => {
        var method = $('#method').val()
        if (method == 'all' || method == 'today' || method == 'monthly') {
            $('#product_id').css('display', 'none');
            $('#msg_task_id').css('display', 'none');
        }

        if (method == 'project') {
            $('#product_id').css('display', 'block');
            $('#msg_task_id').css('display', 'none');
        }

        if (method == 'task') {
            $('#msg_task_id').css('display', 'block');
            $('#product_id').css('display', 'none');
        }
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

</script>
</body>

</html>