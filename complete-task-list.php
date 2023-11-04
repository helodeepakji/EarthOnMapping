<?php

$current_page = 'complete-task-list';

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
if($user_type == 'admin'){
    $sql = $conn->prepare("SELECT * FROM `assign` WHERE `status`='assign'");
    $sql->execute();
}else{
    $sql = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'complete' AND `user_id` = ? ORDER BY `created_at` DESC");
    $sql->execute([$user_id ]);
}
$tasks = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

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

    a{
        text-decoration: none;
    }

    .projectID {

      display: flex;
      margin-left: 20px;
      margin-top: 15px;
      position: absolute;
      z-index: 9;

    }
  </style>
</head>

<body>
<?php 
  $title = 'Complete Tasks || EOM ';
  include 'settings/header.php' 
?>

  <main style="margin-top: 100px;">
    <div class="container pt-1">
      <div class="accordion accordion-flush" id="accordionFlushExample">
        <div class="accordion-item">
          <div class="row">


            <div class="col border col-8 p-3" style="width:99%;height: 500px;overflow: auto;">
              <div class="text-center">
                <button type="button" class="btn bg-white btn1  text-center">Completed Task List Under project</button>
              </div>

              <div class=" d-block w-100 ">
                <div>
                  <div class="accordion-body">
                    <div>

                      <table class="table table-striped ">
                        <thead>
                          <tr>
                            <th>Task Id</th>
                            <th>Project Id</th>
                            <th>Area Sqkm</th>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Efficiency</th>
                            <th>View</th>
                          </tr>
                        </thead>
                        <tbody class="scroll-bar">
                          <?php

                            foreach($tasks as $task){

                                $sql3 = $conn->prepare('SELECT * FROM `efficiency` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `profile` = ?');
                                $sql3->execute([$task['user_id'],$task['task_id'],$task['project_id'],$task['role']]);
                                $efficiency = $sql3->fetch(PDO::FETCH_ASSOC);

                                if(($efficiency['efficiency'] < 70) && ($efficiency['efficiency'] > 35)){
                                    $class = 'bg-warning';
                                }else if($efficiency['efficiency'] < 35){
                                    $class = 'bg-danger';
                                }else{
                                    $class = 'bg-success';
                                }
                                
                                $sql5 = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ?');
                                $sql5->execute([$task['task_id']]);
                                $taskss = $sql5->fetch(PDO::FETCH_ASSOC);
                                
                                $sql4 = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
                                $sql4->execute([$task['project_id']]);
                                $project = $sql4->fetch(PDO::FETCH_ASSOC);

                                $t_id = base64_encode($task['task_id']);

                          ?>
                          <tr>
                            <th class> <?php echo $task['task_id'] ?></th>
                            <th><?php echo $task['project_id'] ?> (<?php echo $project['project_name'] ?>)</th>
                            <td><?php echo $taskss['area_sqkm'] ?>sqkm</td>
                            <td><span class="close">Complete</span></td>
                            <td><?php echo $task['role'] == 'employee' ? 'Pro' : $task['role'] ?></td>
                            <td><div class="row ms-3 mb-1">
                                    <div class="col-6 mt-1">
                                        <div class="progress">
                                        <div class="progress-bar <?php echo $class ?>" role="progressbar" style="width:<?php echo $efficiency['efficiency'] ?>%"><?php echo $efficiency['efficiency'] ?>%</div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </td>
                            <td><a href="view-efficiency.php?task_id=<?php echo $task['task_id']?>">View Details</a></td>
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