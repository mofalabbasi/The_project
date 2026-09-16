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
    if ($_SESSION['role'] == 'admin') {
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
                        <p class="text-black-50 fs-6"> We provide you with various books and novels at the best prices.
                        </p>
                        <p class="text-black-50 fs-6"> Order now through the website everything you need to receive it
                            within a maximum of 24 hours.
                        </p>
                        <p class="text-black-50 fs-6"> What distinguishes us from other stores : </p>
                        <p class="text-black-50 fs-6"> - Our delivery is fast, safe and easy </p>
                        <p class="text-black-50 fs-6"> - Delivery service is available to all governorates</p>
                    </div>
                </div>
                <div class="col-lg-6 text-center">

                    <img class="img-fluid w-75 mb-5 rounded float-end" src="img/7.jpg" alt="" />


                </div>
            </div>
        </div>
    </div>
    <?php include "includes/footer.php"; ?>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/all.min.js"></script>
</body>

</html>