document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("userSearch");
    const searchForm = document.getElementById("userSearchForm");
    const clearButton = document.getElementById("clearSearch");
    const searchLoading = document.getElementById("searchLoading");
    const searchBox = document.querySelector(".user-search");

    if (!searchInput || !searchForm) {
        return;
    }


    /* ==========================================
       DEBOUNCE TIMER
    ========================================== */

    let debounceTimer;


    /* ==========================================
       SHOW / HIDE CLEAR BUTTON
    ========================================== */

    function updateClearButton() {

        if (searchInput.value.trim() !== "") {

            clearButton.style.display = "flex";

        } else {

            clearButton.style.display = "none";

        }

    }


    updateClearButton();


    /* ==========================================
       SEARCH FUNCTION
    ========================================== */

    function performSearch() {

        const value = searchInput.value.trim();

        searchLoading.style.display = "block";

        searchBox.classList.add("searching");


        /*
         * Small delay so loading effect is visible.
         */

        setTimeout(function () {

            if (value === "") {

                window.location.href = "users.php";

            } else {

                window.location.href =
                    "users.php?search=" +
                    encodeURIComponent(value);

            }

        }, 150);

    }


    /* ==========================================
       DEBOUNCED SEARCH
    ========================================== */

    searchInput.addEventListener("input", function () {

        updateClearButton();

        clearTimeout(debounceTimer);


        const value = searchInput.value.trim();


        /*
         * If search is empty,
         * return to all users.
         */

        if (value === "") {

            searchLoading.style.display = "none";

            searchBox.classList.remove("searching");

            return;

        }


        /*
         * Show searching effect.
         */

        searchLoading.style.display = "block";

        searchBox.classList.add("searching");


        /*
         * Wait 500ms after user stops typing.
         */

        debounceTimer = setTimeout(function () {

            performSearch();

        }, 500);

    });


    /* ==========================================
       CLEAR SEARCH
    ========================================== */

    clearButton.addEventListener("click", function () {

        clearTimeout(debounceTimer);

        searchInput.value = "";

        updateClearButton();

        searchLoading.style.display = "none";

        searchBox.classList.remove("searching");


        window.location.href = "users.php";

    });


    /* ==========================================
       FORM SUBMIT
       Manual Search Button
    ========================================== */

    searchForm.addEventListener("submit", function (event) {

        event.preventDefault();

        clearTimeout(debounceTimer);

        performSearch();

    });


    /* ==========================================
       ESCAPE KEY
    ========================================== */

    searchInput.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            clearButton.click();

        }

    });

});