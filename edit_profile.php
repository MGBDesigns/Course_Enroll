<?php
include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['email'];

$query = "SELECT firstName, lastName, phone, email FROM tblprofiles WHERE email = ?";
$stmt = mysqli_prepare($dbconnect, $query);
if ($stmt === false) {
    die('Error preparing SQL query: ' . mysqli_error($dbconnect));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_bind_result($stmt, $firstName, $lastName, $phone, $email);
    mysqli_stmt_fetch($stmt);
} else {
    die('Profile not found.');
}

mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    
    if (empty($firstName) || empty($lastName) || empty($phone)) {
        $error_message = "All fields are required.";
    } else {
        $update_query = "UPDATE tblprofiles SET firstName = ?, lastName = ?, phone = ? WHERE email = ?";
        $stmt = mysqli_prepare($dbconnect, $update_query);
        if ($stmt === false) {
            die('Error preparing update query: ' . mysqli_error($dbconnect));
        }

        mysqli_stmt_bind_param($stmt, "ssss", $firstName, $lastName, $phone, $email);
        $update_success = mysqli_stmt_execute($stmt);
        if ($update_success) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . mysqli_error($dbconnect);
        }

        mysqli_stmt_close($stmt);
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
    <title>Edit Profile</title>
</head>

<body>
    <div class="jumbotron">
        <div class="container text-center">
            <h1>Edit Your Profile</h1>
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
                    <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
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

    <div class="container">
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php } ?>

        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php } ?>

        <h2>Edit Your Profile Information</h2>
        <form action="edit_profile.php" method="POST">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>