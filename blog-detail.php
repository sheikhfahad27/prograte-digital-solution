<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "prograte_db";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed.");
}


/*
|--------------------------------------------------------------------------
| Get Blog Slug
|--------------------------------------------------------------------------
*/

$slug = trim($_GET["slug"] ?? "");

if ($slug === "") {
    header("Location: blog.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Blog From Database
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM blogs
     WHERE slug = ?
     AND status = 'Published'
     LIMIT 1"
);

$stmt->bind_param("s", $slug);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    http_response_code(404);

    die("Blog article not found.");

}

$blog = $result->fetch_assoc();

$stmt->close();


/* Increase blog views */
$viewStmt = $conn->prepare(
    "UPDATE blogs SET views = views + 1 WHERE id = ?"
);

$viewStmt->bind_param("i", $blog["id"]);
$viewStmt->execute();
$viewStmt->close();

/*
|--------------------------------------------------------------------------
| Page SEO
|--------------------------------------------------------------------------
*/

$pageTitle = !empty($blog["seo_title"])
    ? $blog["seo_title"]
    : $blog["title"];

$metaDescription = !empty($blog["meta_description"])
    ? $blog["meta_description"]
    : $blog["excerpt"];

include "include/navbar.php";

?>


<!-- BLOG DETAIL HERO -->

<section class="blog-detail-hero">

    <div class="blog-detail-hero-content">

        <span class="section-tag">

            <?php
            echo htmlspecialchars($blog["category"]);
            ?>

        </span>


        <h1>

            <?php
            echo htmlspecialchars($blog["title"]);
            ?>

        </h1>


        <div class="blog-detail-meta">

            <span>

                <i class="fa-regular fa-calendar"></i>

                <?php
                echo date(
                    "M d, Y",
                    strtotime($blog["created_at"])
                );
                ?>

            </span>


            <span>

                <i class="fa-regular fa-clock"></i>

                5 min read

            </span>

        </div>

    </div>

</section>


<!-- BLOG ARTICLE -->

<section class="blog-detail-section">

    <div class="blog-detail-wrapper">


        <!-- MAIN ARTICLE -->

        <article class="blog-article">


            <?php if (!empty($blog["image"])): ?>

                <div class="blog-detail-image">

                    <img
                        src="<?php echo htmlspecialchars($blog["image"]); ?>"
                        alt="<?php echo htmlspecialchars($blog["title"]); ?>"
                    >

                </div>

            <?php endif; ?>


            <div class="blog-article-content">


                <?php if (!empty($blog["excerpt"])): ?>

                    <p class="blog-lead">

                        <?php
                        echo htmlspecialchars($blog["excerpt"]);
                        ?>

                    </p>

                <?php endif; ?>


                <div class="blog-content-text">

                    <?php

                    /*
                     * Blog content ko line breaks ke saath show karega.
                     * HTML content bhi support karega.
                     */

                    echo $blog["content"];

                    ?>

                </div>


                <!-- SHARE -->

                <div class="blog-share">

                    <span>
                        Share this article
                    </span>


                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); ?>"
                        target="_blank"
                        aria-label="Share on Facebook"
                    >
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>


                    <a
                        href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); ?>"
                        target="_blank"
                        aria-label="Share on LinkedIn"
                    >
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>


                    <a
                        href="https://wa.me/?text=<?php echo urlencode($blog["title"] . " - " . "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); ?>"
                        target="_blank"
                        aria-label="Share on WhatsApp"
                    >
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>

                </div>

            </div>

        </article>


        <!-- SIDEBAR -->

        <aside class="blog-sidebar">


            <div class="sidebar-box">

                <span class="section-tag">
                    HAVE A PROJECT?
                </span>

                <h3>
                    Let's Build Something
                    <span>Great.</span>
                </h3>

                <p>
                    Have a website, software, mobile app or
                    digital marketing project in mind?
                </p>

                <a
                    href="contact.php"
                    class="btn btn-primary"
                >
                    Start a Project

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>


            <div class="sidebar-box">

                <span class="section-tag">
                    OUR SERVICES
                </span>

                <div class="sidebar-links">

                    <a href="service-web.php">
                        Web Development
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="service-mobile.php">
                        Mobile Apps
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="service-software.php">
                        Software Solutions
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="service-ecommerce.php">
                        E-Commerce
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="service-marketing.php">
                        Digital Marketing
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="service-seo.php">
                        SEO
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


        </aside>

    </div>

</section>


<?php

$conn->close();

include "include/footer.php";

?>