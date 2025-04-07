<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (empty($firstName) || empty($lastName) || empty($phone) || empty($password)) {
        die("Please fill in all fields.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check_email_query = "SELECT userID FROM tblusers WHERE email = ?";
    $stmt = mysqli_prepare($dbconnect, $check_email_query);
    if ($stmt === false) {
        die("Error preparing email check query: " . mysqli_error($dbconnect));
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        die("Email already exists.");
    }

    mysqli_stmt_close($stmt);

    $insert_user_query = "INSERT INTO tblusers (email, password) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbconnect, $insert_user_query);
    if ($stmt === false) {
        die("Error preparing user insert query: " . mysqli_error($dbconnect));
    }

    mysqli_stmt_bind_param($stmt, "ss", $email, $hashedPassword);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        $userID = mysqli_insert_id($dbconnect);

        $insert_profile_query = "INSERT INTO tblprofiles (userID, firstName, lastName, phone, email) 
                                  VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbconnect, $insert_profile_query);
        if ($stmt === false) {
            die("Error preparing profile insert query: " . mysqli_error($dbconnect));
        }

        mysqli_stmt_bind_param($stmt, "issss", $userID, $firstName, $lastName, $phone, $email);
        $profile_success = mysqli_stmt_execute($stmt);

        if ($profile_success) {
            echo "Student account and profile creation successful! You can now <a href='login.php'>login</a>";
        } else {
            die("Error in profile creation: " . mysqli_error($dbconnect));
        }

    } else {
        die("Error inserting user: " . mysqli_error($dbconnect));
    }

    mysqli_stmt_close($stmt);
    mysqli_close($dbconnect);
}
?>
