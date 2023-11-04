<?php

  $current_page = 'leave';
  include 'settings/config/config.php';

  session_start();
  if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
    header("location: login.php"); exit;
  }else{
    $userDetails = $_SESSION['userDetails'];
  }

  $sql = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'pending' AND `user_id` = ?");
  $sql->execute([$userDetails]);
  $result = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<?php 
  $title = 'Pending Application || EOM ';
  include 'settings/header.php' 
?>
     
  <main style="margin-top: 75px;">
    <div class="container ">
      <section class="vh-100 gradient-custom">
        <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
                
          <a href="leave.php" class=" text-decoration-none p-3  btn-outline-primary">Apply</a>
          <a href="#" class=" text-decoration-none p-3 btn-outline-primary active">Pending</a>
          <a href="history.php" class=" text-decoration-none p-3   btn-outline-primary">History</a>
          <!-- <button type="button" class="btn btn-primary">Pending</button>
          <button type="button" class="btn btn-primary">History</button> -->
        </div>
          <div class="container  h-100">
            <div class="row justify-content-center mt-3 h-100">
              <div class="col-12 col-lg-9 col-xl-10">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                  <div class="card-body p-2 p-md-5">
                    <!-- <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Registration Form</h3> -->
                    <form>
                      <div class="accordion accordion-flush" id="accordionFlushExample">
                        <?php
                          foreach($result as $item){
                            $date1 = new DateTime($item['form_date']);
                            $date2 = new DateTime($item['end_date']);

                            $interval = $date1->diff($date2);
                            $daysDifference = $interval->days;

                            echo '

                              <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    <div class="row box1  ">
                                      <div class="col-3 "><span class="text-muted">Category</span><p>Leave</p></div>
                                      <div class="col-3"><span class="text-muted">Leape type</span><p>'.$item['leave_type'].'</p></div>
                                      <div class="col-3"><span class="text-muted">No. of day</span><p>'.$daysDifference.'</p></div>
                                      <div class="col-3 mt-2 "><span class=" color" style="color:#b0a411">'.strtoupper($item['status']).'</span></div>
                                    
                                    </div>
                                  </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                  <div class="accordion-body">
                                      <div class="d-flex">
                                        <span class="fw-bold">Duration : </span><p><strong>'.$item['form_date'].'</strong>('.$item['formdate_session'].') to  <strong>'.$item['end_date'].'</strong> ('.$item['enddate_session'].')</p>
                                      </div>
                                      <div class="d-flex">
                                          <span class="fw-bold">Resion : </span><p>'.$item['resion'].'</p>
                                      </div>
                                  </div>
                                  <hr>
                                  <div class="accordion-body">
                                      <div class="d-flex justify-content-between text-center justify-content-center">
                                        <div class="">
                                          <strong>Applied on</strong><p>'.$item['created_at'].'</p>
                                        </div>
                                        <div class="details mt-4"><a href="" class="">View Details</a></div>
                                      </div>
                                    
                                  </div>
                                </div>
                              </div>
                            
                            ';
                          }
                        ?>
                          
                        </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
    </div>
  </main>
   

    <?php include 'settings/footer.php' ?>

</body>
</html>