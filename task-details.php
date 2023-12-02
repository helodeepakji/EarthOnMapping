<?php
$current_page = 'index';
include 'settings/config/config.php';
session_start();

if (isset($_COOKIE['userId'])) {
  $_SESSION['userId'] = $_COOKIE['userId'];
  $_SESSION['loggedin'] = true;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}
$task_id = base64_decode($_GET['task_id']);
$sql3 = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
$sql3->execute([$task_id]);
$tasks = $sql3->fetch(PDO::FETCH_ASSOC);
$project_id = $tasks['project_id'];
$user_id = $_SESSION['userId'];


$project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
$project->execute([$tasks['project_id']]);
$project = $project->fetch(PDO::FETCH_ASSOC);


if (($tasks['status'] == 'ready') || ($tasks['status'] == 'in_progress')) {
  if ($tasks['is_qc_failed'] == 1) {
    $part_estimated_hour = 0.375;
  } else {
    $part_estimated_hour = 0.75;
  }
} else if (($tasks['status'] == 'ready_for_qc') || ($tasks['status'] == 'qc_in_progress')) {
  if ($tasks['is_qa_failed'] == 1) {
    $part_estimated_hour = 0.1;
  } else {
    $part_estimated_hour = 0.20;
  }
} else if (($tasks['status'] == 'ready_for_qa') || ($tasks['status'] == 'qa_in_progress')) {
  if($project['vector'] == 1){
    $part_estimated_hour = 0.02;
  }else{
    $part_estimated_hour = 0.05;
  }
}else if (($tasks['status'] == 'ready_for_vector') || ($tasks['status'] == 'assign_vector') || ($tasks['status'] == 'vector_in_progress')){
  $part_estimated_hour = 0.03;
} else {
  $part_estimated_hour = 1;
}


$sql = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? ORDER BY `created_at` DESC");
$sql->execute([$task_id, $project_id]);
$assigns = $sql->fetch(PDO::FETCH_ASSOC);


if ($tasks['status'] == 'vector_in_progress') {
  $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_vector' ORDER BY `id` DESC;");
  $checkQC->execute([$task_id, $project_id]);
  $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
  if ($checkQC) {
    $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
    $totalWorkPercentage->execute([$task_id, $project_id, $checkQC['id']]);
    $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
    if (!$totalWorkPercentage) {
      $totalWorkPercentage['total_percentage'] = 0;
    }
  }

}else if ($tasks['status'] == 'qc_in_progress') {
  $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qc' ORDER BY `id` DESC;");
  $checkQC->execute([$task_id, $project_id]);
  $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
  if ($checkQC) {
    $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
    $totalWorkPercentage->execute([$task_id, $project_id, $checkQC['id']]);
    $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
    if (!$totalWorkPercentage) {
      $totalWorkPercentage['total_percentage'] = 0;
    }
  }

}else if ($tasks['status'] == 'qa_in_progress') {
  $checkQC = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = 'assign_qa' ORDER BY `id` DESC;");
  $checkQC->execute([$task_id, $project_id]);
  $checkQC = $checkQC->fetch(PDO::FETCH_ASSOC);
  if ($checkQC) {
    $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
    $totalWorkPercentage->execute([$task_id, $project_id, $checkQC['id']]);
    $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
    if (!$totalWorkPercentage) {
      $totalWorkPercentage = 0;
    }
  }

} else if ($tasks['status'] == 'in_progress') {
  $checkfailure = $conn->prepare("SELECT * FROM work_log WHERE `task_id` = ? AND `project_id` = ? AND (change_type = 'qc_failure_ressignment' OR change_type = 'qa_failure_ressignment') ORDER BY `id` DESC;");
  $checkfailure->execute([$task_id, $project_id]);
  $checkfailure = $checkfailure->fetch(PDO::FETCH_ASSOC);
  if ($checkfailure) {
    $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ?");
    $totalWorkPercentage->execute([$task_id, $project_id, $checkfailure['id']]);
    $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
  } else {
    $totalWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_percentage FROM `work_log` WHERE `task_id` = ? AND `project_id` = ?");
    $totalWorkPercentage->execute([$task_id, $project_id]);
    $totalWorkPercentage = $totalWorkPercentage->fetch(PDO::FETCH_ASSOC);
  }
}


function getTimeAgo($givenTimestamp)
{

  $givenUnixTimestamp = strtotime($givenTimestamp);
  $currentUnixTimestamp = time();
  $timeDifference = $currentUnixTimestamp - $givenUnixTimestamp;
  $days = floor($timeDifference / (60 * 60 * 24));
  $hours = floor(($timeDifference - ($days * 60 * 60 * 24)) / (60 * 60));
  $minutes = floor(($timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60)) / 60);
  $seconds = $timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60) - ($minutes * 60);
  $timeAgo = "";
  if ($days > 0) {
    $timeAgo .= $days . " days ";
    return $timeAgo;
  }
  if ($hours > 0) {
    $timeAgo .= $hours . " hr ";
  }
  if ($minutes > 0) {
    $timeAgo .= $minutes . " min ";
  }
  if ($timeAgo == "") {
    $timeAgo = "just now";
  }

  return $timeAgo;
}

function diffDateInDay($firstDate, $secondDate)
{


  try {
    $firstDate = new DateTime($firstDate);
    $secondDate = new DateTime($secondDate);
    if ($firstDate > $secondDate) {
      return 0;
    }
    $interval = $firstDate->diff($secondDate);
    return $interval->days;
  } catch (Exception $e) {
    return 0;
  }
}

$sqli = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? ORDER BY `created_it` DESC");
$sqli->execute([$task_id]);
$WorkLogs = $sqli->fetchAll(PDO::FETCH_ASSOC);
function logHour($WorkLogs)
{
  $total_hours = 0;
  $total_minutes = 0;
  if (!$WorkLogs) {
    return 0;
  }
  foreach ($WorkLogs as $WorkLog) {
    $parts = explode(' ', $WorkLog['taken_time']);
    $hours = intval(str_replace('H', '', $parts[0]));
    $minutes = intval(str_replace('M', '', $parts[1]));

    $total_hours += $hours;
    $total_minutes += $minutes;
  }
  $extra_hours = floor($total_minutes / 60);
  $total_hours += $extra_hours;
  $total_minutes %= 60;

  $total_hours += $total_minutes / 60;
  if ($total_hours == 0) {
    return 0;
  }

  return $total_hours;
}


