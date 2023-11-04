<?php

  $current_page = 'log-work';
  session_start();
  if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
  header("location: login.php"); exit;
  }else{
  $userDetails = $_SESSION['userDetails'];
  }

?>
   
      <?php include 'settings/header.php'  ?>
      <style>
        .gj-datepicker.gj-datepicker-md.gj-unselectable {
            width: 100%;
        }
      </style>
      
  <main style="margin-top: 58px;">
    <div class="container pt-4">
      <section class="vh-100 gradient-custom">
        <div class="container py-5 h-100">
          <div class="row justify-content-center  h-100">
            <div class="col-12 col-lg-6 col-xl-9">
              <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                <div class="card-body p-0 p-md-4">
                  <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Work Log</h3>
                  <form>
                    <div >
                      <div class="d-flex input-box">
                        <label for="Summary" class=" me-2 control-label ">Time Spant </label>
                        <input type="text" id="Summary"  class="form-control " >
                      </div>
                    </div>
                    <div >
                      <div class="d-flex input-box">
                        <label for="" class="me-2">Start date </label>
                        <input type="text" class="form-control Attachment-input" id="datepicker" placeholder="Select a date">
                      </div>
                     </div>
                     <div >
                        <div class="d-flex input-box">
                          <label for="tx" class="me-2 ">work description <span class="text-danger">*</span></label>
                          <input type="text" id="Summary"  class="form-control " > 
                        </div>
                    </div>
                    <div class="d-flex justify-content-end btn-clase">
                      <!-- <input class="btn btn1 btn-primary btn-lg me-4" type="submit" value="Submit" /> -->
                     
                      <a href="" class="btn  btn-primary btn-size" style="margin: 10px;" type="submit">Log</a>
                      <a href="" class="btn  btn-primary btn-size" style="margin: 10px;" type="submit">Cancel</a>
                      
        
                        
                      </div>
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
   
  <script>
    $(document).ready(function () {
      $('#datepicker').dateDropper({
            format: 'Y/m/d',
            large: true,
            largeDefault: true,
            largeOnly: true,
            theme: 'datetheme' 
      });
    });
  </script>



</body>
</html>