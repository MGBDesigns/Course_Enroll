<?php
include 'config.php';
include 'waitlist_notification.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['email'];

$query = "SELECT firstName, lastName, phone, email FROM tblprofiles WHERE email = ?";
$stmt = mysqli_prepare($dbconnect, $query);
if ($stmt === false) {
    die('Error preparing the SQL query: ' . mysqli_error($dbconnect));
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

$courses_query = "SELECT c.courseID, c.courseNumber, c.courseName, s.name AS semesterName, s.year, co.offeringID
                  FROM tblenrollments e
                  JOIN tblcourseofferings co ON e.offeringID = co.offeringID
                  JOIN tblcourses c ON co.courseID = c.courseID
                  JOIN tblsemesters s ON co.semesterID = s.semesterID
                  WHERE e.userID = (SELECT userID FROM tblprofiles WHERE email = ?)";
				  
$stmt_courses = mysqli_prepare($dbconnect, $courses_query);
mysqli_stmt_bind_param($stmt_courses, "s", $email);
mysqli_stmt_execute($stmt_courses);
$courses_result = mysqli_stmt_get_result($stmt_courses);
mysqli_stmt_close($stmt_courses);

$waitlist_query = "SELECT c.courseID, c.courseNumber, c.courseName, s.name AS semesterName, s.year, co.offeringID, w.position
    FROM tblwaitlist w
    JOIN tblcourseofferings co ON w.offeringID = co.offeringID
    JOIN tblcourses c ON co.courseID = c.courseID
    JOIN tblsemesters s ON co.semesterID = s.semesterID
    WHERE w.userID = (SELECT userID FROM tblprofiles WHERE email = ?)
    ORDER BY w.position ASC"; 

$stmt_waitlist = mysqli_prepare($dbconnect, $waitlist_query);
mysqli_stmt_bind_param($stmt_waitlist, "s", $email);
mysqli_stmt_execute($stmt_waitlist);

$waitlist_result = mysqli_stmt_get_result($stmt_waitlist);

mysqli_stmt_close($stmt_waitlist);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_enrollment'])) {
    $offeringID = $_POST['offeringID'];

    $cancel_query = "DELETE FROM tblenrollments WHERE offeringID = ? AND userID = (SELECT userID FROM tblprofiles WHERE email = ?)";
    $stmt_cancel = mysqli_prepare($dbconnect, $cancel_query);
    mysqli_stmt_bind_param($stmt_cancel, "is", $offeringID, $email);
    
    if (mysqli_stmt_execute($stmt_cancel)) {
        $update_query = "UPDATE tblcourseofferings SET enrolledCount = enrolledCount - 1 WHERE offeringID = ?";
        $stmt_update = mysqli_prepare($dbconnect, $update_query);
        mysqli_stmt_bind_param($stmt_update, "i", $offeringID);
        mysqli_stmt_execute($stmt_update);
        
        echo "<script>alert('Enrollment cancelled successfully.');</script>";
    } else {
        echo "<script>alert('Error cancelling enrollment.');</script>";
    }

    mysqli_stmt_close($stmt_cancel);
    mysqli_stmt_close($stmt_update);
    header("Location: profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_waitlist'])) {
    $offeringID = $_POST['offeringID'];

    $cancel_waitlist_query = "DELETE FROM tblwaitlist WHERE offeringID = ? AND userID = (SELECT userID FROM tblprofiles WHERE email = ?)";
    $stmt_cancel_waitlist = mysqli_prepare($dbconnect, $cancel_waitlist_query);
    mysqli_stmt_bind_param($stmt_cancel_waitlist, "is", $offeringID, $email);
    
    if (mysqli_stmt_execute($stmt_cancel_waitlist)) {
        echo "<script>alert('Successfully removed from waitlist.');</script>";
    } else {
        echo "<script>alert('Error removing from waitlist.');</script>";
    }

    mysqli_stmt_close($stmt_cancel_waitlist);
    header("Location: profile.php");
    exit;
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
    <title>Student Profile</title>
</head>

<body>
    <div class="jumbotron">
        <div class="container text-center">
            <h1>Student Profile</h1>
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
                        echo '<li><a href="logout.php?Logout=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Your Profile Information</h2>
        <div class="row">
            <div class="col-md-6 col-md-offset-3 profile-info">
                <table class="table table-bordered">
                    <tr>
                        <th>First Name</th>
                        <td><?php echo htmlspecialchars($firstName); ?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><?php echo htmlspecialchars($lastName); ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo htmlspecialchars($phone); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                </table>
                <div class="btn-container">
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
				<p><a href="update_password.php">Update Password</a></p>
            </div>
        </div>

        <h2>Current Course Registration</h2>
        <?php if (mysqli_num_rows($courses_result) > 0): ?>
            <div class="course-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Course Number</th>
                            <th>Course Name</th>
                            <th>Semester</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['courseNumber']); ?></td>
                                <td><?php echo htmlspecialchars($course['courseName']); ?></td>
                                <td><?php echo htmlspecialchars($course['semesterName'] . ' ' . $course['year']); ?></td>
                                <td>
                                    <form method="POST" action="profile.php">
                                        <input type="hidden" name="offeringID" value="<?php echo $course['offeringID']; ?>">
                                        <button type="submit" name="cancel_enrollment" class="btn btn-danger">Cancel Enrollment</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
				<p><a href="course_registration.php">Register for additional courses.</a></p>
            </div>
        <?php else: ?>
            <p class="text-center">You are not enrolled in any courses.</p>
        <?php endif; ?>

		<h2>Your Waitlisted Courses</h2>

		<?php if (mysqli_num_rows($waitlist_result) > 0): ?>
			<div class="course-table">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Course Number</th>
							<th>Course Name</th>
							<th>Semester</th>
							<th>Position</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($waitlist_course = mysqli_fetch_assoc($waitlist_result)): ?>
							<tr>
								<td><?php echo htmlspecialchars($waitlist_course['courseNumber']); ?></td>
								<td><?php echo htmlspecialchars($waitlist_course['courseName']); ?></td>
								<td><?php echo htmlspecialchars($waitlist_course['semesterName'] . ' ' . $waitlist_course['year']); ?></td>
								<td><?php echo htmlspecialchars($waitlist_course['position']); ?></td>
								<td>
									<form method="POST" action="profile.php">
										<input type="hidden" name="offeringID" value="<?php echo $waitlist_course['offeringID']; ?>">
										<button type="submit" name="cancel_waitlist" class="btn btn-danger">Cancel Waitlist</button>
									</form>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		<?php else: ?>
			<p>You are not waitlisted for any courses.</p>
		<?php endif; ?>
    </div>

    <br><br><?php include 'footer.php'; ?>
</body>
</html>