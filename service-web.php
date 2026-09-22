<?php
$pageTitle = "Web Development | Prograte Digital Solutions";
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $pageTitle; ?></title>

    <meta name="description"
          content="Professional website and web application development services by Prograte Digital Solutions.">

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


<!-- SERVICE HERO -->

<section class="service-hero">

    <div class="service-hero-content">

        <span class="small-title">
            OUR SERVICES / WEB DEVELOPMENT
        </span>

        <h1>
            Websites That
            <span>Work For Your Business.</span>
        </h1>

        <p>
            We build modern, responsive and high-performance
            websites that help businesses establish a strong
            digital presence.
        </p>

        <a href="contact.php" class="btn btn-gold">
            Start Your Project
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="service-hero-icon">

        <div>
            <i class="fa-solid fa-laptop-code"></i>
        </div>

    </div>

</section>


<!-- OVERVIEW -->

<section class="section service-overview">

    <div>

        <span class="small-title">
            WHAT WE DO
        </span>

        <h2>
            Complete
            <span>Web Solutions</span>
        </h2>

    </div>

    <div>

        <p>
            Your website is often the first interaction customers
            have with your business. We create digital experiences
            that are fast, responsive and built around your goals.
        </p>

        <p>
            From simple business websites to complex web
            applications, our development approach focuses on
            performance, usability, scalability and maintainability.
        </p>

    </div>

</section>


<!-- FEATURES -->

<section class="section service-features">

    <div class="section-heading">

        <span class="small-title">
            OUR CAPABILITIES
        </span>

        <h2>
            Everything You Need
            <span>Online</span>
        </h2>

    </div>


    <div class="features-grid">

        <div class="feature-card">

            <i class="fa-solid fa-building"></i>

            <h3>
                Business Websites
            </h3>

            <p>
                Professional websites designed to showcase
                your company, services and brand.
            </p>

        </div>


        <div class="feature-card">

            <i class="fa-solid fa-code"></i>

            <h3>
                Web Applications
            </h3>

            <p>
                Custom web applications built around your
                specific business requirements.
            </p>

        </div>


        <div class="feature-card">

            <i class="fa-solid fa-mobile-screen"></i>

            <h3>
                Responsive Development
            </h3>

            <p>
                Websites that provide a smooth experience
                across mobile, tablet and desktop.
            </p>

        </div>


        <div class="feature-card">

            <i class="fa-solid fa-plug"></i>

            <h3>
                API Integration
            </h3>

            <p>
                Connect your website with external services,
                APIs and business systems.
            </p>

        </div>


        <div class="feature-card">

            <i class="fa-solid fa-gauge-high"></i>

            <h3>
                Performance
            </h3>

            <p>
                Optimized websites focused on speed,
                performance and efficient loading.
            </p>

        </div>


        <div class="feature-card">

            <i class="fa-solid fa-shield-halved"></i>

            <h3>
                Security
            </h3>

            <p>
                Development practices focused on creating
                reliable and secure digital products.
            </p>

        </div>

    </div>

</section>


<!-- PROCESS -->

<section class="process-section">

    <div class="section-heading">

        <span class="small-title">
            OUR PROCESS
        </span>

        <h2>
            From Idea
            <span>To Launch</span>
        </h2>

    </div>


    <div class="process-grid">

        <div class="process-card">

            <span>01</span>

            <i class="fa-solid fa-comments"></i>

            <h3>
                Discovery
            </h3>

            <p>
                We understand your business, audience
                and project requirements.
            </p>

        </div>


        <div class="process-card">

            <span>02</span>

            <i class="fa-solid fa-pen-ruler"></i>

            <h3>
                Planning
            </h3>

            <p>
                We define the structure, features and
                technology required for your project.
            </p>

        </div>


        <div class="process-card">

            <span>03</span>

            <i class="fa-solid fa-code"></i>

            <h3>
                Development
            </h3>

            <p>
                Our team turns the plan into a functional
                digital product.
            </p>

        </div>


        <div class="process-card">

            <span>04</span>

            <i class="fa-solid fa-rocket"></i>

            <h3>
                Launch
            </h3>

            <p>
                After testing and final improvements,
                your website goes live.
            </p>

        </div>

    </div>

</section>


<!-- CTA -->

<section class="cta">

    <div>

        <span class="small-title">
            READY TO BUILD?
        </span>

        <h2>
            Let's Build Your
            <span>Next Website.</span>
        </h2>

        <p>
            Tell us about your project and let's get started.
        </p>

    </div>

    <a href="contact.php" class="btn btn-gold">
        Get Started
        <i class="fa-solid fa-arrow-right"></i>
    </a>

</section>


<?php include 'include/footer.php'; ?>

<script src="app.js"></script>

</body>
</html>