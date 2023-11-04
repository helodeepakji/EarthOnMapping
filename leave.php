<?php

  $current_page = 'leave';

  session_start();
  if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin']!=true){
  header("location: login.php"); exit;
  }else{
  $userDetails = $_SESSION['userDetails'];
  }

?>
<?php 
  $title = 'Leaves || EOM ';
  include 'settings/header.php' 
?>
     
  <main style="margin-top: 75px;">
    <div class="container ">
      
  
    <section class="gradient-custom">
      <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
        <a href="#" class=" text-decoration-none p-3  btn-outline-primary active">Apply</a>
        <a href="pending.php" class=" text-decoration-none p-3 btn-outline-primary">Pending</a>
        <a href="history.php" class=" text-decoration-none p-3   btn-outline-primary">History</a>
      </div>
    
      <div class="container py-2">
        <div class="row justify-content-center align-items-center">
          <div class="col-12 col-lg-9 col-xl-10">
            <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
              <div class="card-body p-4 p-md-5">
                <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Applying for Leave</h3>
                <form id="leaveForm">
                  <div class="input-box">
                      <label class="form-label select-label">Leave type</label>
                      <select class="select form-control " name="leave_type" required>
                        <option value="" disabled>Choose option</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="4">Unpaid Leave</option>
                      </select>
                  </div>
    
                  <div class="d-flex input-box">
                    <div class="col-md-6 col-sm-12 mb-2 me-3 ">
                      <label class="form-label select-label">from date</label>
                      <input type="text" class="width-200 form-control"id="datepicker" name="form_date" required>
                      <input type="hidden" name="type" value="addLeave">
                    </div>
                    <div class="col-md-6 mb-2">   
                      <label class="form-label select-label">Sessions</label>
                        <select class="select form-control width-200" name="formdate_session" required>
                          <option value="" disabled>Choose option</option>
                          <option value="First Half">Session 1</option>
                          <option value="Second Half">Session 2</option>
                          
                        </select>
                    </div>
                  </div>
                  
                  <div class="d-flex input-box">
                    <div class="col-md-6 col-sm-12 mb-2 me-3 ">
                      <label class="form-label select-label">End date</label>
                      <input type="text" class="width-200 form-control" id="datepicker1"  name="end_date" required>
                    </div>
                    <div class="col-md-6 mb-2">   
                      <label class="form-label select-label">Sessions</label>
                        <select class="select form-control width-200" name="enddate_session" required>
                          <option value="1" disabled>Choose option</option>
                          <option value="2">Session 1</option>
                          <option value="3">Session 2</option>
                          
                        </select>
                    </div>
                  </div>

                    <!-- <div class=" input-box">
                      <img src="https://miro.medium.com/v2/resize:fill:224:224/0*K0cFJ7X5gQoJ3CtG.jpg" alt="img" class="images">
                      <select class="select form-control-sm border-0">
                          <option value="1" >Choose option</option>
                          <option value="2">Subject 1</option>
                          <option value="3">Subject 2</option>
                          <option value="4">Subject 3</option>
                        </select>
                    </div> -->

                    <!--<div class="mb-2 input-box">
                      <label for="formFileDisabled" class="form-label">Cc to</label>
                      <input class="form-control" type="file" id="formFileDisabled" name="upload">
                    </div>-->
                    <div class="d-flex input-box">
                      <div class="col-md-6 col-sm-12 mb-2 me-3 ">
                        <p class="form-label select-label">Contact details</p>
                        <input type="text" class="form-control" placeholder="Enter Contact details" name="contact_detail" required>
                      </div>
                      <div class="col-md-6 mb-2">   
                        <label class="form-label select-label">Region</label>
                        <input type="text" class="form-control" placeholder="Enter a Region" name="region" required>
                      </div>
                    </div>
                    <div class="d-flex ms-5">
                  <div class="mt-4 pt-2 me-4">
                    <input class="btn btn-primary btn-lg" type="submit" value="Submit" />
                  </div>
                  <div class="mt-4 pt-2 me-">
                      <input class="btn btn-primary btn-lg" type="reset" value="Cancel" />
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
  </script>
  <script>
    var notyf = new Notyf({position: {x: 'right',y: 'top'}});
    $("#leaveForm").submit(function(){
      event.preventDefault();
      var formData = new FormData(this);
				$.ajax({
					url: 'settings/api/leaveApi.php',
					type: 'POST',
					data: formData,
					cache: false,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function (response) {
						notyf.success(response.message);
            setTimeout(() => {
              window.location = 'pending.php';
            }, 1500);
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