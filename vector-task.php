<?php

$current_page = 'vector-task';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
  header("location: login.php");
  exit;
} else {
  $userDetails = $_SESSION['userDetails'];
}

include 'settings/config/config.php';

$vector = $conn->prepare("SELECT * FROM `tasks` Where `status` = 'ready_for_vector'");
$vector->execute();
$vector = $vector->fetchAll(PDO::FETCH_ASSOC);

$sql2 = $conn->prepare("SELECT * FROM `users` Where `user_type` = 'user'");
$sql2->execute();
$users = $sql2->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
$title = 'Vector Task || EOM ';
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
            <input type="hidden" class="form-control" name="role" value="vector" required>
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
  <div class="container pt-5">
    <div class="container">
      <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
        <p class="fw-bold">Vector Task</p>
        <a class="btn btn-primary" onclick="getAddAssign()">Assign</a>
      </div>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th onclick="copyButton()" scope="col">Select <i class="far fa-copy mr-2"></i></th>
            <th scope="col">#</th>
            <th scope="col">Task Id</th>
            <th scope="col">Project Id</th>
            <th scope="col">Area Sqkm</th>
            <th scope="col">Complexity</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($vector as $task) {
            $id = base64_encode($task['task_id']);
            echo '
                    <tr id="row_' . $task['task_id'] . '">
                      <th ><input type="checkbox" class="select_box" data-task="' . $task['task_id'] . '"  data-project="' . $task['project_id'] . '"  id="' . $task['task_id'] . '"></th>
                      <th scope="row">' . $i . '</th>
                      <td><label for="' . $task['task_id'] . '">' . $task['task_id'] . '</label></td>
                      <td>' . $task['project_id'] . '</</td>
                      <td>' . $task['area_sqkm'] . '</td>
                      <td>' . $task['complexity'] . '</td>
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


  // function getAddAssign(task_id , project_id){
  //     $.ajax({
  //         url: 'settings/api/vectorApi.php',
  //         type: 'POST',
  //         data: {
  //             type : 'vectorComplete',
  //             task_id: task_id,
  //             project_id : project_id
  //         },
  //         dataType: 'json',   
  //         success: function(response) {
  //             console.log(response); 
  //             notyf.success(response.message);
  //             $('#row_'+task_id).remove();
  //         },
  //         error: function(xhr, status, error) {
  //             var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
  //             notyf.error(errorMessage);
  //         }
  //     });
  // }

  $('#addAssign').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'settings/api/assignApi.php',
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
        }, 1500);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });


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
    console.log(projectArray);
  });

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

</body>

</html>