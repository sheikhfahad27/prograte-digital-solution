<?php

$pageTitle = "Blog | Prograte Digital Solutions";

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

$conn->set_charset("utf8mb4");


/* =========================
   GET PUBLISHED BLOGS
========================= */

$sql = "
    SELECT *
    FROM blogs
    WHERE status = 'Published'
    ORDER BY created_at DESC
";

$result = $conn->query($sql);


include "include/navbar.php";

?>


<!-- =========================
     BLOG HERO
========================= -->

<section class="blog-hero">

    <div class="blog-hero-content">

        <span class="section-tag">
            OUR BLOG
        </span>

        <h1>
            Insights for
            <span>Digital Growth.</span>
        </h1>

        <p>
            Explore practical insights, trends, strategies and ideas
            about web development, software, marketing, SEO and
            digital transformation.
        </p>

    </div>

</section>



<!-- =========================
     BLOG SECTION
========================= -->

<section class="blog-section">

    <div class="section-heading">

        <span class="section-tag">
            LATEST ARTICLES
        </span>

        <h2>
            Learn. Build.
            <span>Grow.</span>
        </h2>

        <p>
            Helpful articles and insights to help businesses make
            better digital decisions.
        </p>

    </div>



    <div class="blog-grid">


        <?php if ($result && $result->num_rows > 0): ?>


            <?php while ($blog = $result->fetch_assoc()): ?>


                <article class="blog-card">


                    <!-- =========================
                         BLOG IMAGE
                    ========================= -->

                    <a
                        href="blog-detail.php?slug=<?php echo urlencode($blog["slug"]); ?>"
                        class="blog-image-link"
                    >

                        <div class="blog-image">


                            <?php if (!empty($blog["image"])): ?>


                                <img
                                    src="<?php echo htmlspecialchars($blog["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($blog["title"]); ?>"
                                >


                            <?php else: ?>


                                <div class="blog-no-image">

                                    <i class="fa-solid fa-newspaper"></i>

                                </div>


                            <?php endif; ?>


                            <!-- CATEGORY -->

                            <span class="blog-category">

                                <?php
                                echo htmlspecialchars(
                                    $blog["category"]
                                );
                                ?>

                            </span>


                        </div>

                    </a>



                    <!-- =========================
                         BLOG CONTENT
                    ========================= -->

                    <div class="blog-content">


                        <!-- META -->

                        <div class="blog-meta">


                            <span>

                                <i class="fa-regular fa-calendar"></i>

                                <?php

                                echo date(
                                    "M d, Y",
                                    strtotime(
                                        $blog["created_at"]
                                    )
                                );

                                ?>

                            </span>



                            <span>

                                <i class="fa-regular fa-clock"></i>

                                5 min read

                            </span>


                        </div>



                        <!-- TITLE -->

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $blog["title"]
                            );

                            ?>

                        </h3>



                        <!-- EXCERPT -->

                        <p>


                            <?php


                            $excerpt =
                                $blog["excerpt"];


                            /*
                             * If excerpt is empty,
                             * use blog content.
                             */

                            if (
                                empty($excerpt)
                            ) {

                                $excerpt =
                                    strip_tags(
                                        $blog["content"]
                                    );

                            }


                            $excerpt =
                                trim($excerpt);


                            /*
                             * Limit excerpt
                             * to 150 characters.
                             */

                            echo htmlspecialchars(
                                mb_substr(
                                    $excerpt,
                                    0,
                                    150
                                )
                            );


                            if (
                                mb_strlen(
                                    $excerpt
                                ) > 150
                            ) {

                                echo "...";

                            }


                            ?>

                        </p>



                        <!-- =========================
                             READ ARTICLE
                        ========================= -->

                        <a
                            href="blog-detail.php?slug=<?php echo urlencode($blog["slug"]); ?>"
                            class="blog-read-more"
                        >

                            Read Article

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>


                    </div>


                </article>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =========================
                 NO BLOGS
            ========================= -->

            <div class="no-blogs">

                <i class="fa-solid fa-newspaper"></i>

                <h3>
                    No Articles Published Yet
                </h3>

                <p>
                    New articles will appear here once they are published.
                </p>

            </div>


        <?php endif; ?>


    </div>

</section>



<!-- =========================
     CTA
========================= -->

<section class="blog-cta">


    <span class="section-tag">
        HAVE A PROJECT?
    </span>



    <h2>

        Let's Build Your

        <span>
            Digital Future.
        </span>

    </h2>



    <p>

        Have an idea or project in mind?

        Let's discuss how we can help.

    </p>



    <a
        href="contact.php"
        class="btn btn-primary"
    >

        Start a Project

        <i class="fa-solid fa-arrow-right"></i>

    </a>


</section>



<?php


$conn->close();


include "include/footer.php";


?>
