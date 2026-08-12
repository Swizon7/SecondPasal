const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

const togglePassword = document.getElementById("togglePassword");
const toggleConfirm = document.getElementById("toggleConfirm");

togglePassword.addEventListener("click", function(){

    if(password.type==="password")
    {
        password.type="text";
        this.classList.replace("fa-eye","fa-eye-slash");
    }
    else
    {
        password.type="password";
        this.classList.replace("fa-eye-slash","fa-eye");
    }

});

toggleConfirm.addEventListener("click", function(){

    if(confirmPassword.type==="password")
    {
        confirmPassword.type="text";
        this.classList.replace("fa-eye","fa-eye-slash");
    }
    else
    {
        confirmPassword.type="password";
        this.classList.replace("fa-eye-slash","fa-eye");
    }

});