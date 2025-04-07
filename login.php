<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $input_email = strtolower(trim($_POST['email']));
        $input_password = trim($_POST['password']);

        if (empty($input_email) || empty($input_password)) {
            $error_message = "Please fill in both email and password.";
        } else {
            $sql = "SELECT userID, email, password FROM tblusers WHERE email = ?";

            if ($stmt = mysqli_prepare($dbconnect, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $input_email);

                mysqli_stmt_execute($stmt);

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) > 0) {
                    mysqli_stmt_bind_result($stmt, $userID, $email, $hashed_password);
                    mysqli_stmt_fetch($stmt);

                    $password_check = password_verify($input_password, $hashed_password);

                    if ($password_check) {
                        $_SESSION['logged_in'] = true;
                        $_SESSION['email'] = $email;
                        $_SESSION['userID'] = $userID;
                        header("Location: profile.php");
                        exit;
                    } else {
                        $error_message = "Invalid email or password!";
                    }
                } else {
                    $error_message = "Invalid email or password!";
                }

                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $error_message = "Please provide both email and password.";
    }

    mysqli_close($dbconnect);
}
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
			</button>
		</div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav navbar-left">
                <li class="active"><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
                <li><a href="createaccount.php"><span class="glyphicon glyphicon-user"></span> Create Account</a></li>
                <li><a href="index.php"><span class="glyphicon glyphicon-envelope"></span> Contact Us</a></li>
            </ul>
		</div>
	</div>
</nav>

    <div class="container text-center">
        <h1>Student Account Login</h1>
    </div>

    <div class="container text-center">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

		<form action="login.php" method="POST">
			<div class="form-group row">
				<div class="col-sm-6">
					<label for="email">Email Address: </label>
					<input type="text" id="email" name="email" required class="form-control">
				</div>

				<div class="col-sm-6">
					<label for="password">Password: </label>
					<input type="password" id="password" name="password" required class="form-control">
				</div>
			</div>
			<div class="form-group text-center">
				<input type="submit" value="Login" class="btn btn-primary">
			</div>
		</form>
	    <p>Don't have an account? <a href="createaccount.php">Create Student Account Here</a></p>
		<p><a href="update_password.php">Update Password</a></p>
    </div>
	</div>

    <?php include 'footer.php'; ?>
</body>
</html>
