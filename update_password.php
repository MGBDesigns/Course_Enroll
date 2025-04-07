<?php
include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        $user_id = $_SESSION['userID'];
        $sql = "SELECT userID, password FROM tblusers WHERE userID = ?";

        if ($stmt = mysqli_prepare($dbconnect, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                mysqli_stmt_bind_result($stmt, $userID, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if (password_verify($current_password, $hashed_password)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    $update_sql = "UPDATE tblusers SET password = ? WHERE userID = ?";
                    if ($update_stmt = mysqli_prepare($dbconnect, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "si", $new_hashed_password, $user_id);
                        if (mysqli_stmt_execute($update_stmt)) {
                            $_SESSION['password_update_success'] = "Your password has been updated successfully!";
                            header("Location: index.php");
                            exit;
                        } else {
                            $error_message = "Error updating password. Please try again later.";
                        }
                        mysqli_stmt_close($update_stmt);
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
            } else {
                $error_message = "User not found.";
            }
            mysqli_stmt_close($stmt);
        }
    }

function closeDbConnection($dbconnect) {
    mysqli_close($dbconnect);
}
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
    <title>Update Student Account Password</title>
</head>

<body>
<div class="jumbotron">
	<div class="container text-center">
		<h1>Update Student Account Password</h1>
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
                    <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
                    <li><a href="createaccount.php"><span class="glyphicon glyphicon-user"></span> Create Account</a></li>
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
<div class="container">
    <h2>Update Student Course Enrollment Password</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="update_password.php" method="POST">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required class="form-control">
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required class="form-control">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update Password Now</button>
    </form>
</div>
</body>
<?php include 'footer.php';?>
</html>
