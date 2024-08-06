<?php

    include 'import/connect.php';

    // Fetch food items
    $get_items_query = "SELECT * FROM fooditems";
    $result = mysqli_query($connect, $get_items_query);
    $food_items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $food_items[$row['category']][] = $row;
    }

    if (isset($_POST['submit'])) {

        $customer_name = $_POST['customer_name'];
        $table_number = $_POST['item_name'];
        $food_items = $_POST['food_items'];
        $quantities = $_POST['quantities'];

        // Check if the table is already booked
        $check_table_query = "SELECT table_number FROM orders WHERE table_number = '$table_number'";
        $result_table = mysqli_query($connect, $check_table_query);

        $check_name_query = "SELECT customer_name FROM orders WHERE customer_name = '$customer_name'";
        $result_name = mysqli_query($connect, $check_name_query);
        $row = mysqli_fetch_assoc($result_name);

        if (mysqli_num_rows($result_table) > 0 && mysqli_num_rows($result_name) > 0) {
            echo "<script>alert('Table Already Booked');</script>";
        } else {
        // Insert order details into the orders table

            $insert_order_query = "INSERT INTO orders (customer_name, table_number) VALUES ('$customer_name', '$table_number')";
            $result = mysqli_query($connect, $insert_order_query);
            $order_id = mysqli_insert_id($connect);

        // echo $order_id;

        // Insert each food item into the order_items table
        foreach ($food_items as $index => $food_item) {
            $quantity = $quantities[$index];
            $insert_item_query = "INSERT INTO order_items (order_id, food_item, quantity, table_number) VALUES ('$order_id', '$food_item', '$quantity','$table_number')";
            $result_insert = mysqli_query($connect, $insert_item_query);
        }

        if ($result && $result_insert) {
            echo '<div class="alert alert-info" role="alert">
                      Order placed successfully!
                  </div>';
            echo '<script>
                      setTimeout(function() {
                          window.location.href = "takeorder.php";
                      }, 1000); // Redirect after 1 second
                  </script>';
        } else {
            echo "Error: " . mysqli_error($connect);
        }

        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Order</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var foodItems = <?php echo json_encode($food_items); ?>;
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mt-4">Take Order</h2>
        <form method="post">
            <div class="form-group">
                <label for="customer_name">Customer Name:</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>

            <div class="form-group">
                <label for="item_name">Table Number:</label>
                <select id="item_name" name="item_name" class="form-control">
                    <option value="1">One</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                </select>
            </div>

            <table class="table table-bordered" id="orderTable">
                <thead>
                    <tr>
                        <th>Food Item</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select class="form-control" name="food_items[]">
                                <?php
                                foreach ($food_items as $category => $items) {
                                    echo '<optgroup label="' . htmlspecialchars($category) . '">';
                                    foreach ($items as $item) {
                                        echo '<option value="' . htmlspecialchars($item['name']) . '">' . htmlspecialchars($item['name']) . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="quantities[]" min="1" value="1">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" id="addRow">Add More Items</button>
            <button type="submit" name="submit" class="btn btn-primary">Submit Order</button>
        </form>
    </div>

    <script>
        $(document).ready(function(){
            $('#addRow').click(function(){
                var newRow = `<tr>
                    <td>
                        <select class="form-control" name="food_items[]">
                            ${Object.keys(foodItems).map(category => `
                                <optgroup label="${category}">
                                    ${foodItems[category].map(item => `<option value="${item.name}">${item.name}</option>`).join('')}
                                </optgroup>`).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="quantities[]" min="1" value="1">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                    </td>
                </tr>`;
                $('#orderTable tbody').append(newRow);
            });

            $('#orderTable').on('click', '.remove-row', function(){
                $(this).closest('tr').remove();
            });
        });
    </script>
</body>
</html>
