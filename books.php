<?php
require_once "includes/auth.php";
require_login();
include "includes/connect_db.php";

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_form'])) {
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($id === false) {
        $errorMessage = 'Invalid book ID.';
    } else {
        $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $book = $stmt->fetch();

        if (!$book) {
            $errorMessage = 'Book not found.';
        } elseif ((int)$book['quantity'] < 1) {
            $errorMessage = 'This book is currently out of stock.';
        } else {
            $stmt = $db->prepare(
                "INSERT INTO carts (userId, name, authorName, publicationDate, price, img)
                 VALUES (:userId, :name, :authorName, :publicationDate, :price, :img)"
            );
            $stmt->execute([
                ':userId' => (int)$_SESSION['id'],
                ':name' => $book['book_name'],
                ':authorName' => $book['author'],
                ':publicationDate' => $book['publish_date'],
                ':price' => $book['price'],
                ':img' => $book['img']
            ]);
            $successMessage = 'The book has been successfully added to your cart!';
        }
    }
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
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap" rel="stylesheet" />
</head>
<body>

<?php include "includes/header.php"; ?>

<?php if ($successMessage !== ''): ?>
  <div class="alert alert-success" role="alert">
    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
  <div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

<div class="blog pt-5 pb-5">
  <div class="container">
    <div class="main-title mt-5 mb-5 position-relative text-center">
      <h2>Our Books</h2>
    </div>
    <div class="row">
      <?php
      $stmt = $db->query("SELECT * FROM books ORDER BY book_id DESC");
      $books = $stmt->fetchAll();

      foreach ($books as $book):
      ?>
        <div class="col-md-6 col-lg-3 mt-4">
          <div class="card h-100">
            <img class="img-fluid card-img-top" src="<?php echo htmlspecialchars($book['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book cover">
            <div class="card-body project">
              <h5 class="card-title"><?php echo htmlspecialchars($book['book_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
              <span class="text-black-50 d-block"><?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-black-50 d-block"><?php echo htmlspecialchars($book['publish_date'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-black-50 d-block">Price: <?php echo htmlspecialchars((string)$book['price'], ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="text-black-50 d-block">Available: <?php echo (int)$book['quantity']; ?></span>

              <?php if ((int)$book['quantity'] > 0): ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                  <input type="hidden" value="<?php echo (int)$book['book_id']; ?>" name="id">
                  <input type="submit" value="Add to cart" class="btn btn-primary mt-2 w-50 d-block" name="book_form">
                </form>
              <?php else: ?>
                <button class="btn btn-secondary mt-2 w-50" type="button" disabled>Out of stock</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
</body>
</html>
