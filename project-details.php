<?php
    $current_page = 'project';
    include 'settings/config/config.php';
    session_start();

    if (isset($_COOKIE['userId'])){
        $_SESSION['userId'] = $_COOKIE['userId'];
        $_SESSION['loggedin'] = true;
    }

    if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
        header("location: login.php"); 
        exit;
    }else{
        $userDetails = $_SESSION['userDetails'];
    }
    $user_id = $_SESSION['userId'];

    $project_id= base64_decode($_GET['project_id']);
    $project = $conn->prepare('SELECT * FROM `projects` WHERE `project_id` = ?');
    $project->execute([$project_id]);
    $project = $project->fetch(PDO::FETCH_ASSOC);


    function getTimeAgo($givenTimestamp) {

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
      }
      if ($hours > 0) {
          $timeAgo .= $hours . " hr ";
      }
      if ($minutes > 0) {
          $timeAgo .= $minutes . " min ";
      }
      if ($seconds > 0) {
          $timeAgo .= $seconds . " sec";
      }
      if ($timeAgo == "") {
          $timeAgo = "just now";
      }

      return $timeAgo;
    }

    function diffDateInDay($firstDate,$secondDate){
      $firstDate = new DateTime($firstDate);
      $secondDate = new DateTime($secondDate);
      if($firstDate == $secondDate){
        return 1;
      }
      $interval = $firstDate->diff($secondDate);
      return $interval->days;
    }


    $sqli = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ?  AND `task_id` = ? ORDER BY `created_it` DESC");
    $sqli->execute([$assigns['user_id'],$task_id]);
    $WorkLogs = $sqli->fetchAll(PDO::FETCH_ASSOC);
    function logHour($WorkLogs){
      $times = [];
      foreach ($WorkLogs as $WorkLog) {
        $times[] = $WorkLog['time'];
      }
      $previousTime = null;
      $sum = 0;

      foreach ($times as $time) {
          $currentTime = DateTime::createFromFormat('H:i:s', $time);
          
          if ($previousTime !== null) {
              $interval = $previousTime->diff($currentTime);
              $hoursDifference = $interval->h + $interval->i / 60 + $interval->s / 3600;
              $sum += $hoursDifference;
          }
          
          $previousTime = $currentTime;
      }
      return intval($sum);
    }

?>
   

<?php 
  $title = 'Project Details || EOM ';
  include 'settings/header.php' 
