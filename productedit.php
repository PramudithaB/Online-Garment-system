<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 
include("./config/database.php");

if (isset($_POST['update'])) {
    $pname = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    
    $ID = $_POST['ID'];

    // Check if the 'image' array key is defined and a file has been uploaded
    if (isset($_FILES['image']) && isset($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
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
                if ($fileSize < 10000000) { // Reduced file size limit for practical reasons
                    $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                    $fileDestination = 'uploads/' . $fileNameNew;
                    move_uploaded_file($fileTmpName, $fileDestination);

                    // Update with the new image path
                    $stmt = $conn->prepare("UPDATE `products` SET pname = ?, price = ?, quantity = ?, image = ? WHERE ID = ?");
                    $stmt->bind_param("ssssi", $pname, $price, $quantity, $fileDestination, $ID);
                } else {
                    echo "<script type='text/javascript'>alert('File size exceeds limit');</script>";
                    exit();
                }
            } else {
                echo "<script type='text/javascript'>alert('Error uploading file');</script>";
                exit();
            }
        } else {
            echo "<script type='text/javascript'>alert('Invalid file type');</script>";
            exit();
        }
    } else {
        // No new file uploaded, retain the existing image
        $image = isset($_POST['current_image']) ? $_POST['current_image'] : 'uploads/default.jpg';
        $stmt = $conn->prepare("UPDATE `products` SET pname = ?, price = ?, quantity = ?, image = ? WHERE ID = ?");
        $stmt->bind_param("ssssi", $pname, $price, $quantity, $image, $ID);
    }

    if ($stmt->execute()) {
        // Successful update
        header('Location: productAdminDash.php?message=Record updated successfully!');
        exit();
    } else {
        // Error in execution
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_GET['ID'])) {
    $id = $_GET['ID'];
    $stmt = $conn->prepare("SELECT * FROM `products` WHERE ID = ?");
    $stmt->bind_param("i", $id); // Assuming ID is an integer

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pname = $row['pname'];
        $price = $row['price'];
        $quantity = $row['quantity'];
        // Check if the 'image' key exists; use a default if not
        $image = !empty($row['image']) ? $row['image'] : 'uploads/default.jpg'; // Ensure correct column name
        $ID = $row['ID'];
    } else {
        header('Location: productAdminDash.php');
        exit();
    }

    $stmt->close();
} else {
    header('Location: productAdminDash.php');
    exit();
        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pramudithabandara12@gmail.com';
            $mail->Password   = '#@pramuditha#@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
    
            $mail->setFrom($email, $name);
            $mail->addAddress('it22250742@my.sliit.lk');
    
            // Attach the uploaded file to the email
            $mail->addAttachment($fileDestination);
    
            $mail->isHTML(false);
            $mail->Subject = 'New Form Submission';
            $mail->Body    = "Name: $name\nEmail: $email\nMessage: $message";
    
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="styles/productedit.css">
</head>

<body>
    <div class="container">
        <form action="" method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col">
                    <h3 class="title">Edit Product Form</h3>

                    <!-- Hidden field to hold product ID -->
                    <input type="hidden" name="ID" value="<?php echo $ID; ?>">

                    <!-- Hidden field to hold current image URL if no new image is uploaded -->
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($image); ?>">

                    <!-- Product Name -->
                    <div class="inputBox">
                        <label for="pname">Product Name:</label>
                        <input type="text" name="pname" placeholder="Enter product name" value="<?php echo htmlspecialchars($pname); ?>" required>
                    </div>

                    <!-- Price -->
                    <div class="inputBox">
                        <label for="price">Price:</label>
                        <input type="text" name="price" placeholder="Enter price" value="<?php echo htmlspecialchars($price); ?>" required>
                    </div>

                    <!-- Quantity -->
                    <div class="inputBox">
                        <label for="quantity">Quantity:</label>
                        <input type="number" name="quantity" placeholder="Enter Quantity" value="<?php echo htmlspecialchars($quantity); ?>" required>
                    </div>

                    <!-- Image Upload -->
                    <div class="inputBox">
                        <label for="image">Upload New Image:</label>
                        <input type="file" name="image" accept=".jpg, .jpeg, .png">
                    </div>

                    <!-- Display current image -->
                    <div class="inputBox">
                        <label>Current Image:</label><br>
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Product Image" style="max-width: 300px; height: auto;">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <input type="submit" name="update" value="Update Product" class="submit_btn">
        </form>
    </div>
</body>

</html>