$users = $conn->prepare("SELECT * FROM `users` WHERE `user_type` = 'user'");
$users->execute();
$users = $users->fetchAll(PDO::FETCH_ASSOC);

?>



<?php
$title = 'Tasks Details || EOM ';
include 'settings/header.php'
  ?>
<style>
  .modal-date {
    margin-left: 35px;
    width: 235px;

  }

  .span {
    font-weight: 600;
  }

  .Assign-name {
    width: 170px;
    border: none;
    padding: 5px;
  }

  .option {
    border: none;
    background-color: #F4F6F6;
  }

  .activity-name {
    background-color: #0052cc;
    color: white;
    padding: 5px;
    border-radius: 50%;

    letter-spacing: 1px;
    /* margin: 30px 10px; */
    margin-top: 20px;
    text-transform: uppercase;

  }

  .Activity-short-name {
    background-color: #0052cc;
    color: white;
    padding: 5px;
    border-radius: 15px;
    letter-spacing: 1px;

  }

  .accordion-button {
    padding: 10px;
  }

  .scroll-bar {
    max-height: 500px;
    overflow-y: scroll;
  }

  .accordion-button::after {

    background-image: none;

  }

  .accordion-button:not(.collapsed)::after {
    background-image: none;
    transform: rotate(0deg);
  }
  .notiflix-loading-icon {
      width: 250px !important;
      height: 250px !important;
  }
</style>


<!--Main Navigation-->