?>
  <style>
      .modal-date{
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
  .activity-name{
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
  
  .accordion-button{
    padding: 10px;
  }
  .scroll-bar{
    max-height: 150px;
    overflow-y: scroll;
  }
  .home {
    height: auto;
  }


.item{
  background: white;
  margin: 10px;
}
.item-card-box-task{
padding: 15px;
transition:0.5s;
cursor:pointer;
}
.item-card-box-task-title{  
font-size:15px;
transition:1s;
text-align: right;
cursor:pointer;
}
.item-card-box-task-title i{  
font-size:15px;
transition:1s;
cursor:pointer;
color:#008bf6
}
.card-box-task-title i:hover{
transform: scale(1.25) rotate(100deg); 
color:#18d4ca;

}
.card-box-task:hover{
transform: scale(1.05);
box-shadow: 10px 10px 15px rgba(0,0,0,0.3);
}
.card-box-task-text{
height:80px;  
}

.card-box-task::before, .card-box-task::after {
position: absolute;
top: 0;
right: 0;
bottom: 0;
left: 0;
transform: scale3d(0, 0, 1);
transition: transform .3s ease-out 0s;
background: rgba(255, 255, 255, 0.1);
content: '';
pointer-events: none;
}
.card-box-task::before {
transform-origin: left top;
}
.card-box-task::after {
transform-origin: right bottom;
}
.card-box-task:hover::before, .card-box-task:hover::after, .card-box-task:focus::before, .card-box-task:focus::after {
transform: scale3d(1, 1, 1);
}

.just-center{
justify-content: center;
}

  </style>
  
 
  <!--Main Navigation-->
  
  <!--Main layout-->
  <main style="margin-top: 100px;">
  
    <div class="container">
      <div class="sec">
          <div class="home">
            <div class="nav d-flex">
                <div class="logo">
                <img src="https://jira.atlassian.com/secure/projectavatar?pid=18511&avatarId=105990" alt="jksjds">
                </div>
                <div class="head">
                    <div class="ms-2">
                            <li class=" pb-1 text-primary"><?php echo $project['project_name'].' (project id : '.$project['project_id'].')' ?> </li>
                            <div class="d-flex">
                  <div id="editableText" contenteditable="false">
                    <p class="fw-bold">Need certification apk Which was down for sqpark</p>
                  </div>
              </div>
                    </div>
                    <div>
                    </div>
                </div>
                <?php if($_SESSION['userType'] == 'admin'){
                  echo '
                    <div class="right">
                      <a href="create-project.php?edit='.$_GET['project_id'].'" class="btn btn-primary"  style="margin: 15px;">Edit</a>
                      <a onclick="deleteProject('.$project_id.')" class="btn btn-danger"  style="margin: 15px;">Delete</a>
                    </div>
                  ';
                } ?>
            </div>
            <br><br>
            <div class=" d-flex px-3 pt-2">
              <div class="col col-7">
                  <div class="Details">
                      <p class="fw-bold">Details</p>
                      <div class="row ms-3">
                          <div class="col col-3 text-muted">Name</div>
                          <div class="col col-3"><?php echo $project['project_name'] ?></div>
                          <div class="col col-3 text-muted">Priority</div>
                          <div class="col col-3 indipended"><i class="fas fa-signal" style="font-size: 20px;margin: 0 10px;color:black"></i><span class="text-uppercase px-1 development-text fw-bold" id="current_status"><?php echo str_replace('_', ' ', $project['complexity']) ?></span>
                        </div>
                      </div>                  
                  </div>
                  <div class="Descrption mt-3">
                      <p class="fw-bold">Descrption</p>
                    <div class="row ms-3 mt-1">
                        <div class="col-12 ">
                        
                            <p><?php echo $project['description'] ?></p>                   
                            
                        </div>
                    </div>   
                  </div>
              </div>
              
                <div class="col p-3 ms-5 right">
                    <div class="Time-traking ">
                      <p class="fw-bold">Time Traking</p>
                      <div class="row ms-3 mb-2">
                          <div class="col-4 ">Estimated</div>
                          <div class="col-6 mt-1">
                            <div class="progress">
                              <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">100%</div>
                            </div>
                            
                            </div>
                            <div class="col ms-2"><?php echo $project['estimated_hour'] ?>h</div>
                      </div>
                      <div class="row ms-3 mb-1">
                        <div class="col-4 ">Remaining</div>
                        <div class="col-6 mt-1">
                          <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php 
                            
                            $rem = diffDateInDay($project['start_date'],$project['end_date']);
                            
                            if($rem > 0){
                              echo (diffDateInDay(date('Y-m-d'),$project['end_date'])/$rem)*100;
                            }else{
                              echo 0;
                            }
                            
                            ?>%;" 
                            aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"><?php
                            
                            echo number_format((diffDateInDay(date('Y-m-d'),$project['end_date'])/diffDateInDay($project['start_date'],$project['end_date']))*100) 
                            
                            ?>%</div>
                          </div>
                          </div>
                          <div class="col ms-2"><?php echo diffDateInDay(date('Y-m-d'),$project['end_date']) ?>d</div>
                          <!-- <div class="col ms-3">3d</div> -->
                    </div>
                  </div>            
                </div>
            </div>
          </div>
      </div>
      <hr>
      <div class="task-list">
        <h2>Task List</h2>
        <div class="container mt-2">
          <div class="row just-center">
            <?php 
            $tasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ?");
            $tasks->execute([$project_id]);
            $tasks = $tasks->fetchAll(PDO::FETCH_ASSOC);
            foreach($tasks as $task){
              $task_id = base64_encode($task['task_id']);
              if(($task['status'] == 'pending') || ($task['status'] == 'ready_for_qa') || ($task['status'] == 'ready_for_qc')){
                $color = 'red';
              }else{
                $color = 'green';
              };

              echo '
              <div class="col-md-3 col-sm-6 item clickable-row" data-href="task-details.php?task_id='.$task_id.'">
                <div class="card-box-task item-card-box-task card-box-task-block">
                <h4 class="item-card-box-task-title text-right"><a style="margin: 0px 5px;" href="create-task.php?edit='.$task_id.'"><i class="fa-solid fa-pen-to-square"></i></a></h4>
                  <h6 class="card-box-task-title  mt-3 mb-3"># '.$task['task_id'].'</h6>
                  <h5 class="card-box-task-title  mt-3 mb-3">Area : '.$task['area_sqkm'].'sqkm</h5>
                  <span class="text-uppercase px-1 development-text fw-bold" id="current_status" style="color:white;font-size:12px;background:'.$color.'">'. str_replace('_', ' ', $task['status']).'</span>
                </div>
              </div>  
              ';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
</main>
  <!--Main layout-->

  <?php include 'settings/footer.php' ?>
  <script>

    var currentDate = new Date();  
    var formattedDate = currentDate.getFullYear() + "-" +(currentDate.getMonth() + 1) + "-" + currentDate.getDate();
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
        }, ],
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: false,
                },
            }, ],
        },
        legend: {
            display: false,
        },
    },
});
    </script>

  <script>

    var notyf = new Notyf({ position: { x: 'right', y: 'top' } });

    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.clickable-row');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                window.location.href = this.dataset.href;
            });
        });
    });


    function deleteProject(id){
      var task_id = $('#task_id').val();
      $.ajax({
        url: 'settings/api/projectApi.php',
        type : 'POST',
        data : {
          type : 'deleteProject',
          id : id
        },
        success: function(result){
          notyf.success("Project is delete successfull");
          setTimeout(() => {
            window.location.href = 'project.php';
          }, 1500);
        }
      });
    }

    function inProgress(task_id,project_id,user_id){
      $.ajax({
        url: 'settings/api/taskApi.php',
        data : {
          type : 'inProgress',
          task_id : task_id,
          project_id: project_id,
          user_id :user_id
        },
        dataType: 'json',
        success: function(result){
          setTimeout(() => {
            location.reload();
          }, 1500);
          console.log(result);
          $('#current_status').text(result.next_status);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

    $('#addLogWork').submit(function(event){
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
          window.location = 'index.php';
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    });
    
    $('#addComment').submit(function(event){
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
          var html = '<div class="d-flex mt-1"><div><span class="activity-name">'+response.first_name[0]+''+response.last_name[0]+'</span></div><div><span class="m-2">'+response.first_name+' '+response.last_name+'</span><p class="d-inline text-muted">added a comment - Just Now</p><p class="text-muted">'+response.comment+'</p></div></div><hr>';
          $('#commentBox').prepend(html);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    });

  </script>


</body>
</html>