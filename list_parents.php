<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>List all Parents</title>
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
          <a class="nav-link active" aria-current="page" href="list_parents.php">Parents</a>
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
    die("Connection failed: ");
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1 class="mt-4">All Parents</h1>
        <a href="add_parent.php" class="btn btn-success">Add Parent</a>
    </div>
    <?php
    // SQL to get parents' details and students' name
    $sql = "SELECT
                p.ParentID,
                p.ParentName,
                p.Address,
                p.Email,
                p.PhoneNumber,
                GROUP_CONCAT(s.StudentName SEPARATOR ', ') AS Students
            FROM
                Parents p
            LEFT JOIN
                Student_Parent_Relationship spr ON p.ParentID = spr.ParentID
            LEFT JOIN
                Students s ON spr.StudentID = s.StudentID
            GROUP BY
                p.ParentID, p.ParentName, p.Address, p.Email, p.PhoneNumber
            ORDER BY
                p.ParentID";

    $result = $link->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Parent ID</th>
                        <th>Parent Name</th>
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";

        // Show data in table format
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["ParentID"] . "</td>
                    <td>" . $row["ParentName"] . "</td>
                    <td>" . $row["Address"] . "</td>
                    <td>" . $row["PhoneNumber"] . "</td>
                    <td>" . $row["Email"] . "</td>
                    <td>" . (isset($row["Students"]) ? $row["Students"] : 'No Students Assigned') . "</td>
                    <td>
                        <a href='edit_parent.php?id=" . $row["ParentID"] . "' class='btn btn-warning btn-sm'>Edit</a>
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
