<?php

include("includes/send_mail.php");

if(sendResetEmail(
    "mhrznsuiizon@gmail.com",
    "Test User",
    "http://localhost/test"
))
{
    echo "Email sent successfully!";
}
else
{
    echo "Failed to send email.";
}