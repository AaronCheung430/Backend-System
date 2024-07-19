<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>List all Students</title>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-md">
    <a class="navbar-brand" href="#">School Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Navbar item with each directs to different pages -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php">Students</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_parents.php">Parents</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_classes.php">Classes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_activities.php">Activities</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_teachers.php">Teachers</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php
$link = mysqli_connect("localhost", "root", "password", "myschool");

// Check connection
if ($link == false) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <!-- Show title, and button for add student -->
        <h1 class="mt-4">All Students</h1>
        <a href="add_student.php" class="btn btn-success">Add Student</a>
    </div>
    <?php
    // SQL Command to get data
    $sql = "SELECT
                s.StudentID,
                s.StudentName,
                s.Gender,
                s.Age,
                s.MedicalInformation,
                c.ClassName,
                GROUP_CONCAT(DISTINCT p.ParentName SEPARATOR ', ') AS Parents,
                GROUP_CONCAT(DISTINCT a.ActivityName SEPARATOR ', ') AS Activities
            FROM
                Students s
            LEFT JOIN
                Classes c ON s.ClassID = c.ClassID
            LEFT JOIN
                Student_Parent_Relationship spr ON s.StudentID = spr.StudentID
            LEFT JOIN
                Parents p ON spr.ParentID = p.ParentID
            LEFT JOIN
                Student_Activity_Relationship sar ON s.StudentID = sar.StudentID
            LEFT JOIN
                Activities a ON sar.ActivityID = a.ActivityID
            GROUP BY
                s.StudentID,
                s.StudentName,
                s.Gender,
                s.Age,
                s.MedicalInformation,
                c.ClassName";

    $result = $link->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Class Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

        // Show data in table format
        while($row = $result->fetch_assoc()) {
            $studentID = $row["StudentID"];
            echo "<tr>
                    <td>" . $row["StudentName"] . "</td>
                    <td>" . $row["Gender"] . "</td>
                    <td>" . $row["Age"] . "</td>
                    <td>" . $row["ClassName"] . "</td>
                    <td>
                        <button class='btn btn-primary btn-sm' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$studentID' aria-expanded='false' aria-controls='collapse$studentID'>
                            View
                        </button>
                        <a href='edit_student.php?id=$studentID' class='btn btn-warning btn-sm'>Edit</a>
                    </td>
                  </tr>
                  <tr>
                    <td colspan='5'>
                      <div class='collapse' id='collapse$studentID'>
                        <div class='card card-body' id='details-content-$studentID'>
                          <p><strong>Medical Information:</strong> " . $row["MedicalInformation"] . "</p>
                          <p><strong>Activities Joined:</strong> " . $row["Activities"] . "</p>
                          <p><strong>Parents:</strong> " . $row["Parents"] . "</p>
                        </div>
                      </div>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "0 results";
    }

    $link->close();
    ?>
</div>

</body>
</html>
