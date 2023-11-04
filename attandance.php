<?php

  $current_page = 'attandance';
  include 'settings/config/config.php';
  session_start();
  if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
    header("location: login.php"); exit;
  }else{
     $userDetails = $_SESSION['userDetails'];
     $user_id = $_SESSION['userId'];
  }

?>

<?php 
  $title = 'Attendance Employee || EOM ';
  include 'settings/header.php' 
?>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">My Regularisation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">  
            <form id="updateAttendance">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>ClockOut Time</label>
                    <input type="time" class="form-control" name="clockout_time" id="clockout_time" required>
                    <input type="hidden" class="form-control" name="type" value="addRegularisation" required>
                    <input type="hidden" id="attendance_id" name="attendance_id" value="" required>
                  </div>
                </div>              
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Remarks</label>
                    <input type="text" id="remarks" class="form-control" name="remarks" >
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
      </div>
    </div>
    </div>
  </div>

  <main style="margin-top: 58px;">
    <div class="container ">
      <!-- <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
          <a class="navbar-brand">Logo</a>
          <p>Managemant System</p>
          <form class="d-flex input-group w-auto">
            <input
              type="search"
              class="form-control rounded"
              placeholder="Search"
              aria-label="Search"
              aria-describedby="search-addon"
            />
            <span class="input-group-text border-0" id="search-addon">
              <i class="fas fa-search"></i>
            </span>
          </form>
        </div>
      </nav> -->
      <section class="h-100 h-custom gradient-custom-2">
        <div class="container py-1 h-100">
          <div class="row d-flex justify-content-center py-5 h-100">
            <div class="col-12">
              <div class="card card-registration card-registration-2" style="border-radius: 15px;">
                <div class="card-body p-0">
                    <h1 class="d-flex justify-content-center py-2">All Attendance</h1>
                  <div class="row g-0">
                    <div class="">
                      <div class="px-4">AVG work hour: <span class="ms-5">10h</span></div>
                     
                      <hr>
                    <div class="px-4">AVG actual work our <span class="ms-3">12h</span></div>
                    <hr>
                    </div>
                    <div class="mb-2">
                       <div class="d-flex justify-content-between">
                        <div class="ms-4">
                            <span>Show</span>
                            <select class="select form-control-sm mx-2">
                                <option selected >option</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="3">4</option>
                                <option value="3">5</option>
                                <option value="3">6</option>
                                <option value="3">7</option>
                                <option value="3">8</option>
                                <option value="3">9</option>
                                <option value="3">10</option>
                              </select>
                              <span>Enter</span>
                        </div>
                        <div>
                        </div>
                       </div>
                    </div>
                    
                    <table class="table p-5">
                        <thead class="bg-secondary text-white">
                          <tr>
                            <th scope="col">
                              Si.No
                            </th>
                            <th scope="col">Date</th>
                            <th scope="col">In-Time</th>
                            <th scope="col">Out</th>
                            <th scope="col">Duration</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                          $currentDate = date('Y-m-d');
                          // echo "Current Date: $currentDate\n";
                          $j = 1;
                          for ($i = 0; $i <= 7; $i++) {
                            
                            $previousDate = date('Y-m-d', strtotime("-$i days"));
                            
                            echo '<tr>
                                  <th scope="row">'.$j.'</th>
                                  <td>'.date("M j, D", strtotime($previousDate)).'</td>';

                              $attendances = $conn->prepare("SELECT * FROM `attendence` WHERE `user_id` = ? AND `date` = ?");
                              $attendances->execute([$user_id,$previousDate]);
                              $attendance = $attendances->fetch(PDO::FETCH_ASSOC);

                              if($attendance){

                                  $attendence_clockIn_date = DateTime::createFromFormat('H:i:s', $attendance['clock_in_time']);
                                  $clockIn_time = $attendence_clockIn_date->format("g:i A");

                                  if(($attendance['clock_out_time'])&&($attendance['regularisation'] == 0)){
                                    $attendence_clockOut_date = DateTime::createFromFormat('H:i:s', $attendance['clock_out_time']);
                                    $clockOut_time = $attendence_clockOut_date->format("g:i A");
                                    $diff = $attendence_clockIn_date->diff($attendence_clockOut_date);
                                    $hours = $diff->h;
                                    $minutes = $diff->i;
                                  }else if(($attendance['clock_out_time'] != '')&&($i > 0)&&($attendance['regularisation'] == 1)){
                                    $clockOut_time = 'Pending';                                   
                                    $hours = 0;
                                    $minutes = 0;
                                  }if(($attendance['clock_out_time'] == '')&&($i > 0)&&($attendance['regularisation'] == 0)){
                                    $clockOut_time = '<button  class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="editAttandance('.$attendance['id'].')">My Regularisation</button>';                                   
                                    $hours = 0;
                                    $minutes = 0;
                                  }else{
                                    if($i == 0){
                                      $diff = $attendence_clockIn_date->diff(new DateTime());
                                      $hours = $diff->h;
                                      $minutes = $diff->i;
                                    }
                                  }
  

                              }else{

                                $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = ?");
                                $holiday->execute([$previousDate]);
                                $holiday = $holiday->fetch(PDO::FETCH_ASSOC);

                                if($holiday){
                                  $clockIn_time = '<p style="color:red">Holiday</p>';
                                  $clockOut_time = '<p style="color:red">'.$holiday['summary'].'</p>';
                                }else{

                                  $leave = $conn->prepare("SELECT * FROM `leaves` WHERE `form_date` >= ? AND `end_date` <= ? AND `user_id` = ?");
                                  $leave->execute([$previousDate, $previousDate,$user_id]);
                                  $leave = $leave->fetch(PDO::FETCH_ASSOC);
                                  if($leave){
                                    $clockIn_time = '<p style="color:red">leave</p>';
                                    $clockOut_time = '<p style="color:red">'.$leave['leave_type'].'</p>';
                                  }else{
                                    if (date("w", strtotime($previousDate)) == 0) {
                                      $clockIn_time = '<p style="color:red">Week Off</p>';
                                      $clockOut_time = '<p style="color:red">Sunday</p>';
                                    }else{
                                      $clockIn_time = '';
                                      $clockOut_time = '';
                                    }
                                  }

                                }

                                $hours = $minutes = 0;
                              }


                              echo '<td>'.$clockIn_time.'</td>
                                    <td>'.$clockOut_time.'</td>
                                    <td>'.$hours.'H '.$minutes.'M</td>
                                </tr>
                            ';
                                $j++;
                          }
                          ?>

                          <?php

                          //   $attendances = $conn->prepare("SELECT * FROM `attendence` WHERE `user_id` = ? ORDER BY `created_at` DESC");
                          //   $attendances->execute([$user_id]);
                          //   $attendances = $attendances->fetchAll(PDO::FETCH_ASSOC);
                          //   $i = 1;

                          //   foreach($attendances as $attendance){
                          //     $attendence_clockIn_date = DateTime::createFromFormat('H:i:s', $attendance['clock_in_time']);
                          //     if($attendance['clock_out_time']){
                          //       $attendence_clockOut_date = DateTime::createFromFormat('H:i:s', $attendance['clock_out_time']);
                          //       $clockOut_time = $attendence_clockOut_date->format("g:i A");
                          //     }else{
                          //       $attendence_clockOut_date = '';
                          //     }

                          //     if($attendence_clockOut_date){
                          //       $diff = $attendence_clockIn_date->diff($attendence_clockOut_date);
                          //       $hours = $diff->h;
                          //       $minutes = $diff->i;
                          //     }else{
                          //       $diff = $attendence_clockIn_date->diff(new DateTime());
                          //       $hours = $diff->h;
                          //       $minutes = $diff->i;
                          //     }
                          
                          
                          //     echo '
                          //         <tr>
                          //             <th scope="row">
                          //                 '.$i.'
                          //             </th>
                          //             <td>'.$attendance['date'].'</td>
                          //             <td>'.$attendence_clockIn_date->format("g:i A").'</td>
                          //             <td>'.$clockOut_time.'</td>
                          //             <td>'.$hours.'H '.$minutes.'M</td>
                          //         </tr>
                          //     ';
                          //     $i++;
                          // }
                          
                          ?>
                        </tbody>
                      </table>
                    
                    
                  </div>
                   </div>
                   <div>
                  <!-- <div class="border  px-3 p-3">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Possimus, neque. Quidem id ipsa reprehenderit, debitis nam soluta molestiae provident recusandae amet quis, doloremque expedita saepe architecto eligendi maxime at omnis!</div>
                   </div> -->
              </div>
            </div>
          </div>
        </div>
      </section>

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
    function editAttandance(id){
      $('#attendance_id').val(id);
    }
    $('#updateAttendance').submit(function(){
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: 'settings/api/attendanceApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          location.reload();
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