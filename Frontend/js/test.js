const PATH_PREFIX = window.location.pathname.includes('/sites/') ? '../..' : '..';

document.addEventListener("DOMContentLoaded", () => {
    // Statische "In den Warenkorb"-Buttons aktivieren (falls vorhanden)
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", () => {
            const productId = button.getAttribute("data-id");
            addToCart(productId);
        });
    });

    updateCartCount();

    // Produktsuche & Anzeige
    if (window.location.pathname.includes("products.html")) {
        const searchInput = document.getElementById("product-search");
        const productList = document.getElementById("product-list");

        const fetchProducts = (query = "") => {
            fetch(`${PATH_PREFIX}/Backend/logic/search_products.php?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data)) {
                        console.warn("Unerwartete Antwort:", data);
                        return;
                    }

                    productList.innerHTML = "";
                    if (data.length === 0 && query.trim() !== "") {
                        productList.innerHTML = "<p>Keine Produkte gefunden.</p>";
                        return;
                    }

                    data.forEach(product => {
                        // Grün: Mache Pfad dynamisch, je nachdem ob product.image schon 'productpictures/...' enthält.
                        const imgPath = product.image.includes('/')
                            ? `${PATH_PREFIX}/Backend/${product.image}` 
                            : `${PATH_PREFIX}/Backend/productpictures/${product.image}`;

                        const col = document.createElement("div");
                        col.className = "col";
                        col.innerHTML = `
                            <div class="card h-100 shadow-sm product-card" draggable="true" data-id="${product.id}">
                                <img src="${imgPath}" class="card-img-top" alt="${product.name}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class='card-title'>${product.name}</h5>
                                    <p class='card-text'>${product.description}</p>
                                    <p class='text-success fw-bold'>${parseFloat(product.price).toFixed(2)} €</p>
                                    <button class="btn btn-success w-100 add-to-cart" data-id="${product.id}">In den Warenkorb</button>
                                </div>
                            </div>
                        `;
                        // Drag’n’Drop-Handling
                        col.querySelector(".product-card").addEventListener("dragstart", e => {
                            e.dataTransfer.setData("text/plain", product.id);
                        });
                        productList.appendChild(col);

                        // „In den Warenkorb“-Button neu binden
                        col.querySelector(".add-to-cart").addEventListener("click", () => {
                            addToCart(product.id);
                        });
                    });
                })
                .catch(error => {
                    console.warn("Produkte konnten nicht geladen werden:", error);
                });
        };

        fetchProducts();

        // Live-Search
        searchInput.addEventListener("input", () => {
            fetchProducts(searchInput.value.trim());
        });
    }
});
