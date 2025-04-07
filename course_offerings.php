<?php
include 'config.php';

$query = "
    SELECT 
        co.offeringID, 
        co.courseID, 
        co.semesterID, 
        co.maxEnrollment, 
        COUNT(e.enrollmentID) AS enrolledCount,
        c.courseName, 
        s.name AS semesterName, 
        s.year AS semesterYear
    FROM 
        tblcourseofferings co
    JOIN 
        tblcourses c ON co.courseID = c.courseID
    JOIN 
        tblsemesters s ON co.semesterID = s.semesterID
    LEFT JOIN 
        tblenrollments e ON co.offeringID = e.offeringID AND e.enrolled = 1
    GROUP BY 
        co.offeringID, co.courseID, co.semesterID, co.maxEnrollment, c.courseName, s.name, s.year;
";

$result = mysqli_query($dbconnect, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table class='table'>";
    echo "<thead><tr><th>Course</th><th>Semester</th><th>Max Enrollment</th><th>Enrolled Count</th><th>Actions</th></tr></thead><tbody>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['courseName'] . "</td>";
        echo "<td>" . $row['semesterName'] . " " . $row['semesterYear'] . "</td>";
        echo "<td>" . $row['maxEnrollment'] . "</td>";
        echo "<td>" . $row['enrolledCount'] . "</td>";
        echo "<td>";
        
        if ($row['enrolledCount'] < $row['maxEnrollment']) {
			echo "<a href='enroll.php?offeringID=" . $row['offeringID'] . "' class='btn btn-primary'>Enroll</a>";
		} elseif ($row['enrolledCount'] == $row['maxEnrollment']) {
			echo "<a href='waitlist.php?offeringID=" . $row['offeringID'] . "' class='btn btn-warning'>Join Waitlist</a>";
		}
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "No course offerings available.";
}
?>