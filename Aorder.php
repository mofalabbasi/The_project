<?php
session_start();
if(!isset($_SESSION['id']))
{
  header('refresh:0;index.php');
}
?>
<html>

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
  include "includes/adminHeader.php";

  if (isset($_GET['id']) && isset($_GET['delete'])) {
    $id = $_GET['id'];

    $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $bookName = $order['bookName'];


    $stmt = $db->prepare("SELECT * FROM books WHERE book_name = :book_name");
    $stmt->bindParam(':book_name', $bookName);
    $stmt->execute();
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    $quantity = $book['quantity'];
    $quantity -= 1;
    if ($quantity == 0) {
      $stmt = $db->prepare("Delete from books where book_name= :book_name");
      $stmt->bindParam(':book_name', $bookName);
      $stmt->execute();
      $rowCount = $stmt->rowCount();
      if ($rowCount > 0) {
        echo "<div class='alert alert-success'role='alert'>This is the last book \n The book has been deleted from the system.</div>";
      }

    } else {
      $stmt = $db->prepare("Update books set quantity=:quantity where book_name=:book_name");
      $stmt->bindParam(':book_name', $bookName);
      $stmt->bindParam(':quantity', $quantity);
      $stmt->execute();
      $rowCount = $stmt->rowCount();
      if ($rowCount > 0) {
        echo "<div class='alert alert-success'role='alert'>The quantity has been updated for " . $bookName . "'s book!</div>";
      }
    }

    $stmt = $db->prepare("Delete from orders where id= :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
      echo "<div class='alert alert-success'role='alert'>Order delete successfully.</div>";
    } else {
      echo "<div class='alert alert-danger'role='alert'>Order not found.</div>";
    }
  }
  ?>


  <div class="project text-center pt-5 pb-5">
    <h4 class="text-black fw-bold"> Managment of Orders </h4>
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

      <?php
      $stmt = $db->prepare("SELECT o.id, o.address, o.phone, o.bookName, o.authorName, o.orderDate, o.price, o.img, u.name FROM orders AS o JOIN user AS u ON o.userId = u.id;");
      $stmt->execute();
      $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>" . $order['id'] . "</td>";
        echo "<td> <img class='' width=60px src='" . $order['img'] . "' /> </td>";
        echo "<td>" . $order['bookName'] . "</td>";
        echo "<td>" . $order['authorName'] . "</td>";
        echo "<td>" . $order['price'] . "</td>";
        echo "<td>" . $order['orderDate'] . "</td>";
        echo "<td>" . $order['name'] . "</td>";
        echo "<td>" . $order['address'] . "</td>";
        echo "<td>" . $order['phone'] . "</td>";
        echo "<td> <a href='Aorder.php?delete=1&&id=" . $order['id'] . "' class='btn btn-danger'>Delete</a></td>";
        echo "</tr>";
      }
      ?>
    </table>
  </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
  <script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>