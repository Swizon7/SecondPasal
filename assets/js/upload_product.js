document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("productImages");
    const uploadBox = document.getElementById("photoUploadBox");
    const uploadContent = document.querySelector(".photo-upload-content");
    const previewGrid = document.getElementById("photoPreviewGrid");
    const counter = document.getElementById("photoCounter");

    const maxPhotos = 5;
    const maxSize = 5 * 1024 * 1024;

    let selectedFiles = [];

    function updateInputFiles() {
        const dataTransfer = new DataTransfer();

        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });

        input.files = dataTransfer.files;
    }

    function addFiles(files) {

        Array.from(files).forEach(file => {

            if (selectedFiles.length >= maxPhotos) {
                return;
            }

            if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
                alert(file.name + " is not a JPG, PNG or WEBP image.");
                return;
            }

            if (file.size > maxSize) {
                alert(file.name + " is larger than 5 MB.");
                return;
            }

            const duplicate = selectedFiles.some(existing =>
                existing.name === file.name &&
                existing.size === file.size &&
                existing.lastModified === file.lastModified
            );

            if (!duplicate) {
                selectedFiles.push(file);
            }
        });

        updateInputFiles();
        renderPreviews();
    }

    function renderPreviews() {

        previewGrid.innerHTML = "";

        selectedFiles.forEach((file, index) => {

            const reader = new FileReader();

            reader.onload = function (event) {

                const preview = document.createElement("div");
                preview.className = "photo-preview";

                preview.innerHTML = `
                    <img
                        src="${event.target.result}"
                        alt="Product photo ${index + 1}"
                    >

                    ${
                        index === 0
                        ? `<span class="main-photo-badge">
                              <i class="fa-solid fa-star"></i>
                              Main Photo
                           </span>`
                        : ""
                    }

                    <button
                        type="button"
                        class="remove-photo"
                        data-index="${index}"
                        title="Remove photo"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;

                previewGrid.appendChild(preview);
            };

            reader.readAsDataURL(file);
        });

        counter.textContent =
            `${selectedFiles.length} / ${maxPhotos} photos selected`;

        if (selectedFiles.length >= maxPhotos) {
            uploadBox.classList.add("disabled");
        } else {
            uploadBox.classList.remove("disabled");
        }
    }


    /* ==============================
       FILE SELECT
    ============================== */

    input.addEventListener("change", function () {

        if (this.files.length > 0) {
            addFiles(this.files);
        }

        // Reset input so same file can be selected again
        this.value = "";
    });


    /* ==============================
       REMOVE PHOTO
    ============================== */

    previewGrid.addEventListener("click", function (event) {

        const removeButton = event.target.closest(".remove-photo");

        if (!removeButton) {
            return;
        }

        const index = parseInt(removeButton.dataset.index);

        selectedFiles.splice(index, 1);

        updateInputFiles();
        renderPreviews();
    });


    /* ==============================
       DRAG & DROP
    ============================== */

    ["dragenter", "dragover"].forEach(eventName => {

        uploadBox.addEventListener(eventName, function (event) {

            event.preventDefault();
            event.stopPropagation();

            if (selectedFiles.length < maxPhotos) {
                uploadBox.classList.add("dragging");
            }
        });

    });

    ["dragleave", "drop"].forEach(eventName => {

        uploadBox.addEventListener(eventName, function (event) {

            event.preventDefault();
            event.stopPropagation();

            uploadBox.classList.remove("dragging");
        });

    });


    uploadBox.addEventListener("drop", function (event) {

        if (selectedFiles.length >= maxPhotos) {
            return;
        }

        addFiles(event.dataTransfer.files);
    });


    /* ==============================
       INITIAL STATE
    ============================== */

    counter.textContent = "0 / 5 photos selected";

});