const searchInput = document.getElementById("productSearch");

let searchTimer;

if (searchInput) {

    searchInput.addEventListener("input", function () {

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {

            const searchValue = searchInput.value.trim();

            const url = new URL(window.location.href);

            url.searchParams.set("search", searchValue);

            // Start from page 1 for a new search
            url.searchParams.delete("page");

            // Don't keep an empty search in the URL
            if (searchValue === "") {
                url.searchParams.delete("search");
            }

            window.location.href = url.toString();

        }, 500);

    });

}