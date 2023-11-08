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
  $sql = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'employee'");
  $sql->execute([$user_id]);
  $tasks = $sql->fetchAll(PDO::FETCH_ASSOC);
  $countEmployee = count($tasks);

  $vectorSQL = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'vector'");
  $vectorSQL->execute([$user_id]);
  $vectorSQL = $vectorSQL->fetchAll(PDO::FETCH_ASSOC);
  $countVector = count($vectorSQL);

  $QaSQL = $conn->prepare("SELECT * FROM `assign` WHERE `isActive` = 1 AND `status` = 'assign' AND `user_id` = ? AND `role` = 'qa'");
  $QaSQL->execute([$user_id]);
  $QaSQL = $QaSQL->fetchAll(PDO::FETCH_ASSOC);
  $countQa = count($QaSQL);
  
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
  .overflow{
    overflow: auto;
  }
</style>

<main style="margin-top: 100px;">
  <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
        <a href="#" style="display: flex;align-items: center;margin: 0 10px">
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
        <a href="qa-task-list.php" style="display: flex;align-items: center;margin: 0 10px">
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
    <div class="accordion accordion-flush" id="accordionFlushExample">
      <div class="accordion-item">
        <div class="row overflow">
          <div class="col border col-8 p-3" style="width:99%;height: 500px;">
            <div class="text-center">
              <button type="button" class="btn bg-white btn1  text-center">PRO Task List Under project</button>
            </div>

            <div class=" d-block w-100 ">
              <div>
                <div class="accordion-body">
                  <div>

                    <table class="table table-striped ">
                      <thead>
                        <tr>
                          <th>Assign Date</th>
                          <th>Tile #</th>
                          <th>Project Id</th>
                          <th>Activity</th>
                          <th>Name</th>
                          <th>View Details</th>
                        </tr>
                      </thead>
                      <tbody class="scroll-bar">
                        <?php

                        foreach ($tasks as $task) {

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
                          <tr style="<?php echo $taskss['is_reassigned'] ? 'background: #e16767;' : ''; ?>">
                            <th>
                              <?php echo date('j M, Y h:i A', strtotime($task['created_at']) )?>
                            </th>
                            <th>
                              <?php echo $task['task_id'] ?>
                            </th>
                            <th>
                              <?php echo $task['project_id'] ?> (
                              <?php echo $project['project_name'] ?>)
                            </th>
                            <td><span class="close">
                                <?php echo str_replace('_', ' ', $taskss['status']) == 'ready' ? 'Assign Pro' : str_replace('_', ' ', $taskss['status']) ?>
                            </td>
                            <td>
                              <?php echo $user['first_name'] . ' ' . $user['last_name'] ?>
                            </td>
                            <td><a href="task-details.php?task_id=<?php echo $t_id ?>">View Details</a></td>
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

</script>

</body>

</html>