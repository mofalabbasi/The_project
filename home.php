<?php
require_once "includes/auth.php";
require_login();
include "includes/connect_db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Books Market</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="landing d-flex justify-content-center align-items-center">
  <div class="text-center text-light">
    <h1>Books Market</h1>
    <p class="fs-6 text-white-50 mb-5">
      Cultural, scientific, human development, self-development, thought, poetry and religious books.
    </p>
    <a class="btn btn-primary rounded-pill" href="books.php" role="button">Browse Books</a>
  </div>
</div>

<div class="stuff pt-5 pb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 text-center mb-4 text-md-start">
        <div class="text">
          <h4>Discover Your Next Book</h4>
          <p class="text-black-50 fs-6">
            Browse the available books, check prices and stock, and add the books you want to your shopping cart.
          </p>
          <p class="text-black-50 fs-6">
            Your orders are submitted through the shopping cart after entering your delivery information.
          </p>
          <a href="books.php" class="btn btn-primary rounded-pill">View Books</a>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <img class="img-fluid w-75 h-75" src="img/1.jpg" alt="Books Market">
      </div>
    </div>
  </div>
</div>

<div class="team text-center pt-5 pb-5">
  <div class="container">
    <h2 class="fw-bold">Book Categories</h2>
    <p class="text-black-50 fs-6 mb-5">
      Explore different types of books and find something suitable for your interests.
    </p>
    <div class="row">
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="box bg-white h-100">
          <img class="img-fluid" src="img/34.jpg" alt="Category">
          <h4 class="p-3">Self Development</h4>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="box bg-white h-100">
          <img class="img-fluid" src="img/33.jpg" alt="Category">
          <h4 class="p-3">Novels</h4>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="box bg-white h-100">
          <img class="img-fluid" src="img/36.jpg" alt="Category">
          <h4 class="p-3">Thought & Culture</h4>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="box bg-white h-100">
          <img class="img-fluid" src="img/8.jpg" alt="Category">
          <h4 class="p-3">Religious Books</h4>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
</body>
</html>
