<?php
session_start();
if(!isset($_SESSION['id']))
{
  header('refresh:0;index.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Books</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/all.min.css" />
    <link rel="stylesheet" href="css/boot.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap"
        rel="stylesheet" />
</head>

<body>
    <?php
    include "includes/connect_db.php";
    include "includes/header.php";

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $db->prepare("DELETE FROM carts WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            echo "<div class='alert alert-success'role='alert'>Book deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'role='alert'>Book not found or could not be deleted.</div>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["Request_form"])) {
            $address = $_POST['address'];
            $phone = $_POST['phone'];

            if (empty(trim($address))) {
                $errors["address_err"] = "Address is Required";
            }

            // Validate phone number
            if (empty(trim($phone)) || !is_numeric($phone) || ($phone < 770000000 || $phone > 779999999)) {
                $errors["phone_err"] = "Invalid phone number format";
            }

            if (empty($errors)) {
                $userId = $_SESSION['id'];
                $stmt = $db->prepare("SELECT * FROM carts WHERE userId = :userId");
                $stmt->bindParam(':userId', $userId);
                $stmt->execute();

                $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($carts) {

                    foreach ($carts as $cart) {

                        // $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
                        $userId = $cart['userId'];
                        $bookName = $cart['name'];
                        $authorName = $cart['authorName'];
                        $price = $cart['price'];
                        $targetFile = $cart['img'];

                        $orderDate = date("Y-m-d");


                        $stmt = $db->prepare("INSERT INTO orders (userId, address, phone, bookName, authorName, orderDate, price, img) VALUES (:userId, :address, :phone, :bookName, :authorName, :orderDate, :price, :img)");
                        $stmt->bindParam(':userId', $userId);
                        $stmt->bindParam(':address', $address);
                        $stmt->bindParam(':phone', $phone);
                        $stmt->bindParam(':bookName', $bookName);
                        $stmt->bindParam(':authorName', $authorName);
                        $stmt->bindParam(':orderDate', $orderDate);
                        $stmt->bindParam(':price', $price);
                        $stmt->bindParam(':img', $targetFile);
                        $stmt->execute();

                        $stmt = $db->prepare("DELETE FROM carts WHERE userId = :userId");
                        $stmt->bindValue(':userId', $userId);
                        $stmt->execute();

                        echo "<div class='alert alert-success' role='alert'>Your Request has been sent successfully!</div>";

                    }
                } else {
                    echo "<div class='alert alert-danger'role='alert'>You don't have any books in your shopping cart.</div>";
                }

            }
        }
    }






    ?>

    <div class="project text-center pt-5 pb-5">
        <h4 class="text-black fw-bold"> SHOPPING CART </h4>
        <i class="fa-solid fa-cart-shopping fs-1"></i>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-9 mt-3">
                <table class="table table-bordered text-center">
                    <thead class="table-info">
                        <tr>
                            <th>The Book</th>
                            <th>The name</th>
                            <th>Author Name</th>
                            <th>Publication Date</th>
                            <th>Price</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $userId = $_SESSION['id'];
                        $stmt = $db->prepare("SELECT * FROM carts Where userId = :userId");
                        $stmt->bindParam(':userId', $userId);
                        $stmt->execute();
                        $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $i = 0;
                        foreach ($carts as $cart) {
                            $i++;
                            echo "<tr>";
                            echo "<td> <img class='rounded-circle' width=60px src='" . $cart['img'] . "' /> </td>";
                            echo "<td> " . $cart['name'] . "</td>";
                            echo "<td> " . $cart['authorName'] . "</td>";
                            echo "<td> " . $cart['publicationDate'] . "</td>";
                            echo "<td class='price'>" . $cart['price'] . "</td>";

                            echo "<td><a class='btn btn-danger' href='cart.php?action=delete&id=" . $cart['id'] . "'>Delete from cart</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-3 mt-3">
                <fieldset class="border  pb-5 project container text-center">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div>
                            <input placeholder="Enter Your Address"
                                class="w-100 rounded-1 mt-5 ps-2 border border-dark-subtle" name="address" type="text"
                                value="<?php echo isset($address) ? $address : ""; ?>" />
                            <p class="text-danger">
                                <?php echo isset($errors["address_err"]) ? $errors["address_err"] : ""; ?>
                            </p>
                        </div>
                        <div>
                            <input placeholder="Enter Your Phone Number"
                                class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle" name="phone" type="number"
                                value="<?php echo isset($phone) ? $phone : ""; ?>" />
                            <p class="text-danger">
                                <?php echo isset($errors["phone_err"]) ? $errors["phone_err"] : ""; ?>
                            </p>
                        </div>
                        <div>
                            <?php
                            $userId = $_SESSION['id'];
                            $stmt = $db->prepare("SELECT SUM(carts.price) AS total_order_amount FROM carts WHERE carts.userId = :userId");
                            $stmt->bindParam(':userId', $userId);
                            $stmt->execute();
                            $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($carts as $carts) {
                                echo "<p class='w-100 rounded-1 mt-5 ps-2 border border-dark-subtle'>Total carts is : <span>" . $carts['total_order_amount'] . "</span></p>";
                            }
                            ?>
                        </div>
                        <input type="submit" value="Send The Request" class="btn btn-primary w-75 mt-3"
                            name="Request_form">
                    </form>
                </fieldset>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/all.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>