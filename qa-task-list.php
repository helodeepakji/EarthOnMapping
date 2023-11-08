<?php

$current_page = 'task-list';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $userDetails = $_SESSION['userDetails'];
    $user_id = $_SESSION['userId'];
    $user_type = $_SESSION['userType'];
}

include 'settings/config/config.php';
if ($user_type == 'admin') {
    $sql = $conn->prepare("SELECT * FROM `assign` WHERE `status`='assign'");
    $sql->execute();
} else {
    $sql2 = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'qa'");
    $sql2->execute([$user_id]);
    $tasks2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $countQa = count($tasks2);

    $vectorSQL = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'vector'");
    $vectorSQL->execute([$user_id]);
    $vectorSQL = $vectorSQL->fetchAll(PDO::FETCH_ASSOC);
    $countVector = count($vectorSQL);

    $qcSql = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'employee' ");
    $qcSql->execute([$user_id]);
    $qcSql = $qcSql->fetchAll(PDO::FETCH_ASSOC);
    $countEmployee = count($qcSql);


    $QcSQL = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'qc'");
    $QcSQL->execute([$user_id]);
    $QcSQL = $QcSQL->fetchAll(PDO::FETCH_ASSOC);
    $countQc = count($QcSQL);
}
?>

<?php
$title = 'Tasks List || EOM ';
include 'settings/header.php'
    ?>
<style>
    table tbody td .close {
        background-color: rgb(170, 248, 218);
        display: block;
        border-radius: 20px;
        padding: 0px 8px;
        text-transform: uppercase;
    }

    .scroll-bar {
        max-height: 350px;
        overflow: scroll;
    }

    a {
        text-decoration: none;
    }

    .projectID {

        display: flex;
        margin-left: 20px;
        margin-top: 15px;
        position: absolute;
        z-index: 9;

    }

    .notiflix-loading-icon {
        width: 250px !important;
        height: 250px !important;
    }

    .overflow{
        overflow: auto;
    }

</style>

