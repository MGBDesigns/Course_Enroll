<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<link href="https://fonts.googleapis.com/css2?family=Dosis:wght@400;500&family=Ubuntu:wght@400;500&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="style.css">
    <title>Student Course Enrollment</title>
</head>

<body>
    <div class="jumbotron">
        <div class="container text-center">
            <h1>Student Course Enrollment</h1>
        </div>
    </div>

    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav navbar-left">
                    <li class="active"><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
                    <?php
                    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                        echo '<li><a href="createaccount.php"><span class="glyphicon glyphicon-user"></span> Create Account</a></li>';
                        echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>';
                    }
                    ?>
                    <li><a href="index.php"><span class="glyphicon glyphicon-envelope"></span> Contact Us</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        echo '<li><a href="course_registration.php"><span class="glyphicon glyphicon-book"></span> Course Registration</a></li>';
                        echo '<li><a href="profile.php"><span class="glyphicon glyphicon-education"></span> Student Profile</a></li>';
                        echo '<li><a href="logout.php?Logout=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-center">
        <h3>Please Login to Register for Courses</h3>
    </div>
    
    <?php
    include 'footer.php';
    ?>
</body>
</html>
