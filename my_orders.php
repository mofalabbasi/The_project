<?php
require_once "includes/auth.php";
require_login();
include "includes/connect_db.php";

$userId = (int)$_SESSION['id'];
$stmt = $db->prepare("SELECT id, bookName, authorName, orderDate, price, img, address, phone FROM orders WHERE userId = :userId ORDER BY id DESC");
$stmt->execute([':userId' => $userId]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Books Market - My Orders</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="project text-center pt-5 pb-5">
  <h4 class="text-black fw-bold">My Orders</h4>
  <i class="fa-solid fa-box-open fs-1"></i>
</div>

<div class="container-fluid">
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-info">
        <tr>
          <th>Order Id</th>
          <th>Photo</th>
          <th>Book</th>
          <th>Author</th>
          <th>Price</th>
          <th>Order Date</th>
          <th>Address</th>
          <th>Phone</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td><?php echo (int)$order['id']; ?></td>
          <td><img width="60" height="70" style="object-fit:cover" src="<?php echo htmlspecialchars($order['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book cover"></td>
          <td><?php echo htmlspecialchars($order['bookName'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($order['authorName'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars((string)$order['price'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($order['orderDate'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars((string)$order['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr><td colspan="8">You have no orders yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
