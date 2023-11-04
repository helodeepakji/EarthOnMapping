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
                                <option value="all" >All</option>
                                <option value="project">Project</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="col-lg-3 p-2">
                            <select name="product_id" class="form-control" id="product_id" style="display:none">
                                <option value="" default>Select Project</option>
                                <?php
                                    foreach ($projectlist as $value) {
                                        echo '<option value="'.$value['project_id'].'">'.$value['project_name'].'</option>';
                                    }
                                ?>  
                            </select>
                            <div id="msg_task_id" style="display:none;">
                                <select name="task_id" id="task_id" class="form-control select2" style="width: 100%;">
                                    <option value="" default>Select Task</option>
                                    <?php
                                        foreach ($tasklist as $value) {
                                            echo '<option value="'.$value['task_id'].'">'.$value['task_id'].'</option>';
                                        }
                                    ?>  
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3" style="display: flex; align-items: center;justify-content: space-evenly;">
                            <button class="btn btn-primary" id="search-btn" style=" width: 40%;">Search</button>
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
        if (user_id) {
            $.ajax({
                url: 'settings/api/efficiencyAPi.php',
                data: {
                    type: 'getProjectEfficiency',
                    user_id: user_id,
                    method : method,
                    product_id : product_id,
                    task_id : task_id
                },
                dataType: 'json',
                success: function (response) {
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
        } else {
            notyf.error("Select User First.");
        }
    });

    $('#download-btn').click(() => {
        var user_id = $("#user_id").val();
        var method = $("#method").val();
        var product_id = $("#product_id").val();
        var task_id = $("#task_id").val();
        if (user_id) {
            $.ajax({
                url: 'settings/api/efficiencyAPi.php',
                data: {
                    type: 'getProjectEfficiency',
                    user_id: user_id,
                    method : method,
                    product_id : product_id,
                    task_id : task_id
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
                    downloadExcel(extractedDataArray);
                }
            });
        } else {
            notyf.error("Select User First.");
        }
    });

    $('#method').change(()=>{
        var method = $('#method').val()
        if(method == 'all'){
            $('#product_id').css('display','none');
            $('#msg_task_id').css('display','none');
        }
        
        if(method == 'project'){
            $('#product_id').css('display','block');
            $('#msg_task_id').css('display','none');
        }
        
        if(method == 'task'){
            $('#msg_task_id').css('display','block');
            $('#product_id').css('display','none');
        }
    });

    // function downloadExcel(data){
    //     $.ajax({
    //         url: "settings/downloadExcel.php",
    //         type: 'POST',
    //         data: {
    //             data : data
    //         } ,
    //         xhrFields: {
    //             responseType: 'blob'
    //         },
    //         success: function(result){
    //             var a = document.createElement('a');
    //             var url = window.URL.createObjectURL(result);
    //             a.href = url;
    //             a.download = "example.xlsx"; // Set the desired file name
    //             document.body.appendChild(a);
    //             a.click();
    //             window.URL.revokeObjectURL(url);
    //             notyf.success("Excel File Download SuccessFull");
    //         }
    //     });
    // }

</script>
</body>

</html>