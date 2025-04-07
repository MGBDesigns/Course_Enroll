<?php
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get logged-in user's ID (assuming it's stored in session)
$userID = $_SESSION['userID'];

// Handle course enrollment
if (isset($_POST['enroll'])) {
    $offeringID = $_POST['offeringID'];

    // Check if the course is already fully enrolled
    $query = "SELECT maxEnrollment, enrolledCount FROM tblcourseofferings WHERE offeringID = ?";
    $stmt = mysqli_prepare($dbconnect, $query);
    mysqli_stmt_bind_param($stmt, "i", $offeringID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $maxEnrollment, $enrolledCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($enrolledCount < $maxEnrollment) {
        // Enroll the student in the course
        $insert_query = "INSERT INTO tblenrollments (userID, offeringID, enrolled) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($dbconnect, $insert_query);
        mysqli_stmt_bind_param($stmt, "ii", $userID, $offeringID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Update enrolled count
        $update_query = "UPDATE tblcourseofferings SET enrolledCount = enrolledCount + 1 WHERE offeringID = ?";
        $stmt = mysqli_prepare($dbconnect, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $offeringID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "You have successfully enrolled in the course!";
    } else {
        // Add to the waitlist if the course is full
        $insert_waitlist_query = "INSERT INTO tblwaitlist (userID, offeringID, position, added) SELECT ?, ?, COUNT(*) + 1, NOW() FROM tblwaitlist WHERE offeringID = ?";
        $stmt = mysqli_prepare($dbconnect, $insert_waitlist_query);
        mysqli_stmt_bind_param($stmt, "iii", $userID, $offeringID, $offeringID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "The course is full. You have been added to the waitlist.";
    }
}

// Handle course cancellation
if (isset($_POST['cancel_enrollment'])) {
    $enrollmentID = $_POST['enrollmentID'];

    // Remove the enrollment
    $delete_query = "DELETE FROM tblenrollments WHERE enrollmentID = ?";
    $stmt = mysqli_prepare($dbconnect, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $enrollmentID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Decrease the enrolled count
    $update_query = "UPDATE tblcourseofferings SET enrolledCount = enrolledCount - 1 WHERE offeringID = (SELECT offeringID FROM tblenrollments WHERE enrollmentID = ?)";
    $stmt = mysqli_prepare($dbconnect, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $enrollmentID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Check if there's anyone on the waitlist for this course
    $check_waitlist_query = "SELECT waitlistID, userID FROM tblwaitlist WHERE offeringID = (SELECT offeringID FROM tblenrollments WHERE enrollmentID = ?) ORDER BY position LIMIT 1";
    $stmt = mysqli_prepare($dbconnect, $check_waitlist_query);
    mysqli_stmt_bind_param($stmt, "i", $enrollmentID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $waitlistID, $waitlistUserID);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($waitlistID) {
        // Notify the first user on the waitlist
        // For simplicity, we just update the waitlist position here.
        $update_waitlist_query = "UPDATE tblwaitlist SET position = position - 1 WHERE waitlistID = ?";
        $stmt = mysqli_prepare($dbconnect, $update_waitlist_query);
        mysqli_stmt_bind_param($stmt, "i", $waitlistID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo "You have successfully canceled your enrollment. The first person on the waitlist has been notified.";
    } else {
        echo "You have successfully canceled your enrollment.";
    }
}

// Fetch available semesters
$semesters_query = "SELECT semesterID, name, year FROM tblsemesters";
$semesters_result = mysqli_query($dbconnect, $semesters_query);

// Fetch available courses for the selected semester
$semesterID = isset($_GET['semesterID']) ? $_GET['semesterID'] : null;
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
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
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
                    <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
                    <li><a href="index.php"><span class="glyphicon glyphicon-envelope"></span> Contact Us</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        echo '<li><a href="course_offerings.php"><span class="glyphicon glyphicon-education"></span> Course Offerings</a></li>';
                        echo '<li><a href="profile.php"><span class="glyphicon glyphicon-education"></span> Student Profile</a></li>';
                        echo '<li><a href="index.php?Logout=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-center">
        <h1>Course Registration</h1>
        <form method="GET" action="course_registration.php">
            <label for="semester">Select Semester:</label>
            <select name="semesterID" id="semester" class="form-control">
                <?php while ($semester = mysqli_fetch_assoc($semesters_result)) { ?>
                    <option value="<?php echo $semester['semesterID']; ?>" <?php echo ($semester['semesterID'] == $semesterID) ? 'selected' : ''; ?>>
                        <?php echo $semester['name'] . ' ' . $semester['year']; ?>
                    </option>
                <?php } ?><br>
            </select>
            <button type="submit" class="btn btn-primary">Show Courses</button>
        </form>

        <?php if ($courses_result): ?>
            <h2>Available Courses</h2>
            <table class="table">
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
                                // Check if the user is already enrolled
                                $enrolled_query = "SELECT enrollmentID FROM tblenrollments WHERE userID = ? AND offeringID = ?";
                                $stmt = mysqli_prepare($dbconnect, $enrolled_query);
                                mysqli_stmt_bind_param($stmt, "ii", $userID, $course['offeringID']);
                                mysqli_stmt_execute($stmt);
                                mysqli_stmt_store_result($stmt);

                                if (mysqli_stmt_num_rows($stmt) > 0) {
                                    echo '<form method="POST" action="course_registration.php">
                                            <input type="hidden" name="enrollmentID" value="' . $course['offeringID'] . '">
                                            <button type="submit" name="cancel_enrollment" class="btn btn-danger">Cancel Enrollment</button>
                                          </form>';
                                } else {
                                    echo '<form method="POST" action="course_registration.php">
                                            <input type="hidden" name="offeringID" value="' . $course['offeringID'] . '">
                                            <button type="submit" name="enroll" class="btn btn-success">Enroll</button>
                                          </form>';
                                }
                                mysqli_stmt_close($stmt);
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>