<!--Main layout-->
<main style="margin-top: 100px;">

  <!-- start modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Log Work</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <form id="addLogWork">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Previous Status</label>
                    <input type="text" class="form-control" name="prev_status" id="prev_status" readonly required>
                    <input type="hidden" class="form-control" name="type" value="addLogWork" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $tasks['project_id'] ?>"
                      required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Next Status</label>
                    <input type="text" id="next_status" class="form-control" name="next_status" readonly required>
                  </div>
                </div>
              </div>
              <?php
              if ($_SESSION['userType'] != 'admin') {
                ?>

                <div class="row form-row mb-3">
                  <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                      <label>Start Date</label>
                      <input type="text" name="start_date" id="datepicker" class="form-control" readonly required>
                    </div>
                  </div>
                  <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                      <label>Remarks</label>
                      <input type="text" class="form-control" name="remarks">
                    </div>
                  </div>
                </div>
                <?php
              }
              ?>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Log Work</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->

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
                    <select name="break_type" id="break_type"  class="form-control" required>
                        <option value="" Default>Select Break Type</option>
                        <option value="break_fast">Break Fast</option>
                        <option value="snacks" >Snacks</option>
                        <option value="lunch" >Lunch</option>
                        <option value="team_meeting" >Team Meeting</option>
                        <option value="other" >Other</option>
                    </select>
                    <input type="hidden"  name="type" value="addBreak" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $tasks['project_id'] ?>"
                      required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Time (minute)</label>
                    <input type="number" id="break_time" class="form-control" name="time" min="1" value="30" required readonly>
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
  
  <!-- start modal -->
  <div class="modal fade" id="logWorkModal" tabindex="-1" aria-labelledby="logWorkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logWorkModalLabel">Log Work</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <form id="addPauseLogWork">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Status</label>
                    <input type="text" class="form-control" name="status" id="status"
                      value="<?php echo $tasks['status'] ?>" readonly required>
                    <input type="hidden" class="form-control" name="type" value="addPauseLogWork" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $tasks['project_id'] ?>"
                      required>
                  </div>
                </div>

                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Work Percentage ( <span id="per_val" style="color:green">
                        <?php echo $totalWorkPercentage['total_percentage']; ?>%
                      </span> )</label>
                    <input type="range" id="work_percentage"
                      min="<?php echo $totalWorkPercentage['total_percentage']; ?>" max="100" step="10"
                      class="form-range" name="work_percentage" value="0" style="height: 40px;" required>
                  </div>
                </div>
              </div>
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Hour</label>
                    <input type="number" class="form-control" id="log_hour" name="hour" readonly required>
                  </div>
                </div>
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Minute</label>
                    <input type="number" class="form-control" id="log_minute" min="0" max="59" name="minute" readonly required>
                  </div>
                </div>
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
                <button type="submit" id="logWorkBtn" class="btn btn-primary">Log Work</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->

  <!-- start modal -->
  <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <form id="addComment">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-12 p-2">
                  <div class="form-group">
                    <label>Comment</label>
                    <input type="text" class="form-control" name="comment" id="comment" required>
                    <input type="hidden" class="form-control" name="type" value="addComment" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Comment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->

  <!-- start modal -->
  <div class="modal fade" id="reAssignModal" tabindex="-1" aria-labelledby="reAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Assign Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <form id="reAssignTaskForm">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-12 p-2">
                  <div class="form-group">
                    <label>Select Employee</label>
                    <select class="form-control select2" name="user_id" required>
                      <option value="">Choose Employee</option>
                      <?php
                      foreach ($users as $user) {
                        echo '<option value="' . $user['id'] . '" ' . $select . '>' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                      }
                      ?>
                    </select>
                    <input type="hidden" class="form-control" name="type" value="reAssignTask" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">ReAssign</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->

  <!-- start modal -->
  <div class="modal fade" id="failureModal" tabindex="-1" aria-labelledby="failureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Failure </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <?php if ($tasks['status'] == 'qa_in_progress') { ?>
              <form id="qaFailureTaskForm">
              <?php } else { ?>
                <form id="failureTaskForm">
                <?php } ?>

                <div class="row form-row mb-3">
                  <div class="col-12 col-sm-12 p-2">
                    <div class="form-group">

                      <?php if ($tasks['status'] == 'qa_in_progress') { ?>
                        <label>Select Qc</label>
                      <?php } else { ?>
                        <label>Select Employee</label>
                      <?php } ?>
                      <select class="form-control select2" name="user_id" required>
                        <?php if ($tasks['status'] == 'qa_in_progress') { ?>
                          <option value="">Choose Qc</option>
                        <?php } else { ?>
                          <option value="">Choose Employee</option>
                        <?php } ?>
                        <?php
                        foreach ($users as $user) {
                          echo '<option value="' . $user['id'] . '" ' . $select . '>' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                        }
                        ?>
                      </select>
                      <input type="hidden" class="form-control" name="type" value="failureTask" required>
                      <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                      <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>" required>
                      <input type="hidden" id="hour_failure" name="hour" value="<?php echo $task_id ?>" required>
                      <input type="hidden" id="minute_failure" name="minute" value="<?php echo $project_id ?>" required>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Comment</button>
                </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->


  <div class="container">
    <div class="sec">
      <div class="home">
        <div class="nav d-flex">
          <div class="logo">
            <img src="https://jira.atlassian.com/secure/projectavatar?pid=18511&avatarId=105990" alt="jksjds">
          </div>
          <div class="head">
            <div class="ms-2">
              <li class=" pb-1 text-primary">
                <?php echo $project['project_name'] . ' (project id : ' . $project_id . ')' ?> /
                <?php echo ' (task id : ' . $task_id . ')' ?>
              </li>
              <div class="d-flex">
                <div id="editableText" contenteditable="false">
                  <p class="fw-bold" id="task_summary">
                    <?php echo $tasks['summary'] ?>
                  </p>
                </div>
                <?php
                if ($_SESSION['userType'] == 'admin') {
                  ?>
                  <button id="editButton" class="btn btn-primary  ms-3">Edit</button>
                  <?php
                }
                ?>
              </div>
            </div>
            <div>
            </div>
          </div>
        </div>
        <div class="main-btn d-flex justify-content-between">
          <div class="btn-left mt-4 ">
            <button type="button" class="btn bg-btn" data-bs-toggle="modal" data-bs-target="#commentModal"><i
                class="fa-sharp fa-regular fa-comment me-2"></i>Comment</button>
            <!-- <button type="button" class="btn bg-btn">Assign</button>    -->
            <?php if (($tasks['status'] == 'qc_in_progress') || ($tasks['status'] == 'qa_in_progress')) { ?>
              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1"
                data-bs-toggle="dropdown" aria-expanded="false">
                More
              </button>

              <?php
              if ((($tasks['is_qa_failed'] == 0) || ($tasks['status'] == 'qa_in_progress'))) {
                ?>
                <ul class="dropdown-menu" id="dropdown_menu" aria-labelledby="dropdownMenuButton1">
                  <li id="failureModalbtn"><a class="dropdown-item">Fail</a></li>
                  <!-- <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="nextStatus()">Pass</a></li> -->
                </ul>
                <?php
              }
            }
            if (($tasks['status'] == 'ready') || ($tasks['status'] == 'assign_qc') || ($tasks['status'] == 'assign_qa') || ($tasks['status'] == 'assign_vector')) {
              ?>
              <button type="button" onclick="inProgress('<?php echo $task_id ?>',<?php echo $project_id ?>)"
                class="btn bg-btn">Start Work</button>
              <?php
            } else {
              ?>
              <?php
                if ($_SESSION['userType'] != 'admin') {
                  $contionues = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ? AND `next_status` = 'Pause Work' ORDER BY `work_log`.`id` DESC");
                  $contionues->execute([$task_id,$project_id,$tasks['status']]);
                  $contionues = $contionues->fetch(PDO::FETCH_ASSOC);
                  if($contionues){
                    $logbtnflg = 0;
                  }else{
                    $logbtnflg = 1;
                  }
                ?>
                <?php if($logbtnflg == 1){ ?>
                  <button type="button" id="#logWorkModalBtn" class="btn bg-btn" data-bs-toggle="modal"
                    data-bs-target="#logWorkModal" onclick="getLastLog()" style="background: #098a01;color: white;">Log Work</button>
                  <button type="button" id="#breakModal" class="btn bg-btn" data-bs-toggle="modal"
                    data-bs-target="#breakModal" style="background: #c71b1b;color: white;">Break</button>
                <?php }else{ ?>
                  <button type="button" class="btn bg-btn" onclick="getContinueWork()" style="background: #098a01;color: white;">Continue Work</button>
                <?php } ?>
                <?php
              }
            }
            ?>
            <?php if (($tasks['status'] == 'ready' || $tasks['status'] == 'in_progress') && ($_SESSION['userType'] == 'admin') && ($tasks['is_qc_failed'] == 0)) { ?>
              <button type="button" class="btn bg-btn" data-bs-toggle="modal"
                data-bs-target="#reAssignModal">Re-Assign</button>
            <?php } ?>
          </div>
        </div>

        <div class=" d-flex px-3 pt-2">
          <div class="col col-7">
            <div class="Details">
              <p class="fw-bold">Details</p>
              <div class="row ms-3">
                <div class="col col-3 text-muted">Type</div>
                <div class="col col-3"><i class="bi bi-align-middle"></i>Sub-task</div>
                <div class="col col-3 text-muted">Status</div>
                <div class="col col-3 indipended"><span class="text-uppercase px-1 development-text fw-bold"
                    id="current_status">
                    <?php echo str_replace('_', ' ', $tasks['status']) ?>
                  </span>
                  <?php
                  if (($tasks['status'] != 'ready') && ($tasks['status'] != 'assign_qc') && ($tasks['status'] != 'assign_qa')) {
                    ?>
                    <!-- <a class="btn " data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="nextStatus()"><i style="color: black;font-size: 20px" class="fas fa-caret-down"></i></a> -->
                    <?php
                  }
                  ?>
                </div>
              </div>
              <div class="row ms-3">
                <div class="col col-3 text-muted">Priority</div>
                <div class="col col-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="12 "
                    fill="currentColor" class="bi bi-chevron-double-up text-danger fw-bold" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                      d="M7.646 2.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 3.707 2.354 9.354a.5.5 0 1 1-.708-.708l6-6z" />
                    <path fill-rule="evenodd"
                      d="M7.646 6.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 7.707l-5.646 5.647a.5.5 0 0 1-.708-.708l6-6z" />
                  </svg>
                  <?php echo $tasks['complexity'] ?>
                </div>
                <div class="col col-3"></div>
                <div class="col col-3">(View-workflow)</div>
              </div>
              <div class="row ms-3">
                <div class="col col-3"></div>
                <div class="col col-3"></div>
                <div class="col col-3 text-muted">Resolution</div>
                <div class="col col-3 ">Unresolved</div>
              </div>
              <!-- <div class="row ms-3">
                        <div class="col col-3 text-muted">Labels</div>
                        <div class="col-9">
                            <p>None</p>
                        </div>                     
                    </div> -->
              <!-- <div class="row ms-3">
                        <div class="col col-3 text-muted">Last comment</div>
                        <div class="col col-9">
                            <p class="  " type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-circle" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/>
                                  </svg> The request tog has been removed please check with follwing apk
                            </p>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item" href="#">POS change request / PCR-215 Need to do certification</a></li>
                               
                              </ul>
                              
                        </div>                     
                    </div> -->

            </div>
            <div class="Descrption mt-3">
              <p class="fw-bold">Descrption</p>
              <div class="row ms-3 mt-1">
                <div class="col-12 ">

                  <p>
                    <?php echo $tasks['description'] ?>
                  </p>

                </div>
              </div>

            </div>
            <div class="Descrption mt-3">
              <p class="fw-bold mb-2">Attachment</p>
              <div class="input-group ms-3 mt-1">
                <input type="file" class="form-control" id="uploadAttechment" aria-describedby="inputGroupFileAddon04"
                  name="attachment" aria-label="Upload">
                <button class="btn btn-outline-secondary" type="button" id="uploadAttechment_btn">Button</button>
              </div>

            </div>
            <div class="Descrption mt-3">
              <p class="fw-bold mb-2"> Activity</p>
              <form action="">
                <div class="d-flex border mt-1 ms-3 ">
                  <div class="accordion p-3" id="accordionFlushExample">
                    <div class="">
                      <div class="row">
                        <div class="col  d-flex">
                          <div class="   ">
                            <h2 class="accordion-header" id="flush-headingOne">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne" aria-expanded="false"
                                aria-controls="flush-collapseOne">
                                <div class="me-2">
                                  Activity
                                </div>
                              </button>
                            </h2>
                          </div>
                          <div class=" ">
                            <h2 class="accordion-header" id="flush-headingTwo">
                              <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseTwo" aria-expanded="true" id="btnradio4"
                                aria-controls="flush-collapseTwo">
                                <div class="" id="">
                                  <label class=" " for="btnradio4">Comment</label>
                                </div>
                              </button>
                            </h2>
                          </div>
                          <div class=" ">
                            <h2 class="accordion-header" id="flush-headingThree">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseThree" aria-expanded="false"
                                aria-controls="flush-collapseThree">
                                <div>WorkLog</div>
                              </button>
                            </h2>
                          </div>
                        </div>
                        <div class="col  col-12">
                          <div class=" d-block w-100 ">
                            <!-- <div class="tab-pane fade" id="recent-jobs" role="tabpanel" aria-labelledby="recent-jobs-tab">
                                    <div class="job-box card mt-1">
                                        <div class=" scroll-bar" id="">
                                            <div class="d-flex mt-1">
                                                <div><span class="activity-name">AS</span></div>
                                                <div>
                                                    <span>Anil Sahoo</span>
                                                    <p class="d-inline text-muted">added a comment - 4day age at 10:29 PM</p>
                                                    <p class="text-muted">Code added in the requestof getSdui api</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="d-flex">
                                                <div><span class="activity-name">SD</span></div>
                                                <div>
                                                    <span>Sumit Dash</span>
                                                    <p class=" text-muted">Change the Assignee july 1, 2023 at 10:29 PM</p>
                                                    <p class="text-muted"><span class="">Unssigned <i
                                                                class="bi bi-arrow-right"></i></span><span
                                                            class="Activity-short-name mx-2">SD</span>Sumit Dash </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                            <div id="flush-collapseOne" class="accordion-collapse collapse"
                              aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                              <div class="accordion-body mt-2">
                                <div class=" scroll-bar" id="">
                                  <div class="d-flex mt-1">
                                    <div><span class="activity-name">AS</span></div>
                                    <div>
                                      <span>Anil Sahoo</span>
                                      <p class="d-inline text-muted">added a comment - 4day age at 10:29 PM</p>
                                      <p class="text-muted">Code added in the requestof getSdui api</p>
                                    </div>
                                  </div>
                                  <hr>
                                  <div class="d-flex">
                                    <div><span class="activity-name">SD</span></div>
                                    <div>
                                      <span>Sumit Dash</span>
                                      <p class=" text-muted">Change the Assignee july 1, 2023 at 10:29 PM</p>
                                      <p class="text-muted"><span class="">Unssigned <i
                                            class="bi bi-arrow-right"></i></span><span
                                          class="Activity-short-name mx-2">SD</span>Sumit Dash </p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class=" d-block w-100 ">
                            <!-- <div class="tab-pane fade show active" id="featured-jobs" role="tabpanel" 
                              aria-labelledby="featured-jobs-tab"> 
                                <div class="job-box bookmark-post card mt-1"> -->
                            <div id="flush-collapseTwo" class="accordion-collapse collapse show"
                              aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                              <div class="accordion-body mt-2">
                                <div class=" scroll-bar" id="commentBox">
                                  <?php

                                  $comments = $conn->prepare("SELECT * FROM `comments` WHERE `task_id` = ?AND `project_id` = ?  ORDER BY `created_at` DESC");
                                  $comments->execute([$task_id, $project_id]);
                                  $comments = $comments->fetchAll(PDO::FETCH_ASSOC);
                                  foreach ($comments as $comment) {

                                    $commentuser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                    $commentuser->execute([$comment['user_id']]);
                                    $commentuser = $commentuser->fetch(PDO::FETCH_ASSOC);

                                    echo '
                                            <div class="d-flex mt-1">
                                              <div><span class="activity-name">' . $commentuser['first_name'][0] . '' . $commentuser['last_name'][0] . '</span></div>
                                              <div>
                                                <span class="m-2">' . $commentuser['first_name'] . ' ' . $commentuser['last_name'] . '</span>
                                                <p class="d-inline text-muted">added a comment - ' . getTimeAgo($comment['created_at']) . '</p>
                                                <p class="text-muted">' . $comment['comment'] . '</p>
                                              </div>
                                            </div>
                                            <hr>
                                          
                                          ';
                                  }

                                  ?>
                                </div>
                              </div>
                            </div>
                          </div>
                          <!-- Log-work -->
                          <div class=" d-block w-100 ">
                            <!-- <div class="tab-pane fade" id="freelancer" role="tabpanel" aria-labelledby="freelancer-tab">    
                                <div class="job-box card mt-1"> -->
                            <div id="flush-collapseThree" class="accordion-collapse collapse"
                              aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                              <div class="accordion-body mt-2">
                                <div class=" scroll-bar" id="">
                                  <?php

                                  // $sqli = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ?  AND `task_id` = ? ORDER BY `created_it` DESC");
                                  // $sqli->execute([$assigns['user_id'],$task_id]);
                                  // $WorkLogs = $sqli->fetchAll(PDO::FETCH_ASSOC);
                                  

                                  foreach ($WorkLogs as $WorkLog) {
                                    $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                    $sqluser->execute([$WorkLog['user_id']]);
                                    $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);

                                    echo '<div class="d-flex mb-3 mt-2">
                                                <div><span class="activity-name" style="text-transform: uppercase;">' . $usersdata['first_name'][0] . '' . $usersdata['last_name'][0] . '</span></div>
                                                <div>
                                                  <span class="m-2"> ' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</span>
                                                  <p class="d-inline text-muted m-0">Logged </p class="m-0"><span>' . getTimeAgo($WorkLog['created_it']) . '</span> ago<p class="d-flex d-block m-0"></p>
                                                  <p class="text-muted ">Remark : ' . $WorkLog['remarks'] . '</p>
                                                  <p class="text-muted ">Time : ' . $WorkLog['taken_time'] . '</p>
                                                </div>
                                              </div>
                                              <hr>';
                                  }

                                  ?>

                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>

          </div>

          <div class="col px-3 ms-5 right">
            <div class="people">
              <p class="fw-bold">People</p>
              <div class="row ms-3">
                <div class="col-4 text-muted">Assigness</div>
                <div class="col-8">
                  <select name="" id="" class=" form-control Assign-name">
                    <?php
                    $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                    $sqluser->execute([$assigns['user_id']]);
                    $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                    echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                    ?>
                    <!-- <option value="">Sushant behera</option>
                              <option value="">Abhinash</option>
                              <option value="">bibhu</option> -->

                  </select>
                  <!-- <li class="text-primary">Assign to me</li> -->
                </div>
              </div>
              <div class="report row ms-3 mt-1">

                <div class="col-4 text-muted">
                  <p>Report</p>
                </div>
                <div class="col-8">
                  <select name="" id="" class=" form-control  Assign-name">
                    <?php
                    $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `user_type` = ?");
                    $sqluser->execute(["admin"]);
                    $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                    echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                    ?>
                  </select>


                </div>
              </div>
              <!-- <div class="voter row ms-3 ">
                        <div class="col-4 text-muted">Vote</div>
                        <div class="col-8">Vote for this issue</div>
                    </div>
                    <div class="watcher row ms-3 mt-1">
                        <div class="col-4 text-muted">Watcher</div>
                        <div class="col-8">Start watching this issue</div>
                    </div> -->
            </div>
            <!-- <div class="date-date  ">
                    <p class="fw-bold">Dates</p>
                    <div class="row ms-3 mb-1">
                        <div class="col-4 ">Create:</div>
                        <div class="col-8 "> 
                          <input type="text" class="form-control Attachment-input" id="datepicker" placeholder="Select a date"  name="start_date">
                        </div>
                    </div>
                    <div class="row ms-3 mb-1">
                        <div class="col-4">Update:</div>
                        <div class="col-8 "><input type="text" class="form-control Attachment-input" id="datepicker1" placeholder="Select a date" name="start_date"></div>
                    </div>
                    <div class="row ms-3 mb-1">
                        <div class="col-4 ">Start date:</div>
                        <div class="col-8 "><input type="text" class="form-control Attachment-input" id="datepicker2" placeholder="Select a date" name="start_date"></div>
                    </div>
                    <div class="row ms-3 mb-1">
                        <div class="col-4 ">End date:</div>
                        <div class="col-8" ><input type="text" class="form-control Attachment-input" id="datepicker3" placeholder="Select a date" name="start_date"></div>
                    </div>
                </div> -->
            <div class="Time-traking ">
              <p class="fw-bold">Time Traking</p>
              <div class="row ms-3 mb-2">
                <div class="col-4 ">Estimated</div>
                <div class="col-6 mt-1">
                  <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="25"
                      aria-valuemin="0" aria-valuemax="100">100%</div>
                  </div>

                </div>
                <div class="col ms-2">
                  <?php echo $tasks['estimated_hour'] * $part_estimated_hour ?>h
                </div>
              </div>

              <?php

              if ($assigns['role'] == 'qc') {
                $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
                $workHoursforTime->execute([$task_id, 'assign_qc']);
                $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);
              }

              if ($assigns['role'] == 'qa') {
                $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
                $workHoursforTime->execute([$task_id, 'assign_qa']);
                $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);
                $WorkLogs = $workHoursforTime;
              }
              
              if ($assigns['role'] == 'vector') {
                $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
                $workHoursforTime->execute([$task_id, 'assign_vector']);
                $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);
                $WorkLogs = $workHoursforTime;
              }

              if ($tasks['is_qc_failed'] == 1) {
                $testSql = $conn->prepare("SELECT * FROM `work_log` WHERE `change_type` = 'qc_failure_ressignment' AND `task_id` = ? AND `project_id` = ? ORDER BY `id` DESC;");
                $testSql->execute([$task_id, $project_id]);
                $testSql = $testSql->fetch(PDO::FETCH_ASSOC);

                $sql_query = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `id` > ? AND `change_type` IS NULL AND `prev_status` = 'in_progress'");
                $sql_query->execute([$task_id, $project_id, $testSql['id']]);
                $sql_query = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                $WorkLogs = $sql_query;

              }

              if ($workHoursforTime) {
                $sql_query = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `id` > ? ");
                $sql_query->execute([$task_id, $workHoursforTime['id']]);
                $sql_query = $sql_query->fetchAll(PDO::FETCH_ASSOC);
                $WorkLogs = $sql_query;
              }



              ?>

              <div class="row ms-3 mb-1">
                <div class="col-4 ">Remaining</div>
                <div class="col-6 mt-1">
                  <div class="progress">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php
                    $totalWorkHours = $tasks['estimated_hour'] * $part_estimated_hour;
                    $percentageCompletion = (($totalWorkHours - logHour($WorkLogs)) / $totalWorkHours) * 100;

                    if ($percentageCompletion < 0) {
                      $percentageCompletion = 0;
                      echo $percentageCompletion;
                    } else {
                      echo $percentageCompletion;
                    }
                    ?>%">
                      <?php echo number_format($percentageCompletion, 2); ?>%
                    </div>
                  </div>
                </div>
                <div class="col ms-2">
                  <?php //echo diffDateInDay(date('Y-m-d'),$tasks['end_date']) 
                  $totalWorkHours = $tasks['estimated_hour'] * $part_estimated_hour;
                  echo $totalWorkHours - logHour($WorkLogs) > 0 ? number_format($totalWorkHours - logHour($WorkLogs), 3) : 0;
                  ?>h
                </div>
                <!-- <div class="col ms-3">3d</div> -->
              </div>
              <div class="row ms-3 mb-1">
                <div class="col-4 ">Logged</div>
                <div class="col-6 mt-1">
                  <div class="progress">
                    <div class="progress-bar bg-danger" role="progressbar" style="width:<?php
                    $loggedHours = logHour($WorkLogs);
                    $totalWorkHours = $tasks['estimated_hour'] * $part_estimated_hour; // Assuming 8 hours per work day
                    
                    if ($totalWorkHours > 0) {
                      $percentageCompletion = ($loggedHours / $totalWorkHours) * 100;
                      echo $percentageCompletion;
                    } else {
                      echo "0"; // Default value or error message
                    }
                    ?>%">
                      <?php echo number_format($percentageCompletion, 2); ?>%
                    </div>
                  </div>

                </div>
                <div class="col-1 ms-2">
                  <?php echo number_format(logHour($WorkLogs), 2); ?>h
                </div>
              </div>

            </div>

            <div class="Time-traking ">
              <p class="fw-bold">Attachment <a
                  style="margin-left: 45%;color: #0d6efd;font-weight: 500;text-decoration: none;cursor:pointer"
                  id="downloadButton">Download All</a></p>
              <?php
              if ($tasks['attachment'] != '') {
                ?>
                <div class="mt-3">
                  <div class="row ms-3 mb-2">
                    <div class="col-2" style="display: flex;align-items: center;justify-content: center;font-size: 35px">
                      <i class="fas fa-file-image"></i></div>
                    <div class="col-10">
                      <div class="">
                        <a class="attachment_file_download" href="upload/attachment/<?php echo $tasks['attachment'] ?>"
                          target="_blank" style="text-decoration:none">
                          <?php echo $tasks['attachment'] ?>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col ms-2" style="text-align: right;">
                    <?php echo getTimeAgo($tasks['create_at']) ?>
                  </div>
                </div>
                <hr>
                <?php
              }

              $attachments = $conn->prepare("SELECT * FROM `attachment` WHERE `task_id` = ?");
              $attachments->execute([$task_id]);
              $attachments = $attachments->fetchAll(PDO::FETCH_ASSOC);
              foreach ($attachments as $attachment) {
                ?>

                <div class="mt-3">
                  <div class="row ms-3 mb-2">
                    <div class="col-2" style="display: flex;align-items: center;justify-content: center;font-size: 35px">
                      <i class="fas fa-file-image"></i></div>
                    <div class="col-8">
                      <div class="">
                        <a class="attachment_file_download"
                          href="upload/attachment/<?php echo $attachment['attachment'] ?>" target="_blank"
                          style="text-decoration:none">
                          <?php echo $attachment['attachment'] ?>
                        </a>
                      </div>
                    </div>
                    <div class="col-2"
                      style="display: flex;align-items: center;justify-content: center;font-size: 18px;color:red;cursor: pointer;">
                      <i class="fas fa-trash"
                        onclick="deleteAttachment(<?php echo $attachment['id'] ?>,'<?php echo $task_id ?>')"></i>
                    </div>
                  </div>
                  <div class="col ms-2" style="text-align: right;">
                    <?php echo getTimeAgo($attachment['created_at']) ?>
                  </div>
                </div>

                <?php
              }
              ?>


            </div>



          </div>
        </div>
      </div>
    </div>

  </div>

