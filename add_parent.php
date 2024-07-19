<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Add Parent</title>
</head>
<body>

<!-- Form to add new parent -->
<div class="container">
    <h1 class="mt-4">Add New Parent</h1>
    <form action="" method="post">
        <div class="form-group mb-3">
            <label for="parentName">Parent Name:</label>
            <input type="text" class="form-control" name="parentName" id="parentName" placeholder="Parent Name" required>
        </div>
        <div class="form-group mb-3">
            <label for="parentAddress">Address:</label>
            <input type="text" class="form-control" name="parentAddress" id="parentAddress" placeholder="Address" required>
        </div>
        <div class="form-group mb-3">
            <label for="parentEmail">Email:</label>
            <input type="email" class="form-control" name="parentEmail" id="parentEmail" placeholder="Email" required>
        </div>
        <div class="form-group mb-3">
            <label for="parentPhone">Phone Number:</label>
            <input type="text" class="form-control" name="parentPhone" id="parentPhone" placeholder="Phone Number" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="list_parents.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = mysqli_connect("localhost", "root", "password", "myschool");

    // Check connection
    if ($link == false) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $parentName = $_POST['parentName'];
    $parentAddress = $_POST['parentAddress'];
    $parentEmail = $_POST['parentEmail'];
    $parentPhone = $_POST['parentPhone'];

    // Check is input valid
    if (empty($parentName) || empty($parentAddress) || empty($parentEmail) || empty($parentPhone)) {
        echo "<div class='alert alert-danger mt-4'>All fields are required.</div>";
    } else {
        $add_parent_sql = "INSERT INTO Parents (ParentName, Address, Email, PhoneNumber) VALUES (?, ?, ?, ?)";
        $stmt = $link->prepare($add_parent_sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $link->error);
        }
        $stmt->bind_param("ssss", $parentName, $parentAddress, $parentEmail, $parentPhone);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>New parent added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error adding new parent: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    $link->close();
}
?>

</body>
</html>
