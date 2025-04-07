<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['waitlist'])) {
    $offeringID = $_POST['offeringID'];
    $userID = $_SESSION['userID'];

    $waitlist_query = "SELECT MAX(position) AS max_position FROM tblwaitlist WHERE offeringID = ?";
    $stmt_waitlist_check = mysqli_prepare($dbconnect, $waitlist_query);
    mysqli_stmt_bind_param($stmt_waitlist_check, "i", $offeringID);
    mysqli_stmt_execute($stmt_waitlist_check);
    $waitlist_result = mysqli_stmt_get_result($stmt_waitlist_check);
    $waitlist_row = mysqli_fetch_assoc($waitlist_result);

    $position = $waitlist_row['max_position'] + 1;

    $add_to_waitlist_query = "INSERT INTO tblwaitlist (userID, offeringID, position) VALUES (?, ?, ?)";
    $stmt_add_waitlist = mysqli_prepare($dbconnect, $add_to_waitlist_query);
    mysqli_stmt_bind_param($stmt_add_waitlist, "iii", $userID, $offeringID, $position);
    
    if (mysqli_stmt_execute($stmt_add_waitlist)) {
        $_SESSION['waitlist_success'] = true;
    } else {
        $_SESSION['waitlist_error'] = true;
    }
    
    mysqli_stmt_close($stmt_add_waitlist);
    mysqli_stmt_close($stmt_waitlist_check);

    header("Location: course_registration.php?semesterID=" . $_GET['semesterID']);
    exit;
}
?>