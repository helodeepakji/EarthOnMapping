<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ? $title : 'Welcome to EOM' ?></title>
    <link rel="stylesheet" href="assets/plugin/font-awesome-all.css" crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/plugin/new_bootstrap.css" crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/plugin/select2.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/plugin/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/plugin/notify.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/plugin/datatables.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/plugin/notifix.css" rel="stylesheet" />


    <link rel="stylesheet" href="css/test.css">
</head>

<body>
    <header>
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse bg-white d-flex">
            <div>
                <div class="position-sticky">
                    <div class="list-group list-group-flush mx-3 mt-4">
                        <a href="index.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "dashbord" ? "active" : "" ?>"><i
                                class="fas fa-home fa-fw me-3"></i><span>Home</span></a>
                        <?php
            if(($_SESSION['userType'] != 'admin') && ($_SESSION['userType'] != 'teamleader')){
          ?>
                        <a href="task-list.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "task-list" ? "active" : "" ?>"
                            aria-current="true">
                            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Assigned Task List</span>
                        </a>
                        <a href="complete-task-list.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "complete-task-list" ? "active" : "" ?>"
                            aria-current="true">
                            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Completed Task</span>
                        </a>
                        <?php
            }
          ?>
                        <!-- <a href="log-work.php" class="list-group-item list-group-item-action py-2 ripple <?php //echo $current_page == "log-work" ? "active" : "" ?> " >
            <i class="fas fa-chart-area fa-fw me-3"></i><span>Log Work</span>
          </a> -->
                        <?php
              if(($_SESSION['userType'] == 'admin') || ($_SESSION['userType'] == 'teamleader')){
            ?>
                        <a href="project.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "project" ? "active" : "" ?>"><i
                                class="fas fa-building fa-fw me-3"></i><span>Project</span></a>

                        <?php if($_SESSION['userType'] != 'teamleader'){ ?> 

                        <a href="create-project.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "create-project" ? "active" : "" ?>">
                            <i class="fas fa-chart-pie fa-fw me-3"></i><span>Create Project</span>
                        </a>
                        <a href="create-task.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "create-task" ? "active" : "" ?>"><i
                                class="fas fa-network-wired fa-fw me-3"></i><span>Create Task</span></a>

                        <?php } ?>

                        <a href="assign-employee-task.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "assign-task" ? "active" : "" ?>"><i
                                class="fas fa-user fa-fw me-3"></i><span>Assign Task</span></a>
                        <a href="vector-task.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "vector-task" ? "active" : "" ?>"><i
                                class="fas fa-vector-square fa-fw me-3"></i><span>Vector Task</span></a>

                        <?php if($_SESSION['userType'] != 'teamleader'){ ?> 
                        <a href="employee-list.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "employee-list" ? "active" : "" ?>"><i
                                class="fas fa-users fa-fw me-3"></i><span>Employee</span></a>
                        <a href="create-employee.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "create-employee" ? "active" : "" ?>">
                            <i class="fas fa-user-plus fa-fw me-3"></i><span>Create Employee</span>
                        </a>
                        <a href="holiday.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "holiday" ? "active" : "" ?>"><i
                                class="fas fa-calendar-week fa-fw me-3"></i><span>Holiday</span></a>
                        <a href="leave-application.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "leave" ? "active" : "" ?>"><i
                                class="fas fa-power-off fa-fw me-3"></i><span>Leave Application</span></a>
                        <a href="attandance-regularisation.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "attandance-regularisation" ? "active" : "" ?>"><i
                                class="fas fa-tasks fa-fw me-3"></i><span>Attendance</span></a>
                        <?php } ?>
                        <a href="total-efficiency.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "total-efficiency" ? "active" : "" ?>"><i
                                class="fas fa-user-clock fa-fw me-3"></i><span>Total Efficiency</span></a>
                        <a href="task-efficiency.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "task-efficiency" ? "active" : "" ?>"><i
                                class="fas fa-user-clock fa-fw me-3"></i><span>Task Efficiency</span></a>
                        <?php if($_SESSION['userType'] == 'teamleader'){ ?> 
                        <a href="attandance.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "attandance" ? "active" : "" ?>"><i
                                class="fas fa-globe fa-fw me-3"></i><span>Attendance</span></a>
                        <?php } ?>
                        <a href="gallery.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "gallery" ? "active" : "" ?>">
                            <i class="fas fa-images fa-fw me-3"></i><span>Gallery</span></a>
                        <?php }else{ ?>
                        <a href="leave.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "leave" ? "active" : "" ?>"><i
                                class="fas fa-power-off fa-fw me-3"></i><span>Leave Application</span></a>
                        <a href="attandance.php"
                            class="list-group-item list-group-item-action py-2 ripple <?php echo $current_page == "attandance" ? "active" : "" ?>"><i
                                class="fas fa-globe fa-fw me-3"></i><span>Attendance</span></a>
                        <!-- <a href="#" class="list-group-item list-group-item-action py-2 ripple <?php //echo $current_page == "sales" ? "active" : "" ?>"><i
              class="fas fa-money-bill fa-fw me-3"></i><span>Sales</span></a> -->
                        <?php } ?>
                    </div>
                </div>

            </div>


        </nav>

        <!-- Sidebar -->

        <!-- Navbar -->
        <nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
            <!-- Container wrapper -->
            <div class="container-fluid">
                <!-- Toggle button -->
                <!-- <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#sidebarMenu"
          aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <i class="fas fa-bars"></i>
        </button> -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                    aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Brand -->
                <a class="navbar-brand" href="#"
                    style="width: 210px;display: grid;justify-items: center; align-items: center;">
                    <img src="images/other/logo.png" width="100px" alt="MDB Logo" loading="lazy" />
                </a>



                <ul class="navbar-nav ms-auto d-flex flex-row">

                    <!-- <div class="text">
                        <h1 style="font-size: 25px; margin: 0 50px;">Hii Admin</h1>
                    </div> -->



                    <!-- Avatar -->
                    <div class="dropdown float-end">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton2"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-alt"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <!-- <li><a class="dropdown-item" href="#">Settings</a></li> -->
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                </ul>
            </div>
            <!-- Container wrapper -->
        </nav>
        <!-- Navbar -->
    </header>