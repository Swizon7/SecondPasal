<?php

session_start();

include("db.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Contact Us | SecondPasal</title>

    <link rel="stylesheet" href="assets/css/contact.css">
    <link rel="stylesheet" href="assets/css/footer.css"
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php include("includes/header.php"); ?>


<section class="contact-page">

    <!-- Page Header -->

    <div class="contact-header">

        <h1>Contact Us</h1>

        <p>
            Have a question, suggestion, or need help?
            We're here to help.
        </p>

    </div>

<?php if(isset($_GET['success'])) { ?>

    <div class="contact-success">
        <i class="fa-solid fa-circle-check"></i>
        Your message has been sent successfully. We'll get back to you soon.
    </div>

<?php } ?>

<?php if(isset($_GET['error'])) { ?>

    <div class="contact-error">

        <?php

        if($_GET['error'] === 'empty') {
            echo "Please fill in all fields.";
        }
        elseif($_GET['error'] === 'email') {
            echo "Please enter a valid email address.";
        }
        else {
            echo "Unable to send your message. Please try again.";
        }

        ?>

    </div>

<?php } ?>

    <div class="contact-container">

        <!-- Contact Information -->

        <div class="contact-info">

            <h2>Get in Touch</h2>

            <p class="intro">
                We'd love to hear from you. Reach out to the SecondPasal team
                and we'll get back to you as soon as possible.
            </p>


            <div class="contact-item">

                <div class="contact-icon">
                    <i class="fa-solid fa-location-dot"></i>
                </div>

                <div>

                    <h3>Location</h3>

                    <p>Nepal</p>

                </div>

            </div>


            <div class="contact-item">

                <div class="contact-icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>

                <div>

                    <h3>Email</h3>

                    <p>support@secondpasal.com</p>

                </div>

            </div>


            <div class="contact-item">

                <div class="contact-icon">
                    <i class="fa-solid fa-phone"></i>
                </div>

                <div>

                    <h3>Phone</h3>

                    <p>+977 9800000000</p>

                </div>

            </div>


            <div class="contact-item">

                <div class="contact-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div>

                    <h3>Working Hours</h3>

                    <p>Sunday - Friday, 9:00 AM - 6:00 PM</p>

                </div>

            </div>


            <div class="social-section">

                <h3>Follow Us</h3>

                <div class="social-links">

                    <a href="#" title="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="#" title="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a href="#" title="X">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>

                    <a href="#" title="LinkedIn">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>

                </div>

            </div>

        </div>


        <!-- Contact Form -->

        <div class="contact-form-card">

            <h2>Send Us a Message</h2>

            <p>
                Fill out the form below and we'll get back to you.
            </p>

            <form action="contact_process.php" method="POST">

                <div class="form-row">

                    <div class="form-group">

                        <label>Full Name</label>

                        <div class="input-field">

                            <i class="fa-solid fa-user"></i>

                            <input
                                type="text"
                                name="name"
                                placeholder="Enter your name"
                                required>

                        </div>

                    </div>


                    <div class="form-group">

                        <label>Email</label>

                        <div class="input-field">

                            <i class="fa-solid fa-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                placeholder="Enter your email"
                                required>

                        </div>

                    </div>

                </div>


                <div class="form-group">

                    <label>Subject</label>

                    <div class="input-field">

                        <i class="fa-solid fa-heading"></i>

                        <input
                            type="text"
                            name="subject"
                            placeholder="Enter subject"
                            required>

                    </div>

                </div>


                <div class="form-group">

                    <label>Message</label>

                    <textarea
                        name="message"
                        rows="7"
                        placeholder="Write your message..."
                        required></textarea>

                </div>


                <button type="submit" class="contact-btn">

                    <i class="fa-solid fa-paper-plane"></i>
                    Send Message

                </button>

            </form>

        </div>

    </div>

</section>


<?php include("includes/footer.php"); ?>


</body>
</html>