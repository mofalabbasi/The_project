<?php
require_once "includes/auth.php";
require_login();
include "includes/connect_db.php";

$errors = [];
$successMessage = '';
$address = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $cartId = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($cartId === false) {
        $errors['general'] = 'Invalid cart item.';
    } else {
        $stmt = $db->prepare("DELETE FROM carts WHERE id = :id AND userId = :userId");
        $stmt->execute([
            ':id' => $cartId,
            ':userId' => (int)$_SESSION['id']
        ]);
        $successMessage = $stmt->rowCount() > 0 ? 'Book removed from cart.' : 'Cart item not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Request_form'])) {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($address === '') {
        $errors['address_err'] = 'Address is Required';
    }

    if ($phone === '' || !preg_match('/^77[0-9]{7}$/', $phone)) {
        $errors['phone_err'] = 'Invalid Yemen mobile number. Example: 771234567';
    }

    if (empty($errors)) {
        $userId = (int)$_SESSION['id'];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT * FROM carts WHERE userId = :userId ORDER BY id ASC");
            $stmt->execute([':userId' => $userId]);
            $carts = $stmt->fetchAll();

            if (!$carts) {
                $db->rollBack();
                $errors['general'] = "You don't have any books in your shopping cart.";
            } else {
                $orderDate = date('Y-m-d');

                foreach ($carts as $cart) {
                    // Lock the matching book row while checking and changing stock.
                    $bookStmt = $db->prepare(
                        "SELECT book_id, quantity FROM books
                         WHERE book_name = :bookName AND author = :authorName
                         LIMIT 1 FOR UPDATE"
                    );
                    $bookStmt->execute([
                        ':bookName' => $cart['name'],
                        ':authorName' => $cart['authorName']
                    ]);
                    $book = $bookStmt->fetch();

                    if (!$book) {
                        throw new RuntimeException('One of the books in your cart is no longer available.');
                    }

                    if ((int)$book['quantity'] < 1) {
                        throw new RuntimeException('One of the books in your cart is out of stock.');
                    }

                    $orderStmt = $db->prepare(
                        "INSERT INTO orders
                         (userId, address, phone, bookName, authorName, orderDate, price, img)
                         VALUES (:userId, :address, :phone, :bookName, :authorName, :orderDate, :price, :img)"
                    );
                    $orderStmt->execute([
                        ':userId' => $userId,
                        ':address' => $address,
                        ':phone' => (int)$phone,
                        ':bookName' => $cart['name'],
                        ':authorName' => $cart['authorName'],
                        ':orderDate' => $orderDate,
                        ':price' => $cart['price'],
                        ':img' => $cart['img']
                    ]);

                    $newQuantity = (int)$book['quantity'] - 1;
                    $stockStmt = $db->prepare("UPDATE books SET quantity = :quantity WHERE book_id = :bookId");
                    $stockStmt->execute([
                        ':quantity' => $newQuantity,
                        ':bookId' => (int)$book['book_id']
                    ]);

                    if ($newQuantity < 0) {
                        throw new RuntimeException('Invalid stock quantity.');
                    }
                }

                // Delete the cart only after every order and stock update succeeded.
                $deleteCartStmt = $db->prepare("DELETE FROM carts WHERE userId = :userId");
                $deleteCartStmt->execute([':userId' => $userId]);

                $db->commit();
                $successMessage = 'Your request has been sent successfully!';
                $address = '';
                $phone = '';
            }
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if (empty($errors['general'])) {
                $errors['general'] = $e->getMessage();
            }
        }
    }
}

$userId = (int)$_SESSION['id'];
$stmt = $db->prepare("SELECT * FROM carts WHERE userId = :userId ORDER BY id ASC");
$stmt->execute([':userId' => $userId]);
$carts = $stmt->fetchAll();
$totalAmount = 0;
foreach ($carts as $cart) {
    $totalAmount += (float)$cart['price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Books - Cart</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/all.min.css" />
    <link rel="stylesheet" href="css/boot.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap" rel="stylesheet" />
</head>
<body>
<?php include "includes/header.php"; ?>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="project text-center pt-5 pb-5">
    <h4 class="text-black fw-bold">SHOPPING CART</h4>
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
                <?php foreach ($carts as $cart): ?>
                    <tr>
                        <td><img class="rounded-circle" width="60" src="<?php echo htmlspecialchars($cart['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book cover"></td>
                        <td><?php echo htmlspecialchars($cart['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($cart['authorName'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($cart['publicationDate'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="price"><?php echo htmlspecialchars((string)$cart['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><a class="btn btn-danger" href="cart.php?action=delete&id=<?php echo (int)$cart['id']; ?>">Delete from cart</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($carts)): ?>
                    <tr><td colspan="6">Your cart is empty.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="col-lg-3 mt-3">
            <fieldset class="border pb-5 project container text-center">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                    <div>
                        <input placeholder="Enter Your Address" class="w-100 rounded-1 mt-5 ps-2 border border-dark-subtle" name="address" type="text" value="<?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>" />
                        <p class="text-danger"><?php echo htmlspecialchars($errors['address_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div>
                        <input placeholder="Enter Your Phone Number" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle" name="phone" type="tel" inputmode="numeric" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" />
                        <p class="text-danger"><?php echo htmlspecialchars($errors['phone_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div>
                        <p class="w-100 rounded-1 mt-5 ps-2 border border-dark-subtle">Total cart: <span><?php echo htmlspecialchars((string)$totalAmount, ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>
                    <input type="submit" value="Send The Request" class="btn btn-primary w-75 mt-3" name="Request_form" <?php echo empty($carts) ? 'disabled' : ''; ?>>
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
