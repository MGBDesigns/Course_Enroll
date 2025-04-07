<?php

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cancel_registration'])) {
        $offeringID = $_POST['offeringID'];

        $remove_enrollment_query = "DELETE FROM tblenrollments WHERE userID = ? AND offeringID = ?";
        $stmt_remove = mysqli_prepare($dbconnect, $remove_enrollment_query);
        mysqli_stmt_bind_param($stmt_remove, "ii", $userID, $offeringID);
        $result_remove = mysqli_stmt_execute($stmt_remove);

        if ($result_remove) {
            $update_enrollment_query = "UPDATE tblcourseofferings SET enrolledCount = enrolledCount - 1 WHERE offeringID = ?";
            $stmt_update = mysqli_prepare($dbconnect, $update_enrollment_query);
            mysqli_stmt_bind_param($stmt_update, "i", $offeringID);
            mysqli_stmt_execute($stmt_update);

            $waitlist_query = "SELECT * FROM tblwaitlist WHERE offeringID = ? ORDER BY position ASC LIMIT 1";
            $stmt_waitlist = mysqli_prepare($dbconnect, $waitlist_query);
            mysqli_stmt_bind_param($stmt_waitlist, "i", $offeringID);
            mysqli_stmt_execute($stmt_waitlist);
            $waitlist_result = mysqli_stmt_get_result($stmt_waitlist);

            if ($waitlist_row = mysqli_fetch_assoc($waitlist_result)) {
                $waitlisted_userID = $waitlist_row['userID'];

                $user_email_query = "SELECT email FROM tblusers WHERE userID = ?";
                $stmt_email = mysqli_prepare($dbconnect, $user_email_query);
                mysqli_stmt_bind_param($stmt_email, "i", $waitlisted_userID);
                mysqli_stmt_execute($stmt_email);
                $email_result = mysqli_stmt_get_result($stmt_email);
                $user_data = mysqli_fetch_assoc($email_result);
                $email = $user_data['email'];

                $subject = "Course Enrollment Slot Available";
                $message = "Dear Student,\n\nA spot has opened up in the course you were waitlisted for. You are the first person on the waitlist, and we invite you to enroll now.\n\nPlease visit the course registration page to complete your enrollment.\n\nBest regards,\nThe Course Registration Team";
                $headers = "From: no-reply@yourwebsite.com";

                if (mail($email, $subject, $message, $headers)) {
                    echo "Email notification sent to the waitlisted student.";
                } else {
                    echo "Error sending email.";
                }
            } else {
                echo "No students are currently on the waitlist.";
            }

            mysqli_stmt_close($stmt_waitlist);
            mysqli_stmt_close($stmt_email);
        } else {
            echo "Error canceling the enrollment.";
        }

        mysqli_stmt_close($stmt_remove);
        mysqli_stmt_close($stmt_update);
    }
}

?>
