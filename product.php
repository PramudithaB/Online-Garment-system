<?php
session_start();
include("./config/database.php");

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    $Name = mysqli_real_escape_string($conn, $_POST['name']);
    $Email = mysqli_real_escape_string($conn, $_POST['email']);
    $Message = mysqli_real_escape_string($conn, $_POST['message']);

    // Handling file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['image']['name'];
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileError = $_FILES['image']['error'];
        $fileType = $_FILES['image']['type'];

        // Set allowed file extensions
        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));
        $allowed = array('jpg', 'jpeg', 'png');

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 1000000) { // File size limit (1MB)
                    // Generate a unique file name
                    $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                    // Ensure the uploads directory exists
                    $uploadDir = './uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileDestination = $uploadDir . $fileNameNew;

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        // Insert data into the database with image path
                        $query = "INSERT INTO `add` (name, email, message, image) VALUES ('$Name', '$Email', '$Message', '$fileDestination')";

                        if (mysqli_query($conn, $query)) {
                            echo "<script type='text/javascript'>alert('Successfully Created'); window.location.href = 'http://localhost/garment/productAdminDash.php';</script>";
                            exit(); // Ensure no further output after redirection
                        } else {
                            echo "<script type='text/javascript'>alert('Database Error');</script>";
                        }
                    } else {
                        echo "<script type='text/javascript'>alert('Failed to move uploaded file');</script>";
                    }
                } else {
                    echo "<script type='text/javascript'>alert('File size exceeds limit');</script>";
                }
            } else {
                echo "<script type='text/javascript'>alert('Error uploading file');</script>";
            }
        } else {
            echo "<script type='text/javascript'>alert('Invalid file type');</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Please select a valid image');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/product.css">
    <link rel="stylesheet" type="text/css" href="styles/header-footer.css">

  

</head>
<body>
    <div class="container">
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col">
                    <h3 class="title">Add </h3>

                    <div class="inputBox">
                        <label for="name">Name:</label>
                        <input type="text" name="name"  required>
                    </div>

                    <div class="inputBox">
                        <label for="email">Email:</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="inputBox">
                        <label for="message">Message:</label>
                        <input type="text" name="message"required>
                    </div>

                    <div class="inputBox">
                        <label for="image">Image:</label>
                        <input type="file" name="image" placeholder="Browse your computer" required>
                    </div>

                </div>
            </div>
            <input type="submit" name="submit" value="Add " class="submit_btn">
        </form>
    </div>
</body>
</html>
