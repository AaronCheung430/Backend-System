<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>List all Teachers</title>
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
          <a class="nav-link" href="index.php">Students</a>
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
          <a class="nav-link active" aria-current="page" href="list_teachers.php">Teachers</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?php
$link = mysqli_connect("localhost", "root", "password", "myschool");

// Check connection
if ($link == false) {
    die("Connection failed: ");
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <!-- Show title, and button for add teacher -->
        <h1 class="mt-4">All Teachers</h1>
        <a href="add_teacher.php" class="btn btn-success">Add Teacher</a>
    </div>
    <?php
    // SQL to get teachers' details and class name
    $sql = "SELECT
                Teachers.TeacherID,
                Teachers.TeacherName,
                Teachers.Address,
                Teachers.PhoneNumber,
                Teachers.AnnualSalary,
                Teachers.BackgroundChecked,
                Classes.ClassName
            FROM
                Teachers
            LEFT JOIN
                Classes ON Teachers.TeacherID = Classes.TeacherID
            ORDER BY
                Teachers.TeacherID";

    $result = $link->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Teacher Name</th>
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Annual Salary</th>
                        <th>Background Checked</th>
                        <th>Class Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

        // Show data in table format
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["TeacherID"] . "</td>
                    <td>" . $row["TeacherName"] . "</td>
                    <td>" . $row["Address"] . "</td>
                    <td>" . $row["PhoneNumber"] . "</td>
                    <td>" . $row["AnnualSalary"] . "</td>
                    <td>" . ($row["BackgroundChecked"] ? 'Yes' : 'No') . "</td>
                    <td>" . (isset($row["ClassName"]) ? $row["ClassName"] : 'No Class Assigned') . "</td>
                    <td>
                        <a href='edit_teacher.php?id=" . $row["TeacherID"] . "' class='btn btn-warning btn-sm'>Edit</a>
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
