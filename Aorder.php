<?php
require_once "includes/auth.php";
require_admin();
include "includes/connect_db.php";

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'], $_GET['delete'])) {
    $orderId = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($orderId === false) {
        $errorMessage = 'Invalid order ID.';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1 FOR UPDATE");
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException('Order not found.');
            }

            // Deleting an order means cancelling it, so return one copy to stock.
            $bookStmt = $db->prepare(
                "SELECT book_id, quantity FROM books
                 WHERE book_name = :bookName AND author = :authorName
                 LIMIT 1 FOR UPDATE"
            );
            $bookStmt->execute([
                ':bookName' => $order['bookName'],
                ':authorName' => $order['authorName']
            ]);
            $book = $bookStmt->fetch();

            if ($book) {
                $newQuantity = (int)$book['quantity'] + 1;
                $stockStmt = $db->prepare("UPDATE books SET quantity = :quantity WHERE book_id = :bookId");
                $stockStmt->execute([
                    ':quantity' => $newQuantity,
                    ':bookId' => (int)$book['book_id']
                ]);
            }

            $deleteStmt = $db->prepare("DELETE FROM orders WHERE id = :id");
            $deleteStmt->execute([':id' => $orderId]);

            if ($deleteStmt->rowCount() !== 1) {
                throw new RuntimeException('Order could not be deleted.');
            }

            $db->commit();
            $successMessage = $book
                ? 'Order cancelled and the book quantity was restored.'
                : 'Order deleted. The original book is no longer in the catalog, so stock could not be restored.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errorMessage = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Market Books - Orders</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap" rel="stylesheet" />
</head>
<body>
<?php include "includes/adminHeader.php"; ?>

<?php if ($successMessage !== ''): ?>
  <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
  <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="project text-center pt-5 pb-5">
  <h4 class="text-black fw-bold">Management of Orders</h4>
  <i class="fa-solid fa-truck-fast fs-1"></i>
</div>

<div class="container-fluid">
  <table class="table table-bordered text-center">
    <thead class="table-info">
      <tr>
        <th>Order Id</th>
        <th>Photo</th>
        <th>Book Name</th>
        <th>Author</th>
        <th>Price</th>
        <th>Order Date</th>
        <th>Customer Name</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $stmt = $db->query(
        "SELECT o.id, o.address, o.phone, o.bookName, o.authorName, o.orderDate,
                o.price, o.img, u.name
         FROM orders AS o
         JOIN user AS u ON o.userId = u.id
         ORDER BY o.id DESC"
    );
    $orders = $stmt->fetchAll();

    foreach ($orders as $order):
    ?>
      <tr>
        <td><?php echo (int)$order['id']; ?></td>
        <td><img width="60" src="<?php echo htmlspecialchars($order['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book cover"></td>
        <td><?php echo htmlspecialchars($order['bookName'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($order['authorName'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars((string)$order['price'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($order['orderDate'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars((string)$order['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td>
          <a href="Aorder.php?delete=1&id=<?php echo (int)$order['id']; ?>"
             class="btn btn-danger"
             onclick="return confirm('Cancel this order and restore the book to stock?');">Cancel Order</a>
        </td>
      </tr>
    <?php endforeach; ?>

    <?php if (empty($orders)): ?>
      <tr><td colspan="10">No orders found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
