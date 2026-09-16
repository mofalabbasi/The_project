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
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["book_form"])) {
      $id = $_POST['id'];

      $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :id");
      $stmt->bindParam(':id', $id);
      $stmt->execute();
      $book = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($book) {
        $bookName = $book['book_name'];
        $authorName = $book['author'];
        $publicationDate = $book['publish_date'];
        $price = $book['price'];
        $targetFile = $book['img'];

        $stmt = $db->prepare("INSERT INTO carts (userId, name, authorName, publicationDate, price, img) VALUES (:userId, :name, :authorName, :publicationDate, :price, :img)");
        $stmt->bindParam(':userId', $_SESSION['id']);
        $stmt->bindParam(':name', $bookName);
        $stmt->bindParam(':authorName', $authorName);
        $stmt->bindParam(':publicationDate', $publicationDate);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':img', $targetFile);
        $stmt->execute();
        echo "<div class='alert alert-success' role='alert'>The book has been successfully added to your cart!</div>";
      } else {
        echo "<div class='alert alert-danger' role='alert'>Failed to add the book to your cart.</div>";
      }
    }
  }




  ?>


  <div class="blog pt-5 pb-5">
    <div class="container">
      <div class="main-title mt-5 mb-5 position-relative text-center">
        <h2>Our Books</h2>
      </div>
      <div class="row ">

        <?php


        $stmt = $db->prepare("SELECT * FROM books");

        $stmt->execute();

        $Books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($Books as $book) {

          echo "<div class='col-md-6 col-lg-3 mt-4'>";
          echo "<div class='card'>";
          echo "<img class='img-fluid card-img-top' src='" . $book['img'] . "'/>";
          echo "<div class='card-body project'>";
          echo "<h5 class='card-title'>" . $book['book_name'] . "</h5>";
          echo "<span class='text-black-50 d-block'>" . $book['author'] . "</span>";
          echo "<span class='text-black-50 d-block'>" . $book['publish_date'] . "</span>";
          echo "<span class='text-black-50 d-block'>" . $book['price'] . "</spanp>";


          echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . " ' method='post'>";
          echo "<input type='text' value='" . $book['book_id'] . "'hidden name='id'>";
          echo "<input type='submit' value='Add to cart' class='btn btn-primary mt-2 w-50 d-block' name='book_form'>";
          echo "</form>";
          echo "</div>";
          echo "</div>";
          echo "</div>";

        }
        ?>




      </div>
    </div>
  </div>






  <?php include "includes/footer.php"; ?>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
</body>

</html>