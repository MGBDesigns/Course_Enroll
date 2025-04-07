<?php
include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['userID'];

$semesters_query = "SELECT semesterID, name, year FROM tblsemesters";
$semesters_result = mysqli_query($dbconnect, $semesters_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['enroll'])) {
        $offeringID = $_POST['offeringID'];

        $check_enrollment_query = "SELECT * FROM tblenrollments WHERE userID = ? AND offeringID = ?";
        $stmt_check = mysqli_prepare($dbconnect, $check_enrollment_query);
        mysqli_stmt_bind_param($stmt_check, "ii", $userID, $offeringID);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($check_result) == 0) {
            $course_check_query = "SELECT * FROM tblcourseofferings WHERE offeringID = ?";
            $stmt_course_check = mysqli_prepare($dbconnect, $course_check_query);
            mysqli_stmt_bind_param($stmt_course_check, "i", $offeringID);
            mysqli_stmt_execute($stmt_course_check);
            $course_check_result = mysqli_stmt_get_result($stmt_course_check);
            $course = mysqli_fetch_assoc($course_check_result);

            if ($course['enrolledCount'] < $course['maxEnrollment']) {
                $enrollment_query = "INSERT INTO tblenrollments (userID, offeringID, enrolled) VALUES (?, ?, NOW())";
                $stmt_enroll = mysqli_prepare($dbconnect, $enrollment_query);
                mysqli_stmt_bind_param($stmt_enroll, "ii", $userID, $offeringID);
                if (mysqli_stmt_execute($stmt_enroll)) {
                    $update_enrollment_query = "UPDATE tblcourseofferings SET enrolledCount = enrolledCount + 1 WHERE offeringID = ?";
                    $stmt_update = mysqli_prepare($dbconnect, $update_enrollment_query);
                    mysqli_stmt_bind_param($stmt_update, "i", $offeringID);
                    mysqli_stmt_execute($stmt_update);

                    $_SESSION['enrollment_success'] = true;
                } else {
                    echo "Error enrolling in the course.";
                }
                mysqli_stmt_close($stmt_enroll);
                mysqli_stmt_close($stmt_update);
            } else {
                $waitlist_query = "INSERT INTO tblwaitlist (userID, offeringID, position) 
                                   SELECT ?, ?, 
                                          IFNULL(MAX(position), 0) + 1 FROM tblwaitlist WHERE offeringID = ?";
                $stmt_waitlist = mysqli_prepare($dbconnect, $waitlist_query);
                mysqli_stmt_bind_param($stmt_waitlist, "iii", $userID, $offeringID, $offeringID);
                if (mysqli_stmt_execute($stmt_waitlist)) {
                    $_SESSION['waitlist_success'] = true;
                } else {
                    echo "Error adding to waitlist.";
                }
                mysqli_stmt_close($stmt_waitlist);
            }
        } else {
            $_SESSION['already_enrolled'] = true;
        }

        mysqli_stmt_close($stmt_check);
        header("Location: course_registration.php?semesterID=" . $_GET['semesterID']);
        exit;
    }
}

$semesterID = isset($_GET['semesterID']) ? $_GET['semesterID'] : null;
$courses_result = null;
if ($semesterID) {
    $courses_query = "SELECT c.courseID, c.courseName, co.offeringID, co.maxEnrollment, co.enrolledCount 
                      FROM tblcourseofferings co
                      JOIN tblcourses c ON co.courseID = c.courseID
                      WHERE co.semesterID = ?";
    $stmt = mysqli_prepare($dbconnect, $courses_query);
    mysqli_stmt_bind_param($stmt, "i", $semesterID);
    mysqli_stmt_execute($stmt);
    $courses_result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $courses_result = null;
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
                        echo '<li><a href="profile.php"><span class="glyphicon glyphicon-education"></span> Student Profile</a></li>';
                        echo '<li><a href="logout.php?Logout=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-center">
        <h1>Course Registration</h1>

        <?php if (isset($_SESSION['waitlist_success']) && $_SESSION['waitlist_success']): ?>
            <div class="alert alert-success">
                You have been added to the waitlist for this course.
            </div>
            <?php unset($_SESSION['waitlist_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['waitlist_error']) && $_SESSION['waitlist_error']): ?>
            <div class="alert alert-danger">
                There was an error adding you to the waitlist.
            </div>
            <?php unset($_SESSION['waitlist_error']); ?>
        <?php endif; ?>

		<form method="GET" action="course_registration.php" class="centered-form">
			<label for="semester">Select Semester:</label>
			<select name="semesterID" id="semester" class="form-control">
				<?php while ($semester = mysqli_fetch_assoc($semesters_result)) { ?>
					<option value="<?php echo $semester['semesterID']; ?>"
						<?php echo ($semester['semesterID'] == $semesterID) ? 'selected' : ''; ?>>
						<?php echo $semester['name'] . ' ' . $semester['year']; ?>
					</option>
				<?php } ?>
			</select><br>
			<button type="submit" class="btn btn-primary">Show Courses</button>
		</form>

        <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
            <h2>Available Courses</h2>
			<table class="table table-bordered custom-table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Max Enrollment</th>
                        <th>Enrolled</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                        <tr>
                            <td><?php echo $course['courseName']; ?></td>
                            <td><?php echo $course['maxEnrollment']; ?></td>
                            <td><?php echo $course['enrolledCount']; ?></td>
                            <td>
                                <?php
                                $enrollment_check_query = "SELECT * FROM tblenrollments WHERE userID = ? AND offeringID = ?";
                                $stmt = mysqli_prepare($dbconnect, $enrollment_check_query);
                                mysqli_stmt_bind_param($stmt, "ii", $userID, $course['offeringID']);
                                mysqli_stmt_execute($stmt);
                                $enrollment_check_result = mysqli_stmt_get_result($stmt);
                                mysqli_stmt_close($stmt);

                                if (mysqli_num_rows($enrollment_check_result) > 0) {
                                    echo '<p>Enrollment Successful!</p>';
                                } else {
                                    if ($course['enrolledCount'] < $course['maxEnrollment']) {
                                        echo '<form method="POST" action="course_registration.php?semesterID=' . $semesterID . '">
                                                <input type="hidden" name="offeringID" value="' . $course['offeringID'] . '">
                                                <button type="submit" name="enroll" class="btn btn-success">Enroll</button>
                                              </form>';
                                    } else {
                                        echo '<form method="POST" action="waitlist.php?semesterID=' . $semesterID . '">
                                                <input type="hidden" name="offeringID" value="' . $course['offeringID'] . '">
                                                <button type="submit" name="waitlist" class="btn btn-warning">Add to Waitlist</button>
                                              </form>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found for the selected semester.</p>
        <?php endif; ?>
    </div>

    <br><br><?php include 'footer.php'; ?>
</body>
</html>
