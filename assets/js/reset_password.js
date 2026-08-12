const password=document.getElementById("password");
const confirm=document.getElementById("confirm_password");

document.getElementById("togglePassword").onclick=function(){

password.type=password.type==="password"?"text":"password";

this.classList.toggle("fa-eye");
this.classList.toggle("fa-eye-slash");

}

document.getElementById("toggleConfirm").onclick=function(){

confirm.type=confirm.type==="password"?"text":"password";

this.classList.toggle("fa-eye");
this.classList.toggle("fa-eye-slash");

}