<main style="margin-top: 100px;">

    <!-- start break Modal -->
    <div class="modal fade" id="breakModal" tabindex="-1" aria-labelledby="breakModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logWorkModalLabel">Break</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body p-0">
                        <form id="addBreak">
                            <div class="row form-row mb-3">
                                <div class="col-12 col-sm-6 p-2">
                                    <div class="form-group">
                                        <label>Break Type</label>
                                        <select name="break_type" id="break_type" class="form-control" required>
                                            <option value="" Default>Select Break Type</option>
                                            <option value="break_fast">Break Fast</option>
                                            <option value="snacks">Snacks</option>
                                            <option value="lunch">Lunch</option>
                                            <option value="team_meeting">Team Meeting</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <input type="hidden" name="type" value="addQaBreak" required>
                                        <input type="hidden" id="project_id" name="project_id" value="16" required>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 p-2">
                                    <div class="form-group">
                                        <label>Time (minute)</label>
                                        <input type="number" class="form-control" name="time" min="1" value="30" required readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-row mb-3" id="team_meeting_box">

                            </div>
                            <div class="row form-row mb-3">
                                <div class="col-12 col-sm-12 p-2">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <input type="text" class="form-control" name="remarks">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" id="logWorkBtn" class="btn btn-danger">Break</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end  break Modal -->

    <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
        <a href="task-list.php" style="display: flex;align-items: center;margin: 0 10px">
            <button type="button" class="btn btn-primary position-relative">
                PRO
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $countEmployee ?>
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button></a>
        <a href="qc-task-list.php" style="display: flex;align-items: center;margin: 0 10px">
            <button type="button" class="btn btn-primary position-relative">
                QC
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $countQc ?>
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button></a>
        <a href="#" style="display: flex;align-items: center;margin: 0 10px">
            <button type="button" class="btn btn-primary position-relative">
                QA
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $countQa ?>
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button>
        </a>
        <a href="vector-task-list.php" style="display: flex;align-items: center;margin: 0 10px">
            <button type="button" class="btn btn-primary position-relative">
                Vector
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $countVector ?>
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button>
        </a>
    </div>
    <div class="container pt-1">
        <div class="d-flex" style="justify-content: flex-end;">
            <a class="btn btn-danger"  data-bs-toggle="modal" data-bs-target="#breakModal" onclick="getBreak()" style="margin :0 5px">Break</a>
            <a class="btn btn-primary" onclick="getAddAssign()" style="margin :0 5px">Start Now</a>
            <a class="btn btn-primary" onclick="getComplete()">Complete Now</a>
        </div>
        <div class="accordion accordion-flush" id="accordionFlushExample">
            <div class="accordion-item">
                <div class="row overflow">
                    <div class="col border col-8 p-3" style="width:99%;height: 500px;">
                        <div class="text-center">
                            <button type="button" class="btn bg-white btn1  text-center">QA Task List Under
                                project</button>
                        </div>

                        <div class=" d-block w-100 ">
                            <div>
                                <div class="accordion-body">
                                    <div>

                                        <table class="table table-striped ">
                                            <thead>
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Assign</th>
                                                    <th>Task ID</th>
                                                    <th>Project ID</th>
                                                    <th>Status</th>
                                                    <th>Name</th>
                                                </tr>
                                            </thead>
                                            <tbody class="scroll-bar">
                                                <?php

                                                foreach ($tasks2 as $task) {

                                                    $sql3 = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
                                                    $sql3->execute([$task['user_id']]);
                                                    $user = $sql3->fetch(PDO::FETCH_ASSOC);

                                                    $sql5 = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
                                                    $sql5->execute([$task['task_id']]);
                                                    $taskss = $sql5->fetch(PDO::FETCH_ASSOC);

                                                    $sql4 = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
                                                    $sql4->execute([$task['project_id']]);
                                                    $project = $sql4->fetch(PDO::FETCH_ASSOC);

                                                    $t_id = base64_encode($task['task_id']);

                                                    ?>
                                                    <tr>
                                                        <td><input type="checkbox" class="select_box"
                                                                data-task="<?php echo $task['task_id'] ?>"
                                                                data-project=" <?php echo $task['project_id'] ?>"
                                                                id="<?php echo $task['task_id'] ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo date('j M, Y h:i A', strtotime($task['created_at'])) ?>
                                                        </td>
                                                        <th class>
                                                            <label for="<?php echo $task['task_id'] ?>">
                                                                <?php echo $task['task_id'] ?>
                                                            </label>
                                                        </th>
                                                        <th>
                                                            <?php echo $task['project_id'] ?> (
                                                            <?php echo $project['project_name'] ?>)
                                                        </th>
                                                        <td><span class="close">
                                                                <?php echo str_replace('_', ' ', $taskss['status']) ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $user['first_name'] . ' ' . $user['last_name'] ?>
                                                        </td>
                                                    </tr>
                                                    <?php

                                                }

                                                ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
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
    var notyf = new Notyf({ position: { x: 'right', y: 'top' } });

    const storedBreakTime = new Date(localStorage.getItem('breakTime'));
    const storedBreakDuration = parseInt(localStorage.getItem('breakDuration'));
    const currentTime = new Date();

    const differenceInMilliseconds = storedBreakTime.getTime() + storedBreakDuration * 60000 - currentTime.getTime(); 
    const durationInMinutes = Math.floor(differenceInMilliseconds / (1000 * 60));
    console.log(differenceInMilliseconds);
    function setTimeAndRemoveLoader() {
        Notiflix.Loading.remove();
    }

    
    var countDownDate = storedBreakTime.setMinutes(storedBreakTime.getMinutes() + storedBreakDuration);;
    setInterval(function() {
        var now = new Date().getTime();
        var distance = countDownDate - now;
        const minutes = Math.floor(distance / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        document.getElementById("timebreaktext").innerHTML = minutes + " min " +seconds + " sec";
    },1000);
    
    Notiflix.Loading.custom({
        customSvgCode: `<svg xmlns="http://www.w3.org/2000/svg" id="NXLoadingHourglass" fill="#32c682" width="500px" height="500px" viewBox="0 0 200 200"><style>@-webkit-keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@-webkit-keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@-webkit-keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}@keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}#NXLoadingHourglass *{-webkit-animation-duration:1.2s;animation-duration:1.2s;-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite;-webkit-animation-timing-function:cubic-bezier(0,0,1,1);animation-timing-function:cubic-bezier(0,0,1,1)}</style><g data-animator-group="true" data-animator-type="1" style="-webkit-animation-name:NXhourglass1-animation;animation-name:NXhourglass1-animation;-webkit-transform-origin:50% 50%;transform-origin:50% 50%; scale: 0.5;transform-box:fill-box"><g id="NXhourglass2" fill="inherit"><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass3-animation;animation-name:NXhourglass3-animation;-webkit-animation-timing-function:cubic-bezier(.42,0,.58,1);animation-timing-function:cubic-bezier(.42,0,.58,1);-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass4" d="M100 100l-34.38 32.08v31.14h68.76v-31.14z"></path></g><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass5-animation;animation-name:NXhourglass5-animation;-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass6" d="M100 100L65.62 67.92V36.78h68.76v31.14z"></path></g><path d="M51.14 38.89h8.33v14.93c0 15.1 8.29 28.99 23.34 39.1 1.88 1.25 3.04 3.97 3.04 7.08s-1.16 5.83-3.04 7.09c-15.05 10.1-23.34 23.99-23.34 39.09v14.93h-8.33a4.859 4.859 0 1 0 0 9.72h97.72a4.859 4.859 0 1 0 0-9.72h-8.33v-14.93c0-15.1-8.29-28.99-23.34-39.09-1.88-1.26-3.04-3.98-3.04-7.09s1.16-5.83 3.04-7.08c15.05-10.11 23.34-24 23.34-39.1V38.89h8.33a4.859 4.859 0 1 0 0-9.72H51.14a4.859 4.859 0 1 0 0 9.72zm79.67 14.93c0 15.87-11.93 26.25-19.04 31.03-4.6 3.08-7.34 8.75-7.34 15.15 0 6.41 2.74 12.07 7.34 15.15 7.11 4.78 19.04 15.16 19.04 31.03v14.93H69.19v-14.93c0-15.87 11.93-26.25 19.04-31.02 4.6-3.09 7.34-8.75 7.34-15.16 0-6.4-2.74-12.07-7.34-15.15-7.11-4.78-19.04-15.16-19.04-31.03V38.89h61.62v14.93z"></path></g></g>
        <text id="timebreaktext" transform="matrix(1 0 0 1 20 200)" fill="#49BA81" font-family="'MyriadPro-Regular'" font-size="30px"></text>
        </svg>`,
    });

    setTimeout(setTimeAndRemoveLoader, differenceInMilliseconds);

    $('#break_type').change(function(){
        var break_type = $('#break_type').val();
        if(break_type == 'team_meeting'){
            $('#team_meeting_box').html(` <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Who</label>
                        <input type="text" class="form-control" name="who" required>
                    </div>
                    </div>

                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Why</label>
                        <input type="text" class="form-control" name="why" required>
                    </div>
                    </div>`);
        }else{
        $('#team_meeting_box').html('');
        }
    });

    function deleteTask(task_id) {
        $.ajax({
            url: 'settings/api/taskApi.php',
            type: 'POST',
            dataType: 'json',
            data: {
                type: 'deleteTask',
                task_id: task_id
            },
            success: function (data) {
                notyf.success(data.message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function (error) {
                console.error('Error deleting data:', error);
            }
        });
    }


    const taskArray = [];
    const projectArray = [];
    $('.select_box').change(function () {
        const task_id = $(this).data('task');
        const project_id = $(this).data('project');
        if (this.checked) {
            taskArray.push(task_id);
            projectArray.push(project_id);
        } else {
            var index = taskArray.indexOf(task_id);
            if (index !== -1) {
                taskArray.splice(index, 1);
            }
            var index = projectArray.indexOf(project_id);
            if (index !== -1) {
                projectArray.splice(index, 1);
            }
        }
        console.log(taskArray);
        console.log(projectArray);
    });

    function getAddAssign() {
        if (taskArray.length == 0) {
            notyf.error("Select Task First.");
        } else {
            $.ajax({
                url: 'settings/api/vectorTaskApi.php',
                type: 'POST',
                data: {
                    type: 'startTaskQa',
                    task_id: taskArray,
                    project_id: projectArray
                },
                dataType: 'json',
                success: function (data) {
                    notyf.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var response = JSON.parse(jqXHR.responseText);
                    notyf.error(response.message);
                }
            });
        }
    }

    function getComplete() {
        if (taskArray.length == 0) {
            notyf.error("Select Task First.");
        } else {
            $.ajax({
                url: 'settings/api/vectorTaskApi.php',
                type: 'POST',
                data: {
                    type: 'completeTaskQa',
                    task_id: taskArray,
                    project_id: projectArray
                },
                dataType: 'json',
                success: function (data) {
                    notyf.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var response = JSON.parse(jqXHR.responseText);
                    notyf.error(response.message);
                }
            });
        }
    }

    $('#addBreak').submit(function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'settings/api/breakApi.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                notyf.success(response.message);
                const currentTime = new Date();
                const currentTimeString = currentTime.toISOString();
                localStorage.setItem('breakTime', currentTimeString);
                localStorage.setItem('breakDuration', response.time);
                $('#addBreak').modal('hide');
                setTimeout(() => {
                location.reload();
                }, 1000);
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    });


</script>

</body>

</html>