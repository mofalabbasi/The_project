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
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["my_form"])) {
      $name = $_POST["name"];
      $author = $_POST["author"];
      $price = $_POST['price'];
      $quantity = $_POST['quantity'];
      $publish_date = $_POST['publish_date'];
      $targetFile = $_POST['per_file'];

      if (empty(trim($name))) {
        $errors["name_err"] = "Name is Required";
      } else {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
          $errors["name_err"] = "only char and white space allowed";
        }
      }
      if (empty(trim($author))) {
        $errors["author_err"] = "Author's Name is Required";
      } else {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $author)) {
          $errors["author_err"] = "only char and white space allowed";
        }
      }

      if (empty(trim($publish_date))) {
        $errors["publishDate_err"] = "Publish Date is Required";
      }else {
        if ( $publish_date > date("Y-m-d")) {
          $errors["publishDate_err"] = "Invaild Format Publish Date";
        }
      }

      if (empty(trim($price))) {
        $errors["price_err"] = "Price is Required";
      } else {
        if (!is_numeric($price) || $price < 1) {
          $errors["price_err"] = "Invaild Format Email";
        }
      }

      if (empty(trim($quantity))) {
        $errors["quantity_err"] = "Quantity is Required";
      } else {
        if (!is_numeric($quantity) || $quantity < 1) {
          $errors["quantity_err"] = "Invaild Format Email";
        }
      }


      if (isset($_FILES['file']) && (!empty($_FILES['file']["name"]) || isset($targetFile))) {
        if (!isset($targetFile)) {
          $targetDir = 'uploads/';
          $targetFile = $targetDir . basename($_FILES['file']['name']); // Path of the target file // Check if file is a valid upload 
          $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
          if (!in_array($fileExtension, $allowedExtensions)) {
            $errors["file_err"] = "'jpg', 'jpeg', 'png', 'gif' the allowed extentions";
          }
        }
      } else {
        $errors["file_err"] = "File is Required";
      }

      if (!isset($errors)) {
        if (!empty($_FILES['file']["name"])) {

          if (isset($targetFile)) {
            if (file_exists($targetFile)) {
              if (!unlink($targetFile)) {
                echo "<div class='alert alert-danger'role='alert'>Image not deleted.</div>";
              }
            }
          }
          $targetDir = 'uploads/'; // Directory where the file will be uploaded 
          $targetFile = $targetDir . basename($_FILES['file']['name']); // Path of the target file // Check if file is a valid upload 
          $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
          // Define allowed file extensions 
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
          if (in_array($fileExtension, $allowedExtensions)) {
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
              echo "Error uploading file.";
            }
          } else {
            echo "'jpg', 'jpeg', 'png', 'gif' the allowed extentions";
          }
        }

        if (is_numeric($_POST["id"]) && $_POST["id"] > 0) {
          $stmt = $db->prepare("Update books set book_name=:book_name,author=:author,publish_date=:publish_date,price=:price,quantity=:quantity,img=:img where book_id=:book_id");
          $stmt->bindParam(':book_id', $_POST['id']);
          $stmt->bindParam(':book_name', $name);
          $stmt->bindParam(':author', $author);
          $stmt->bindParam(':publish_date', $publish_date);
          $stmt->bindParam(':price', $price);
          $stmt->bindParam(':quantity', $quantity);
          $stmt->bindParam(':img', $targetFile);
          $stmt->execute();
          $rowCount = $stmt->rowCount();

          if ($rowCount > 0) {
            echo "<div class='alert alert-success'role='alert'>Updated successfully!</div>";
            $id = "";
            $name = "";
            $author = "";
            $publish_date = "";
            $price = "";
            $quantity = "";
            $targetFile = "";


          } else {
            echo "<div class='alert alert-danger'role='alert'>Book not found or could not be updated.</div>";
          }

        } else {

          $stmt = $db->prepare("INSERT INTO books(book_name, author, publish_date, price, quantity, img) VALUES (:book_name,:author,:publish_date,:price,:quantity,:img)");
          $stmt->bindParam(':book_name', $name);
          $stmt->bindParam(':author', $author);
          $stmt->bindParam(':publish_date', $publish_date);
          $stmt->bindParam(':price', $price);
          $stmt->bindParam(':quantity', $quantity);
          $stmt->bindParam(':img', $targetFile);
          $stmt->execute();
          echo "<div class='alert alert-success'role='alert'>Registration successful!</div>";
          $name = "";
          $author = "";
          $publish_date = "";
          $price = "";
          $quantity = "";
          $targetFile = "";
        }
      }
    }
  } else {
    if (isset($_GET['id']) && isset($_GET['action'])) {
      $id = $_GET['id'];
      if ($_GET['action'] == "edit") {
        $stmt = $db->prepare("select * FROM books WHERE book_id =:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($book) {
          $id = $book['book_id'];
          $name = $book["book_name"];
          $author = $book["author"];
          $publish_date = $book["publish_date"];
          $price = $book["price"];
          $quantity = $book["quantity"];
          $targetFile = $book["img"];
        } else {
          echo "<div class='alert alert-danger'role='alert'Book not found.</div>";
        }
      } else {
        $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$book) {
          echo "<div class='alert alert-danger'role='alert'>Book not found or could not be deleted.</div>";
        } else {
          $targetFile = $book['img'];
          if (isset($targetFile)) {
            if (file_exists($targetFile)) {
              if (unlink($targetFile)) {
                $stmt = $db->prepare("DELETE FROM books WHERE book_id = :id");
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                  echo "<div class='alert alert-success'role='alert'>Book deleted successfully.</div>";
                } else {
                  echo "<div class='alert alert-danger'role='alert'>Book not found or could not be deleted.</div>";
                }
              } else {
                echo "<div class='alert alert-danger'role='alert'>Image not deleted.</div>";
              }
            }
          }
        }
      }
    }
  }

  ?>



  <div class="project text-center pt-5 pb-5">
    <h4 class="text-black fw-bold"> Managment of Books </h4>
    <i class="fa-solid fa-book-open fs-1"></i>
  </div>

  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <fieldset class="border p-3 project">
      <div class="row">
        <div class="col mb-2">
          <div class="form-group">
            <label for="name">Name:</label>
            <input class="form-control" type="text" hidden name="id" value="<?php echo isset($id) ? $id : ""; ?>">
            <input class="form-control" type="text" name="name" value="<?php echo isset($name) ? $name : ""; ?>">
            <span class="text-danger">
              <?php echo isset($errors["name_err"]) ? $errors["name_err"] : ""; ?>
            </span>
          </div>
        </div>
        <div class="col mb-2">
          <div class="form-group">
            <label for="author">Author:</label>
            <input class="form-control" type="text" name="author" value="<?php echo isset($author) ? $author : ""; ?>">
            <span class="text-danger">
              <?php echo isset($errors["author_err"]) ? $errors["author_err"] : ""; ?>
            </span>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col">
            <div class="form-group">
              <label for="username">publish_date</label>
              <input class="form-control" type="date" name="publish_date"
                value="<?php echo isset($publish_date) ? $publish_date : ""; ?>">
              <span class="text-danger">
                <?php echo isset($errors["publishDate_err"]) ? $errors["publishDate_err"] : ""; ?>
              </span>
            </div>
          </div>
        </div>
        <div class="row mb-5">
          <div class="col">
            <div class="form-group">
              <label for="price"> The Price:</label>
              <input class="form-control" type="number" name="price" value="<?php echo isset($price) ? $price : ""; ?>">
              <span class="text-danger">
                <?php echo isset($errors["price_err"]) ? $errors["price_err"] : ""; ?>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label for="quantity"> The Quantity:</label>
              <input class="form-control" type="number" name="quantity"
                value="<?php echo isset($quantity) ? $quantity : ""; ?>">
              <span class="text-danger">
                <?php echo isset($errors["quantity_err"]) ? $errors["quantity_err"] : ""; ?>
              </span>
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label for="file">Photo:</label>
              <input class="form-control" hidden name="per_file"
                value="<?php echo isset($targetFile) ? $targetFile : ""; ?>" />
              <input class="form-control" type="file" name="file" />
              <span class="text-danger">
                <?php echo isset($errors["file_err"]) ? $errors["file_err"] : ""; ?>
              </span>
            </div>
          </div>
        </div>
        <div class="row position-relative">
          <div class="col">
            <div class="pt-4 w-100">
              <input type="submit" value="Save"
                class="btn btn-primary w-25 position-absolute bottom-0 start-50 translate-middle-x" name="my_form">
            </div>
          </div>
        </div>
    </fieldset>
  </form>

  <div class="container-fluid">
    <hr>
    <table class="table table-bordered text-center">
      <thead class="table-info">
        <tr>
          <th>Id</th>
          <th>Photo</th>
          <th>Book_Name</th>
          <th>Author</th>
          <th>Publish_date</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM books");

        $stmt->execute();

        $Books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($Books as $book) {
          echo "<tr>";
          echo "<td>" . $book['book_id'] . "</td>";
          echo "<td> <img class='' width=60px src='" . $book['img'] . "' /> </td>";
          echo "<td>" . $book['book_name'] . "</td>";
          echo "<td>" . $book['author'] . "</td>";
          echo "<td>" . $book['publish_date'] . "</td>";
          echo "<td>" . $book['price'] . "</td>";
          echo "<td>" . $book['quantity'] . "</td>";
          echo "<td> <a href='Abook.php?action=edit&&id=" . $book['book_id'] . "' class='btn btn-info'>Edit</a>";
          echo " <a href='Abook.php?action=delete&&id=" . $book['book_id'] . "' class='btn btn-danger ms-5'>Delete</a></td>";
          echo "</tr>";
        }

        ?>
      </tbody>
    </table>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
  <script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>


</html>