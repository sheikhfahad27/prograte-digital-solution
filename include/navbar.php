<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo isset($pageTitle) ? $pageTitle : 'Prograte Digital Solutions'; ?>
    </title>

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">
  


</head>

<body>

<nav class="navbar">

    <a href="index.php" class="logo">

        <span>PROGRATE</span>

        <small>DIGITAL SOLUTIONS</small>

    </a>


    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="about.php">About</a>

        <a href="services.php">Services</a>

        <a href="portfolio.php">Portfolio</a>

        <a href="blog.php">Blog</a>

        <a href="contact.php">Contact</a>

        <a href="contact.php" class="nav-btn">
            Let's Talk
        </a>

    </div>


    <button class="menu-btn" type="button">

        <i class="fa-solid fa-bars"></i>

    </button>

</nav>