</main>
<!--Main layout-->

<?php include 'settings/footer.php' ?>
<script src="assets/plugin/jsZip.js"></script>

<script>

  var currentDate = new Date();
  var formattedDate = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
  $("#datepicker").val(formattedDate);


  const editableText = document.getElementById('editableText');
  const editButton = document.getElementById('editButton');

  editButton.addEventListener('click', () => {
    if (editableText.getAttribute('contenteditable') === 'false') {
      editableText.setAttribute('contenteditable', 'true');
      editButton.textContent = 'Save';
    } else {
      editableText.setAttribute('contenteditable', 'false');
      editButton.textContent = 'Edit';
      var summary = $("#task_summary").text();
      $.ajax({
        url: 'settings/api/taskApi.php',
        type: 'POST',
        data: {
          type: 'changeSummary',
          task_id: '<?php echo $task_id ?>',
          summary: summary
        },
        success: function (result) {
          //  console.log(result);
        }
      });
    }
  });



  const editableText1 = document.getElementById('editableText1');
  const editButton1 = document.getElementById('editButton1');

  editButton1.addEventListener('click', () => {
    if (editableText1.getAttribute('contenteditable') === 'false') {
      editableText1.setAttribute('contenteditable', 'true');
      editButton1.textContent = 'Save';
    } else {
      editableText1.setAttribute('contenteditable', 'false');
      editButton1.textContent = 'Edit';
    }
  });


  let date = new Date();
  var dd = String(date.getDate()).padStart(2, '0');
  var mm = String(date.getMonth() + 1).padStart(2, '0');
  var yyyy = date.getFullYear();
  let today = mm + '/' + dd + '/' + yyyy;
  console.log('today is', today);
  $('#datepicker').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  let date1 = new Date();
  var dd = String(date.getDate()).padStart(2, '0');
  var mm = String(date.getMonth() + 1).padStart(2, '0');
  var yyyy = date.getFullYear();
  let today1 = mm + '/' + dd + '/' + yyyy;
  console.log('today is', today);
  $('#datepicker1').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  let date2 = new Date();
  var dd = String(date.getDate()).padStart(2, '0');
  var mm = String(date.getMonth() + 1).padStart(2, '0');
  var yyyy = date.getFullYear();
  let today2 = mm + '/' + dd + '/' + yyyy;
  console.log('today is', today);
  $('#datepicker2').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  let date3 = new Date();
  var dd = String(date.getDate()).padStart(2, '0');
  var mm = String(date.getMonth() + 1).padStart(2, '0');
  var yyyy = date.getFullYear();
  let today3 = mm + '/' + dd + '/' + yyyy;
  console.log('today is', today);
  $('#datepicker3').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  let date4 = new Date();
  var dd = String(date.getDate()).padStart(2, '0');
  var mm = String(date.getMonth() + 1).padStart(2, '0');
  var yyyy = date.getFullYear();
  let today4 = mm + '/' + dd + '/' + yyyy;
  console.log('today is', today);
  $('#datepicker4').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
