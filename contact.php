<?php

$pageTitle = "Contact | Prograte Digital Solutions";

include "include/navbar.php";

?>

<!-- ================= CONTACT HERO ================= -->

<section class="contact-hero">

    <div class="contact-hero-content">

        <span class="section-tag">GET IN TOUCH</span>

        <h1>
            Let's Build
            <span>Something Great.</span>
        </h1>

        <p>
            Have a website, software, mobile app or digital marketing
            project in mind? Tell us what you need and let's discuss
            your project.
        </p>

    </div>

</section>


<!-- ================= CONTACT SECTION ================= -->

<section class="contact-section">

    <div class="contact-wrapper">


        <!-- LEFT SIDE -->

        <div class="contact-info">

            <span class="section-tag">CONTACT US</span>

            <h2>
                Let's Start a
                <span>Conversation.</span>
            </h2>

            <p class="contact-intro">
                Whether you have a new idea, an existing project that
                needs improvement, or simply want to discuss your
                requirements, we're here to help.
            </p>


            <!-- EMAIL -->

            <div class="contact-item">

                <div class="contact-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

                <div>

                    <small>Email</small>

                    <a href="mailto:hello@prograte.com">
                        hello@prograte.com
                    </a>

                </div>

            </div>


            <!-- PHONE -->

            <div class="contact-item">

                <div class="contact-icon">

                    <i class="fa-solid fa-phone"></i>

                </div>

                <div>

                    <small>Phone</small>

                    <a href="tel:+923000000000">
                        +92 300 0000000
                    </a>

                </div>

            </div>


            <!-- LOCATION -->

            <div class="contact-item">

                <div class="contact-icon">

                    <i class="fa-solid fa-location-dot"></i>

                </div>

                <div>

                    <small>Location</small>

                    <span>Karachi, Pakistan</span>

                </div>

            </div>


            <!-- SOCIAL -->

            <div class="contact-social">

                <a href="#" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="#" aria-label="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a href="#" aria-label="LinkedIn">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>

                <a href="#" aria-label="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>

            </div>

        </div>


        <!-- RIGHT SIDE FORM -->

        <div class="contact-form-box">

            <div class="form-heading">

                <span class="section-tag">PROJECT INQUIRY</span>

                <h3>
                    Tell Us About
                    <span>Your Project.</span>
                </h3>

            </div>


            <form action="send-message.php" method="POST">


                <div class="form-row">

                    <div class="form-group">

                        <label for="name">
                            Your Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >

                    </div>

                </div>


                <div class="form-row">

                    <div class="form-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone"
                        >

                    </div>


                    <div class="form-group">

                        <label for="service">
                            Required Service
                        </label>

                        <select id="service" name="service" required>

                            <option value="">
                                Select a service
                            </option>

                            <option value="Web Development">
                                Web Development
                            </option>

                            <option value="Mobile App Development">
                                Mobile App Development
                            </option>

                            <option value="Software Solutions">
                                Software Solutions
                            </option>

                            <option value="E-Commerce">
                                E-Commerce
                            </option>

                            <option value="Digital Marketing">
                                Digital Marketing
                            </option>

                            <option value="SEO">
                                SEO
                            </option>

                        </select>

                    </div>

                </div>


                <div class="form-group">

                    <label for="budget">
                        Estimated Budget
                    </label>

                    <select id="budget" name="budget">

                        <option value="">
                            Select your budget
                        </option>

                        <option value="Under $500">
                            Under $500
                        </option>

                        <option value="$500 - $1000">
                            $500 - $1000
                        </option>

                        <option value="$1000 - $2500">
                            $1000 - $2500
                        </option>

                        <option value="$2500+">
                            $2500+
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="message">
                        Project Details
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="Tell us about your project..."
                        required
                    ></textarea>

                </div>


                <button type="submit" class="form-submit">

                    Send Project Inquiry

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>

        </div>

    </div>

</section>


<!-- ================= WHATSAPP CTA ================= -->

<section class="contact-cta">

    <span class="section-tag">QUICK CONTACT</span>

    <h2>
        Prefer
        <span>WhatsApp?</span>
    </h2>

    <p>
        Send us a message directly and let's discuss your
        project requirements.
    </p>

    <a
        href="https://wa.me/923000000000"
        target="_blank"
        class="whatsapp-btn"
    >

        <i class="fa-brands fa-whatsapp"></i>

        Chat on WhatsApp

    </a>

</section>


<?php include "include/footer.php"; ?>