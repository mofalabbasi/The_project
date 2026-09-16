<?php
require_once "includes/auth.php";
require_admin();
include "includes/connect_db.php";

$errors = [];
$successMessage = '';
$id = 0;
$name = '';
$author = '';
$publish_date = '';
$price = '';
$quantity = '';
$targetFile = '';

function uploadBookImage(string $oldFile = ''): string
{
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unable to upload the selected image.');
    }

    if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The image must be 5 MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, JPEG, PNG and GIF images are allowed.');
    }

    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Upload directory is not available.');
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $relativePath = 'uploads/' . $fileName;
    $absolutePath = $dir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Error uploading file.');
    }

    if ($oldFile !== '' && strpos($oldFile, 'uploads/') === 0) {
        $oldPath = __DIR__ . DIRECTORY_SEPARATOR . $oldFile;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $relativePath;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $bookId = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($bookId === false) {
        $errors['general'] = 'Invalid book ID.';
    } elseif ($action === 'edit') {
        $stmt = $db->prepare("SELECT * FROM books WHERE book_id = :id LIMIT 1");
        $stmt->execute([':id' => $bookId]);
        $book = $stmt->fetch();

        if ($book) {
            $id = (int)$book['book_id'];
            $name = $book['book_name'];
            $author = $book['author'];
            $publish_date = $book['publish_date'];
            $price = $book['price'];
            $quantity = $book['quantity'];
            $targetFile = $book['img'];
        } else {
            $errors['general'] = 'Book not found.';
        }
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("SELECT img FROM books WHERE book_id = :id LIMIT 1");
        $stmt->execute([':id' => $bookId]);
        $book = $stmt->fetch();

        if (!$book) {
            $errors['general'] = 'Book not found.';
        } else {
            $stmt = $db->prepare("DELETE FROM books WHERE book_id = :id");
            try {
                $stmt->execute([':id' => $bookId]);
                if ($stmt->rowCount() === 1) {
                    if (!empty($book['img']) && strpos($book['img'], 'uploads/') === 0) {
                        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . $book['img'];
                        if (is_file($imagePath)) {
                            @unlink($imagePath);
                        }
                    }
                    $successMessage = 'Book deleted successfully.';
                } else {
                    $errors['general'] = 'Book could not be deleted.';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'This book cannot be deleted because related records exist.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['my_form'])) {
    $postedId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $id = ($postedId && $postedId > 0) ? $postedId : 0;
    $name = trim($_POST['name'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publish_date = trim($_POST['publish_date'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $oldImage = '';

    if ($id > 0) {
        $stmt = $db->prepare("SELECT img FROM books WHERE book_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            $errors['general'] = 'Book not found.';
        } else {
            $oldImage = $existing['img'];
            $targetFile = $oldImage;
        }
    }

    if ($name === '') {
        $errors['name_err'] = 'Name is Required';
    }
    if ($author === '') {
        $errors['author_err'] = "Author's Name is Required";
    }
    if ($publish_date === '') {
        $errors['publishDate_err'] = 'Publish Date is Required';
    } elseif ($publish_date > date('Y-m-d')) {
        $errors['publishDate_err'] = 'Publish date cannot be in the future.';
    }
    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors['price_err'] = 'Price must be greater than 0.';
    }
    if ($quantity === '' || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int)$quantity < 0) {
        $errors['quantity_err'] = 'Quantity must be 0 or greater.';
    }

    try {
        if (empty($errors)) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $targetFile = uploadBookImage($oldImage);
            } elseif ($id === 0) {
                $errors['file_err'] = 'File is Required';
            }

            if (empty($errors)) {
                if ($id > 0) {
                    $stmt = $db->prepare(
                        "UPDATE books SET book_name=:name, author=:author, publish_date=:publish_date,
                         price=:price, quantity=:quantity, img=:img WHERE book_id=:id"
                    );
                    $stmt->execute([
                        ':id' => $id,
                        ':name' => $name,
                        ':author' => $author,
                        ':publish_date' => $publish_date,
                        ':price' => (float)$price,
                        ':quantity' => (int)$quantity,
                        ':img' => $targetFile
                    ]);
                    $successMessage = 'Book updated successfully.';
                } else {
                    $stmt = $db->prepare(
                        "INSERT INTO books (book_name, author, publish_date, price, quantity, img)
                         VALUES (:name, :author, :publish_date, :price, :quantity, :img)"
                    );
                    $stmt->execute([
                        ':name' => $name,
                        ':author' => $author,
                        ':publish_date' => $publish_date,
                        ':price' => (float)$price,
                        ':quantity' => (int)$quantity,
                        ':img' => $targetFile
                    ]);
                    $successMessage = 'Book added successfully.';
                }

                $id = 0;
                $name = '';
                $author = '';
                $publish_date = '';
                $price = '';
                $quantity = '';
                $targetFile = '';
            }
        }
    } catch (Throwable $e) {
        if ($id === 0 && $targetFile !== '' && strpos($targetFile, 'uploads/') === 0) {
            @unlink(__DIR__ . DIRECTORY_SEPARATOR . $targetFile);
        }
        $errors['general'] = 'Unable to save the book right now.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Market Books - Books</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php include "includes/adminHeader.php"; ?>

<?php if ($successMessage !== ''): ?><div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
<?php if (isset($errors['general'])): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

<div class="project text-center pt-5 pb-5">
  <h4 class="text-black fw-bold">Management of Books</h4>
  <i class="fa-solid fa-book-open fs-1"></i>
</div>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data">
  <fieldset class="border p-3 project">
    <div class="row">
      <div class="col mb-2"><label>Name:</label><input class="form-control" type="hidden" name="id" value="<?php echo (int)$id; ?>"><input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"><span class="text-danger"><?php echo htmlspecialchars($errors['name_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div class="col mb-2"><label>Author:</label><input class="form-control" type="text" name="author" value="<?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>"><span class="text-danger"><?php echo htmlspecialchars($errors['author_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div class="col mb-2"><label>Publish date:</label><input class="form-control" type="date" name="publish_date" value="<?php echo htmlspecialchars($publish_date, ENT_QUOTES, 'UTF-8'); ?>"><span class="text-danger"><?php echo htmlspecialchars($errors['publishDate_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
    </div>
    <div class="row mb-4">
      <div class="col"><label>Price:</label><input class="form-control" type="number" step="0.01" name="price" min="0.01" value="<?php echo htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8'); ?>"><span class="text-danger"><?php echo htmlspecialchars($errors['price_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div class="col"><label>Quantity:</label><input class="form-control" type="number" name="quantity" min="0" value="<?php echo htmlspecialchars((string)$quantity, ENT_QUOTES, 'UTF-8'); ?>"><span class="text-danger"><?php echo htmlspecialchars($errors['quantity_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
      <div class="col"><label>Photo:</label><input class="form-control" type="file" name="file" accept="image/jpeg,image/png,image/gif"><span class="text-danger"><?php echo htmlspecialchars($errors['file_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></div>
    </div>
    <input type="submit" value="Save" class="btn btn-primary w-25 d-block mx-auto" name="my_form">
  </fieldset>
</form>

<div class="container-fluid mt-4">
  <table class="table table-bordered text-center">
    <thead class="table-info"><tr><th>Id</th><th>Photo</th><th>Book Name</th><th>Author</th><th>Publish Date</th><th>Price</th><th>Quantity</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $books = $db->query("SELECT * FROM books ORDER BY book_id DESC")->fetchAll();
    foreach ($books as $book):
    ?>
      <tr>
        <td><?php echo (int)$book['book_id']; ?></td>
        <td><img width="60" src="<?php echo htmlspecialchars($book['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="Book cover"></td>
        <td><?php echo htmlspecialchars($book['book_name'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars($book['publish_date'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars((string)$book['price'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo (int)$book['quantity']; ?></td>
        <td>
          <a href="Abook.php?action=edit&id=<?php echo (int)$book['book_id']; ?>" class="btn btn-info">Edit</a>
          <a href="Abook.php?action=delete&id=<?php echo (int)$book['book_id']; ?>" class="btn btn-danger ms-2" onclick="return confirm('Delete this book?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($books)): ?><tr><td colspan="8">No books found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
