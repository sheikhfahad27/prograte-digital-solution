<?php
$pageTitle = "About Us | Prograte Digital Solutions";
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $pageTitle; ?></title>

    <meta name="description"
          content="Learn more about Prograte Digital Solutions, our mission, vision and approach to digital transformation.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php include 'include/navbar.php'; ?>


<!-- PAGE HERO -->

<section class="page-hero">

    <div>

        <span class="small-title">
            ABOUT PROGRATE
        </span>

        <h1>
            Building The
            <span>Digital Future</span>
        </h1>

        <p>
            We create technology solutions that help businesses
            grow, connect and compete in a digital world.
        </p>

    </div>

</section>


<!-- ABOUT INTRO -->

<section class="section about-page">

    <div class="about-page-content">

        <span class="small-title">
            WHO WE ARE
        </span>

        <h2>
            Technology,
            <span>Creativity</span>
            & Strategy
        </h2>

        <p>
            Prograte Digital Solutions is a technology-focused
            digital solutions company dedicated to helping
            businesses establish and grow their digital presence.
        </p>

        <p>
            We combine modern technologies, creative thinking
            and business strategy to develop websites, software,
            applications and digital marketing solutions.
        </p>

        <p>
            Our approach is simple: understand the problem,
            build the right solution and deliver technology
            that creates real value.
        </p>

    </div>


    <div class="about-highlight">

        <div class="highlight-card">

            <i class="fa-solid fa-rocket"></i>

            <h3>
                Innovation First
            </h3>

            <p>
                We continuously explore better technologies
                and smarter ways to solve business problems.
            </p>

        </div>

    </div>

</section>


<!-- MISSION VISION -->

<section class="mission-section">

    <div class="mission-card">

        <div class="mission-icon">
            <i class="fa-solid fa-bullseye"></i>
        </div>

        <span>OUR MISSION</span>

        <h2>
            Create Meaningful
            Digital Solutions
        </h2>

        <p>
            Our mission is to make powerful technology accessible
            to businesses through reliable, scalable and practical
            digital solutions.
        </p>

    </div>


    <div class="mission-card">

        <div class="mission-icon">
            <i class="fa-solid fa-eye"></i>
        </div>

        <span>OUR VISION</span>

        <h2>
            A Smarter
            Digital World
        </h2>

        <p>
            We envision a future where businesses use technology
            to work smarter, serve customers better and achieve
            sustainable growth.
        </p>

    </div>

</section>


<!-- VALUES -->

<section class="section">

    <div class="section-heading">

        <span class="small-title">
            OUR VALUES
        </span>

        <h2>
            What
            <span>Drives Us</span>
        </h2>

        <p>
            The principles behind every project we deliver.
        </p>

    </div>


    <div class="values-grid">

        <div class="value-card">

            <i class="fa-solid fa-lightbulb"></i>

            <h3>
                Innovation
            </h3>

            <p>
                We look for smarter and more effective
                ways to solve problems.
            </p>

        </div>


        <div class="value-card">

            <i class="fa-solid fa-medal"></i>

            <h3>
                Quality
            </h3>

            <p>
                We focus on reliable products and
                high-quality digital experiences.
            </p>

        </div>


        <div class="value-card">

            <i class="fa-solid fa-handshake"></i>

            <h3>
                Trust
            </h3>

            <p>
                We believe strong relationships are
                built through transparency and communication.
            </p>

        </div>


        <div class="value-card">

            <i class="fa-solid fa-chart-line"></i>

            <h3>
                Growth
            </h3>

            <p>
                Our solutions are designed to help
                businesses move forward.
            </p>

        </div>

    </div>

</section>


<!-- CTA -->

<section class="cta">

    <div>

        <span class="small-title">
            WORK WITH US
        </span>

        <h2>
            Let's Turn Your
            <span>Idea Into Reality.</span>
        </h2>

        <p>
            Have a project in mind? Let's discuss it.
        </p>

    </div>

    <a href="contact.php" class="btn btn-gold">
        Start A Project
        <i class="fa-solid fa-arrow-right"></i>
    </a>

</section>


<?php include 'include/footer.php'; ?>


<script src="app.js"></script>

</body>
</html>