</script>
<script>
  // Graph
  var ctx = document.getElementById("myChart");

  var myChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
      ],
      datasets: [{
        data: [15339, 21345, 18483, 24003, 23489, 24092, 12034],
        lineTension: 0,
        backgroundColor: "transparent",
        borderColor: "#007bff",
        borderWidth: 4,
        pointBackgroundColor: "#007bff",
      },],
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false,
          },
        },],
      },
      legend: {
        display: false,
      },
    },
  });
</script>

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


  function getLastLog(){
    var task_id = $('#task_id').val();
    var project_id = $('#project_id').val();
    $.ajax({
      url: 'settings/api/otherApi.php',
      data: {
        type: 'getLastLog',
        task_id: task_id,
        project_id : project_id
      },
      success: function (response) {
        console.log(response);
        $('#log_hour').val(response.hour);
        $('#log_minute').val(response.minutes);
      }
    });
  }
  
  function getContinueWork(){
    var task_id = $('#task_id').val();
    var project_id = $('#project_id').val();
    var pause_id = '<?php echo $contionues['id'] ?>';
    $.ajax({
      url: 'settings/api/otherApi.php',
      type : 'POST',
      data: {
        type: 'getContinueWork',
        task_id: task_id,
        project_id : project_id,
        pause_id : pause_id
      },
      dataType : 'JSON',
      success: function (response) {
        notyf.success(response.message);
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  $('#work_percentage').on('input', function () {
    var percentage = $(this).val();
    $("#per_val").text(percentage + '%');
  });


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
    if(break_type == 'lunch'){
        $('#break_time').val(45);
    }else{
        $('#break_time').val(30);
    }
  });


  $('#dropdownMenuButton1').click(() => {
    var ariaExpanded = $('#dropdownMenuButton1').attr('aria-expanded');
    if (ariaExpanded === 'false') {
      $('#dropdownMenuButton1').attr('aria-expanded', 'true');
      $('#dropdown_menu').addClass('show');
    } else {
      $('#dropdownMenuButton1').attr('aria-expanded', 'false');
      $('#dropdown_menu').removeClass('show');
    }
  });

  $('#failureModalbtn').click(() => {
    if (<?php echo intval($totalWorkPercentage['total_percentage']) ?> == 0) {
      notyf.error("No Work Log Found");
    } else {
      $("#failureModal").modal('show');
    }
  });

  // $('#work_percentage').keyup(()=>{
  //   var work_percentage = $(this).val();
  //     // if(work_percentage == 100){
  //       console.log(work_percentage);
  //     // }else{
  //       // notyf.error("No Work Log Found");
  //     // }
  // });

  // $('#work_percentage').keyup(() => {
  //   var work_percentage = parseInt($('#work_percentage').val()); // Use event.target to access the input element
  //   console.log(work_percentage);
  // });




  $('#downloadButton').click(function () {
    var zip = new JSZip();

    // Fetch and add attachments to the ZIP archive
    var attachments = document.querySelectorAll('.attachment_file_download');
    var promises = [];
    attachments.forEach(function (attachment) {
      var fileName = attachment.textContent.trim();
      var fileURL = attachment.getAttribute('href');

      promises.push(
        fetch(fileURL)
          .then(function (response) {
            return response.blob();
          })
          .then(function (blob) {
            zip.file(fileName, blob);
          })
      );
    });

    // Wait for all promises to resolve
    Promise.all(promises)
      .then(function () {
        // Generate the ZIP file
        return zip.generateAsync({ type: 'blob' });
      })
      .then(function (content) {
        // Create a download link for the ZIP file
        var zipFileName = 'attachments.zip';
        var zipBlob = new Blob([content], { type: 'application/zip' });
        var zipURL = URL.createObjectURL(zipBlob);

        var link = document.createElement('a');
        link.href = zipURL;
        link.download = zipFileName;
        link.style.display = 'none';

        // Trigger the download
        document.body.appendChild(link);
        link.click();

        // Clean up the link
        document.body.removeChild(link);
      })
      .catch(function (error) {
        console.error('Error:', error);
      });
  });



  $("#uploadAttechment_btn").click(() => {
    var task_id = '<?php echo $task_id ?>';
    var formData = new FormData();
    formData.append('type', 'uploadAttechment');
    formData.append('task_id', task_id);
    formData.append('attachment', $('#uploadAttechment')[0].files[0]);

    $.ajax({
      url: 'settings/api/attachmentApi.php',
      type: 'POST',
      data: formData,
      processData: false,  // Don't process the data
      contentType: false,  // Don't set content type
      success: function (result) {
        location.reload();
      }
    });
  });

  function deleteAttachment(id, task_id) {
    $.ajax({
      url: 'settings/api/attachmentApi.php',
      type: 'POST',
      data: {
        type: 'deleteAttechment',
        id: id,
        task_id: task_id
      },
      success: function (result) {
        location.reload();
      }
    });
  }

  function nextStatus() {
    var task_id = $('#task_id').val();
    $.ajax({
      url: 'settings/api/workLogApi.php',
      data: {
        type: 'nextStatus',
        task_id: task_id
      },
      success: function (result) {
        console.log(result);
        $('#prev_status').val(result.prevStatus);
        $('#next_status').val(result.nextStatus);
      }
    });
  }

  function inProgress(task_id, project_id) {
    $.ajax({
      url: 'settings/api/taskApi.php',
      data: {
        type: 'inProgress',
        task_id: task_id,
        project_id: project_id
      },
      dataType: 'json',
      success: function (result) {
        setTimeout(() => {
          location.reload();
        }, 1500);
        console.log(result);
        $('#current_status').text(result.next_status);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  $('#log_hour').keyup(function () {
    var task_id = $('#task_id').val();
    var log_hour = $(this).val();
    $.ajax({
      url: 'settings/api/taskApi.php',
      data: {
        type: 'totalEstimatedTime',
        task_id: task_id
      },
      success: function (response) {
        console.log(response);
        if (log_hour > response.avi_time.hour) {
          Notiflix.Confirm.show(
            'Confirmation',
            'Your Efficiency will be decreased. Are you sure you want to proceed?',
            'Yes',
            'No',
            function () {
              // var flag = true;
              // alert("working")
            }
          );
        }
      }
    });
  });

  $('#log_minute').keyup(function () {
    var task_id = $('#task_id').val();
    var log_minute = $(this).val();
    var log_hour = $("#log_hour").val();
    $.ajax({
      url: 'settings/api/taskApi.php',
      data: {
        type: 'totalEstimatedTime',
        task_id: task_id
      },
      success: function (response) {
        console.log(response);
        if ((log_hour == response.avi_time.hour) && (log_minute > response.avi_time.minute)) {
          Notiflix.Confirm.show(
            'Confirmation',
            'Your Efficiency will be decreased. Are you sure you want to proceed?',
            'Yes',
            'No',
            function () {
              // var flag = true;
              // alert("working")
            }
          );
        }
      }
    });
  });

  $('#qaFailureTaskForm').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/qaFailureApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        console.log(response);
        notyf.success(response.message);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  <?php
  if (($tasks['status'] != "in_progress") && ($tasks['status'] != "vector_in_progress")) {
    ?>
    $('#addPauseLogWork').submit(function (event) {
      event.preventDefault();
      var formData = new FormData(this);
      var percentage = parseInt($("#work_percentage").val());
      if (percentage == 100) {
        Notiflix.Confirm.show(
          'Confirmation',
          'You Are Logging 100% for this task, your task will be autocomplete  Please select work status?',
          'Pass',
          'Fail',
          function () {
            $.ajax({
              url: 'settings/api/workLogApi.php',
              type: 'POST',
              data: formData,
              cache: false,
              contentType: false,
              processData: false,
              dataType: 'json',
              success: function (response) {
                console.log(response);
                notyf.success(response.message);
                $('#exampleModal').modal('hide');
                $('#current_status').text(response.current_status);
                if (percentage == 100) {
                  window.location.href = 'task-list.php';
                } else {
                  location.reload();
                }
              },
              error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
              }
            });
          },
          function () {
            $("#failureModal").modal("show");
            var hour = $('#log_hour').val();
            var minute = $('#log_minute').val();
            $('#hour_failure').val(hour);
            $('#minute_failure').val(minute);
          }
        );
      } else {

        if (percentage != 0) {
          $.ajax({
            url: 'settings/api/workLogApi.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
              console.log(response);
              notyf.success(response.message);
              $('#exampleModal').modal('hide');
              $('#current_status').text(response.current_status);
              if (percentage == 100) {
                window.location.href = 'task-list.php';
              } else {
                location.reload();
              }
            },
            error: function (xhr, status, error) {
              var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
              notyf.error(errorMessage);
            }
          });
        } else {
          notyf.error("Work Percentage should not be Zero");
        }
      }
    });
  <?php
  } else {
    ?>


    $('#addPauseLogWork').submit(function (event) {
      event.preventDefault();
      var log_minute = parseInt($('#log_minute').val());
      var log_hour = parseInt($('#log_hour').val());

      if(log_minute == 0 && log_hour == 0){
        notyf.error("At least 1 min need");
      }else{
        var formData = new FormData(this);
        var percentage = parseInt($("#work_percentage").val());
        if (percentage != 0) {
          $.ajax({
            url: 'settings/api/workLogApi.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
              console.log(response);
              notyf.success(response.message);
              $('#exampleModal').modal('hide');
              $('#current_status').text(response.current_status);
              if (percentage == 100) {
              window.location.href = 'task-list.php';
              } else {
              location.reload();
              }
            },
            error: function (xhr, status, error) {
              var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
              notyf.error(errorMessage);
            }
          });
        } else {
          notyf.error("Work Percentage should not be Zero");
        }
      }
    });

    <?php
  }
  ?>






  $('#addLogWork').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/workLogApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        console.log(response);
        notyf.success(response.message);
        $('#exampleModal').modal('hide');
        $('#current_status').text(response.current_status);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  $('#failureTaskForm').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/workLogApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        console.log(response);
        notyf.success(response.message);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  $('#reAssignTaskForm').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/workLogApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
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

  $('#addComment').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/commentApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        console.log(response);
        notyf.success(response.message);
        $('#commentModal').modal('hide');
        $('#current_status').text(response.current_status);
        var html = '<div class="d-flex mt-1"><div><span class="activity-name">' + response.first_name[0] + '' + response.last_name[0] + '</span></div><div><span class="m-2">' + response.first_name + ' ' + response.last_name + '</span><p class="d-inline text-muted">added a comment - Just Now</p><p class="text-muted">' + response.comment + '</p></div></div><hr>';
        $('#commentBox').prepend(html);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });


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