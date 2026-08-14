const imageInput = document.getElementById("image");
const imagePreview = document.getElementById("imagePreview");

imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {
        imagePreview.style.display = "none";
        imagePreview.src = "";
        return;
    }

    const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!allowedTypes.includes(file.type)) {
        alert("Please select a JPG, PNG or WEBP image.");
        this.value = "";
        imagePreview.style.display = "none";
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert("Image size must be less than 5 MB.");
        this.value = "";
        imagePreview.style.display = "none";
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        imagePreview.src = event.target.result;
        imagePreview.style.display = "block";
    };

    reader.readAsDataURL(file);
});