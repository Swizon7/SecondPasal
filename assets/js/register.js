document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       GET ELEMENTS
       ===================================================== */

    const form = document.getElementById("registerForm");

    const phone = document.getElementById("phone");

    const password = document.getElementById("password");

    const confirmPassword =
        document.getElementById("confirm_password");

    const togglePassword =
        document.getElementById("togglePassword");

    const toggleConfirm =
        document.getElementById("toggleConfirm");

    const strengthBar =
        document.getElementById("strengthBar");

    const strengthText =
        document.getElementById("strengthText");

    const passwordMatch =
        document.getElementById("passwordMatch");


    /* =====================================================
       PHONE NUMBER
       ONLY NUMBERS + MAXIMUM 10 DIGITS
       ===================================================== */

    phone.addEventListener("input", function () {

        // Remove everything except numbers
        this.value = this.value.replace(/[^0-9]/g, "");

        // Maximum 10 digits
        if (this.value.length > 10) {
            this.value = this.value.substring(0, 10);
        }

    });


    /* =====================================================
       PASSWORD SHOW / HIDE
       ===================================================== */

    togglePassword.addEventListener("click", function () {

        if (password.type === "password") {

            password.type = "text";

            this.classList.remove("fa-eye");

            this.classList.add("fa-eye-slash");

            this.title = "Hide password";

        } else {

            password.type = "password";

            this.classList.remove("fa-eye-slash");

            this.classList.add("fa-eye");

            this.title = "Show password";
        }

    });


    /* =====================================================
       CONFIRM PASSWORD SHOW / HIDE
       ===================================================== */

    toggleConfirm.addEventListener("click", function () {

        if (confirmPassword.type === "password") {

            confirmPassword.type = "text";

            this.classList.remove("fa-eye");

            this.classList.add("fa-eye-slash");

            this.title = "Hide password";

        } else {

            confirmPassword.type = "password";

            this.classList.remove("fa-eye-slash");

            this.classList.add("fa-eye");

            this.title = "Show password";
        }

    });


    /* =====================================================
       PASSWORD STRENGTH
       MINIMUM 8 CHARACTERS
       ===================================================== */

    password.addEventListener("input", function () {

        const value = password.value;

        let strength = 0;


        if (value.length >= 8) {
            strength++;
        }

        if (/[A-Z]/.test(value)) {
            strength++;
        }

        if (/[0-9]/.test(value)) {
            strength++;
        }

        if (/[^A-Za-z0-9]/.test(value)) {
            strength++;
        }


        if (value.length === 0) {

            strengthBar.style.width = "0%";

            strengthText.textContent =
                "Password strength";

        }

        else if (value.length < 8) {

            strengthBar.style.width = "20%";

            strengthText.textContent =
                "Minimum 8 characters";

        }

        else if (strength === 1) {

            strengthBar.style.width = "35%";

            strengthText.textContent =
                "Weak";

        }

        else if (strength === 2) {

            strengthBar.style.width = "55%";

            strengthText.textContent =
                "Fair";

        }

        else if (strength === 3) {

            strengthBar.style.width = "75%";

            strengthText.textContent =
                "Good";

        }

        else {

            strengthBar.style.width = "100%";

            strengthText.textContent =
                "Strong";

        }


        checkPasswordMatch();

    });


    /* =====================================================
       CHECK PASSWORD MATCH
       ===================================================== */

    function checkPasswordMatch() {

        if (confirmPassword.value === "") {

            passwordMatch.textContent = "";

            return;
        }


        if (password.value === confirmPassword.value) {

            passwordMatch.textContent =
                "✓ Passwords match";

            passwordMatch.style.color =
                "#2e7d32";

        } else {

            passwordMatch.textContent =
                "✕ Passwords do not match";

            passwordMatch.style.color =
                "#d32f2f";
        }

    }


    confirmPassword.addEventListener(
        "input",
        checkPasswordMatch
    );


    /* =====================================================
       FORM SUBMIT VALIDATION
       ===================================================== */

    form.addEventListener("submit", function (event) {


        /* PHONE VALIDATION */

        if (!/^[0-9]{10}$/.test(phone.value)) {

            event.preventDefault();

            alert(
                "Phone number must contain exactly 10 digits."
            );

            phone.focus();

            return;
        }


        /* PASSWORD VALIDATION */

        if (password.value.length < 8) {

            event.preventDefault();

            alert(
                "Password must contain at least 8 characters."
            );

            password.focus();

            return;
        }


        /* CONFIRM PASSWORD VALIDATION */

        if (password.value !== confirmPassword.value) {

            event.preventDefault();

            alert(
                "Passwords do not match."
            );

            confirmPassword.focus();

            return;
        }

    });

});