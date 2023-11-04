<?php

$current_page = 'project';

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
$sql = $conn->prepare('SELECT * FROM `projects`');
$sql->execute();
$projects = $sql->fetchAll(PDO::FETCH_ASSOC);


?>

<?php 
  $title = 'Project List || EOM ';
  include 'settings/header.php' 
?>
  <link rel="stylesheet" href="css/test2.css">
  <style>
    table tbody td .close {
      background-color: rgb(170, 248, 218);
      display: block;
      border-radius: 20px;
      padding: 0px 8px;
      text-transform: uppercase;
      text-align: center;
    }

    .scroll-bar {
      max-height: 350px;
      overflow: scroll;
    }

    .projectID {
      display: flex;
      margin-left: 20px;
      margin-top: 15px;
      position: absolute;
      z-index: 9;
    }

    .complete {
        background-color: #9ae725 !important;
    }

  </style>

  <main style="margin-top: 100px;">
    <div class="container pt-1">
      <div class="accordion accordion-flush" id="accordionFlushExample">
        <div class="accordion-item">
          <div class="row">
            <div class="col col-3 border p-3" style="width:30%;height: 500px;">
              <div class="text-center">
                <button type="button" class="btn ">
                  Project List
                </button>
              </div>
              <?php

                foreach($projects as $project){
             
              ?>
              <div class=" border p-25 w-100 ">
                <a href="project-details.php?project_id=<?php echo base64_encode($project['project_id']) ?>" style="text-decoration : none"  class=" projectID">Project ID : <?php echo $project['project_id'] ?>  (<?php echo $project['project_name'] ?>)</a>
                <h2 class="accordion-header" id="flush-headingThree">
                  <button class="accordion-button p-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#flush-collapse<?php echo $project['project_id'] ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $project['project_id'] ?>">
     
                  </button>
                </h2>
              </div>

              <?php
                  }
              ?>
            </div>


            <div class="col border col-8 p-3" style="width:68%;height: 500px;overflow:auto">
              <div class="text-center">
                <button type="button" class="btn bg-white btn1  text-center">Task List Under project</button>
              </div>

              <?php 

                foreach($projects as $project){


              ?>

              <div class=" d-block w-100 ">
                <div id="flush-collapse<?php echo $project['project_id'] ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo $project['project_id'] ?>"
                  data-bs-parent="#accordionFlushExample">
                  <div class="accordion-body">
                    <div>

                      <p>Project Id: <span><?php echo $project['project_id'] ?> </span>  (<?php echo $project['project_name'] ?>) </p>

                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>Task Id</th>
                            <th>Area Sqkm</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody class="scroll-bar">
                          <?php

                              $sql2 = $conn->prepare('SELECT * FROM `tasks` WHERE `project_id` = ? ORDER BY `update_at` DESC');
                              $sql2->execute([$project['project_id']]);

                              $tasks = $sql2->fetchAll(PDO::FETCH_ASSOC);
                              foreach($tasks as $task){

                                $assign = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `task_id` = ? AND `status` = 'assign'");
                                $assign->execute([$task['project_id'],$task['task_id']]);
                                $assign = $assign->fetch(PDO::FETCH_ASSOC);

                                $user = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
                                $user->execute([$assign['user_id']]);
                                $user = $user->fetch(PDO::FETCH_ASSOC);

                                $t_id = base64_encode($task['task_id']);

                          ?>


                            <tr>
                              <th class><?php echo $task['task_id'] ?></th>
                              <td><?php echo substr($task['area_sqkm'],0,15)?> sqkm</td>
                              <td><span class="close <?php echo $task['status']?>" style="background-color :<?php echo $task['status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)'  ?>"><?php echo str_replace('_', ' ', $task['status']) ?></span></td>
                              <td><?php echo $user['first_name'].' '.$user['last_name'] ?></td>
                              <?php
                                  if($user_type == 'admin'){
                              ?>
                              <td style="display: flex;justify-content: space-between;"><a href="task-details.php?task_id=<?php echo $t_id ?>"><i style="color: black;" class="fas fa-info-circle"></i></a><a href="create-task.php?edit=<?php echo $t_id ?>"><i style="color: #0001fa;" class="fas fa-edit"></i></a><a onclick="deleteTask('<?php echo $task['task_id'] ?>')"><i style="color: #dd0000;" class="fas fa-trash"></i></a></td>
                              <?php
                                }
                              ?>
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

              <?php

                }

              ?>

            </div>

          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include 'settings/footer.php' ?>

  <script>
    var notyf = new Notyf({ position: { x: 'right', y: 'top' } });
    function deleteTask(task_id){
        $.ajax({
        url: 'settings/api/taskApi.php',
        type: 'POST', 
        dataType: 'json',
        data: {
            type: 'deleteTask', 
            task_id: task_id 
        },
        success: function(data) {
            notyf.success(data.message);
            setTimeout(() => {
              location.reload();
            }, 1500);
        },
        error: function(error) {
            console.error('Error deleting data:', error);
        }
    });
}
  </script>

</body>

</html>