<?php
// Ensure session is started and database is included
session_start();
include "./config/database.php";

// Check if the connection is established
if (!isset($conn) || !$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the image file is uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $fileType = mime_content_type($_FILES['image']['tmp_name']);
    if (strpos($fileType, 'image/') !== 0) {
        echo "Error: Uploaded file is not an image.";
        exit();
    }

    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $imagePath = 'uploads/' . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $image = $imagePath; 
    } else {
        echo "Error: Failed to upload image.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles/productAdminDash.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container">
  

        <!-- Admin Table Section -->
        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th> Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // Query to get all products from the database
                    $query = "SELECT * FROM `add`";
                    $result = mysqli_query($conn, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        // Fetch each row and display it in the table
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Use 'Image' as the key since your table defines it with an uppercase 'I'
                            $imagePath = isset($row['image']) ? $row['image'] : 'uploads/default.jpg'; // Provide a default image if not set
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['pname']); ?>" width="100"></td>
                        <td>
                            <a href="productedit.php?ID=<?php echo urlencode($row['ID']); ?>">
                                <button class="btn bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Edit</button>
                            </a>
                            <a href="productdelete.php?ID=<?php echo urlencode($row['ID']); ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                                <button class="btn bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php
                        }
                    } else {
                        echo "<tr><td colspan='5'>No products found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
