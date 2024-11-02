<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to place an order
function placeOrder($conn, $product_id, $quantity)
{
    // Check stock availability
    $stock_check = $conn->query("SELECT stock_quantity FROM Products WHERE product_id = $product_id");
    if ($stock_check->num_rows == 0) {
        echo "<p style='color:red;'>Product ID $product_id not found.</p>";
        return;
    }

    $product = $stock_check->fetch_assoc();
    if ($product['stock_quantity'] < $quantity) {
        echo "<p style='color:red;'>Insufficient stock for Product ID $product_id. Available stock: " . $product['stock_quantity'] . ".</p>";
        return;
    }

    // Insert order and update stock if stock is sufficient
    $conn->query("INSERT INTO Orders (product_id, order_quantity) VALUES ($product_id, $quantity)");
    $conn->query("UPDATE Products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $product_id");
    echo "<p style='color:green;'>Order placed successfully for Product ID: $product_id.</p>";
}

// Function to add a new product
function addProduct($conn, $product_name, $price, $stock_quantity)
{
    $stmt = $conn->prepare("INSERT INTO Products (product_name, price, stock_quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $product_name, $price, $stock_quantity);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Product '$product_name' added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Failed to add product.</p>";
    }
    $stmt->close();
}

// Function to delete a product
function deleteProduct($conn, $product_id)
{
    $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Product ID $product_id deleted successfully!</p>";
    } else {
        echo "<p style='color:red;'>Failed to delete product. Product ID $product_id may not exist.</p>";
    }
    $stmt->close();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order'])) {
        // Place order form submission
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        placeOrder($conn, $product_id, $quantity);
    } elseif (isset($_POST['add_product'])) {
        // Add new product form submission
        $product_name = $_POST['product_name'];
        $price = doubleval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        addProduct($conn, $product_name, $price, $stock_quantity);
    } elseif (isset($_POST['delete_product'])) {
        // Delete product form submission
        $product_id = intval($_POST['product_id']);
        deleteProduct($conn, $product_id);
    }
    // Redirect to the same page to prevent form resubmission
    // header("Location: " . $_SERVER['PHP_SELF']);
    // exit;
}

// Fetch all products to display in a table
$products_result = $conn->query("SELECT * FROM Products");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Inventory System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            color: #333;
        }

        input,
        button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
        }

        .form-container {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>E-commerce Inventory System</h1>

        <!-- Order Form -->
        <div class="form-container">
            <h2>Place a New Order</h2>
            <form method="POST">
                <label for="product_id">Product ID:</label>
                <input type="number" id="product_id" name="product_id" required>

                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required>

                <button type="submit" name="order">Place Order</button>
            </form>
        </div>

        <!-- Add Product Form -->
        <div class="form-container">
            <h2>Add a New Product</h2>
            <form method="POST">

                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" required>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>

                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" required>

                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>

        <!-- Delete Product Form -->
        <div class="form-container">
            <h2>Delete a Product</h2>
            <form method="POST">
                <label for="product_id">Product ID:</label>
                <input type="number" id="product_id" name="product_id" required>

                <button type="submit" name="delete_product">Delete Product</button>
            </form>
        </div>

        <!-- Display Products Table -->
        <h2>Available Products</h2>
        <table>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Stock Quantity</th>
            </tr>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $product['product_id']; ?></td>
                    <td><?php echo $product['product_name']; ?></td>
                    <td><?php echo $product['price']; ?></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>