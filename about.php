<?php
require_once "includes/auth.php";
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Books Market - About Us</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/all.min.css" />
    <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php
if (($_SESSION['role'] ?? '') === 'admin') {
    include "includes/adminHeader.php";
} else {
    include "includes/header.php";
}
?>

<div class="stuff pt-5 m-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center mb-4 text-md-start">
                <div class="text">
                    <h4>About Us</h4>
                    <p class="text-black-50 fs-6">We provide a selection of books and novels through our online book market.</p>
                    <p class="text-black-50 fs-6">Customers can browse the available books, add them to a cart, and submit an order through the website.</p>
                    <p class="text-black-50 fs-6">Our website is designed to make browsing and ordering books simple and convenient.</p>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img class="img-fluid w-75 mb-5 rounded float-end" src="img/7.jpg" alt="Books Market" />
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
</body>
</html>
