<?php

$current_page = 'reassign-task';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';
$sql = $conn->prepare("SELECT * FROM `tasks` Where `status` = 'ready'");
$sql->execute();
$tasks = $sql->fetchAll(PDO::FETCH_ASSOC);
$countEmployee = count($tasks);

$qcCount = $conn->prepare("SELECT * FROM `tasks` Where `status` = 'assign_qc'");
$qcCount->execute();
$qcCount = $qcCount->fetchAll(PDO::FETCH_ASSOC);
$countQc = count($qcCount);

$qaCount = $conn->prepare("SELECT * FROM `tasks` Where `status` = 'assign_qa'");
$qaCount->execute();
$qaCount = $qaCount->fetchAll(PDO::FETCH_ASSOC);
$countQa = count($qaCount);

$vectorCount = $conn->prepare("SELECT * FROM `tasks` Where `status` = 'assign_vector'");
$vectorCount->execute();
$vectorCount = $vectorCount->fetchAll(PDO::FETCH_ASSOC);
$countVector = count($vectorCount);

$sql2 = $conn->prepare("SELECT * FROM `users` Where `user_type` = 'user'");
$sql2->execute();
$users = $sql2->fetchAll(PDO::FETCH_ASSOC);

$projectList = $conn->prepare("SELECT * FROM `projects`");
$projectList->execute();
$projectList = $projectList->fetchAll(PDO::FETCH_ASSOC);

?>


<?php
$title = 'Assign Employee || EOM ';
include 'settings/header.php'
  ?>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Assign Task </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card-body p-2">
          <form id="addAssign">
            <input type="hidden" class="form-control" name="type" value="addAssign" required>
            <input type="hidden" class="form-control" name="role" value="qa" required>
            <div class="row form-row mb-3 p-2">
              <div class="col-12 col-sm-12">
                <div class="form-group">
                  <label>Assign Employee</label>
                  <select id="ch" name="user_id" class="form-control" required>
                    <option value="" selected>Select Employee</option>
                    <?php
                    foreach ($users as $user) {
                      echo '<option value="' . $user['id'] . '">' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                    }
                    ?>

                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Assign</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<main style="margin-top: 100px;">
  <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
    <a href="reassign-employee-task.php" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative ">
        Employee
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countEmployee ?>
          <span class="visually-hidden">unread messages</span>
        </span>
      </button></a>
    <a href="reassign-qc-task.php" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative">
        QC
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countQc ?>
          <span class="visually-hidden">unread messages</span>
        </span>
      </button>
    </a>
    <a href="#" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative btn_active">
        QA
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countQa ?>
          <span class="visually-hidden">unread messages</span>
        </span>
      </button>
    </a>
    <a href="reassign-vector-task.php" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative">
        Vector
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countVector ?>
          <span class="visually-hidden">unread messages</span>
        </span>
      </button>
    </a>
  </div>
  <div class="container pt-5 p-2">
    <div class="container">
      <div class="d-flex justify-content-between" style="font-size: 25px;">
        <p class="fw-bold">Assign</p>
        <div style="display: flex;">
          <select name="project" id="projectSelect" class="form-control" style="margin :0 15px;">
            <option value="">Select Project</option>
            <?php
            foreach ($projectList as $project) {
              echo '<option value="' . $project['project_id'] . ' (' . $project['project_name'] . ')">' . $project['project_name'] . '</option>';
            }
            ?>
          </select>
          <a class="btn btn-primary" onclick="getAddAssign()">Assign</a>
        </div>
      </div>
      <p class="btn btn-success btn-sm" style="margin: 20px 0;" onclick="checkBoxChanged()"><i class="fa-solid fa-check-double" style="margin: 0 10px;"></i>Select All</p>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th  scope="col"><span onclick="copyButton()">Select <i class="far fa-copy mr-2"></i></span></th>
            <th scope="col">#</th>
            <th scope="col">Task Id</th>
            <th scope="col">Project</th>
            <th scope="col">Area Sqkm</th>
            <th scope="col">Complexity</th>
            <th scope="col">Assigned</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php
          $i = 1;
          foreach ($qaCount as $task) {

            $project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
            $project->execute([$task['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);
            
            $assign = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `role` = 'qa' AND `status` = 'assign'");
            $assign->execute([$task['task_id']]);
            $assign = $assign->fetch(PDO::FETCH_ASSOC);
            
            $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ? ");
            $user->execute([$assign['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            $id = base64_encode($task['task_id']);
            echo '
                    <tr id="row_' . $task['task_id'] . '">
                      <th ><input type="checkbox" class="select_box" data-task="' . $task['task_id'] . '"  data-project="' . $task['project_id'] . '"  id="' . $task['task_id'] . '"></th>
                      <th scope="row">' . $i . '</th>
                      <td><label for="' . $task['task_id'] . '">' . $task['task_id'] . '</label></td>
                      <td>' . $task['project_id'] . ' (' . $project['project_name'] . ')</</td>
                      <td>' . $task['area_sqkm'] . '</td>
                      <td>' . $task['complexity'] . '</td>
                      <td>' . $user['first_name'] . ' ' . $user['last_name'] . '</td>
                      <td><a class="btn btn-danger" onclick="deleteUser(\'' . $task['task_id'] . '\')">Delete</a></td>
                    </tr>
                  ';
            $i++;
          }

          ?>
        </tbody>
      </table>
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

  function checkBoxChanged() {
      $('.select_box').click()
  }


  const taskArray = [];
  const projectArray = [];

  dataTable.on('change', '.select_box', function (){
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
    // console.log(projectArray);
  });

  function deleteUser(id) {
    $.ajax({
      url: 'settings/api/taskApi.php',
      type: 'POST',
      data: {
        type: 'deleteTask',
        task_id: id
      },
      success: function (response) {
        notyf.success(response.message);
        $("#row_" + id).remove();
      }
    });
  }

  function copyButton() {
      var array = taskArray;
      if(array.length == 0){
        notyf.error("Select Task First.");
      }else{
        var textToCopy = array.join('\n');
        var textarea = document.createElement('textarea');
        textarea.value = textToCopy;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        notyf.success("Copy into Clipboard.");
      }
  }

  $('#addAssign').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/reassignApi.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',

      success: function (response) {
        console.log(response);
        notyf.success(response.message);
        $('#exampleModal').modal('hide');
        setTimeout(() => {
          location.reload();
        }, 900);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  function getAddAssign() {
    if (taskArray.length == 0) {
      notyf.error("Select Task First.");
    } else {
      taskArray.forEach(element => {
        $("#addAssign").append(`<input type="hidden" class="form-control" name="task_id[]" value="${element}" readonly required>`);
      });
      projectArray.forEach(element => {
        $("#addAssign").append(`<input type="hidden" class="form-control" name="project_id[]" value="${element}" readonly required>`);
      });
      $("#exampleModal").modal("show");
    }
  }
</script>
<script>
  $('#projectSelect').change(function () {
    var projectInput, projectValue;
    projectInput = document.getElementById("projectSelect");
    projectValue = projectInput.value;
    dataTable.column(3).search(projectValue).draw();
  });
</script>


</body>

</html>