<?php	
error_reporting(E_ALL ^ E_NOTICE)
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
				<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                <li><a href="index.php"><span class="glyphicon glyphicon-envelope"></span> Contact Us</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php
				if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                    echo '<li><a href="course_registration.php"><span class="glyphicon glyphicon-book"></span> Course Registration</a></li>';
					echo '<li><a href="profile.php"><span class="glyphicon glyphicon-education"></span> Student Profile</a></li>';
                    echo '<li><a href="index.php?Logout=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
                }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
	
<body>
    <div class="container text-center">
        <h1>Create Student Account</h1>
    </div>

    <div class="container" style="max-width: 500px; margin: 0 auto; padding: 20px;">
        <form action="submit_createaccount.php" method="POST">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <div class="container" style="min-height: 400px; display: flex; justify-content: center; align-items: center; max-width: 500px; margin: 0 auto; padding: 20px;">
        <div class="text-center">
            <form enctype="multipart/form-data" method="post" action="upFile.php">
                <input type="hidden" name="MAX_FILE_SIZE" value="50000">
                <div class="form-group">
                    <input type="file" name="file1" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Upload Student Photo" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
