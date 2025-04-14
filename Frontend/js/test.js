const PATH_PREFIX = window.location.pathname.includes('/sites/') ? '../..' : '..';

document.addEventListener("DOMContentLoaded", () => {
    // "In den Warenkorb"-Buttons aktivieren (statisch im HTML vorhanden)
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", () => {
            const productId = button.getAttribute("data-id");
            addToCart(productId);
        });
    });

    // Warenkorb anzeigen, wenn cart.html aktiv
    if (window.location.pathname.includes("cart.html")) {
        loadCart();
    }

    // Zähler im Warenkorb immer aktuell halten
    updateCartCount();

    // Produktsuche dynamisch laden
    if (window.location.pathname.includes("products.html")) {
        const searchInput = document.getElementById("product-search");
        const productList = document.getElementById("product-list");

        if (searchInput && productList) {
            const fetchProducts = (query = "") => {
                fetch(`${PATH_PREFIX}/Backend/logic/search_products.php?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!Array.isArray(data)) {
                            console.warn("Unerwartete Antwort:", data);
                            return;
                        }

                        // Nur bei aktiver Suche Inhalte überschreiben
                        if (query.trim() !== "") {
                            productList.innerHTML = "";
                        }

                        if (data.length === 0 && query.trim() !== "") {
                            productList.innerHTML = "<p>Keine Produkte gefunden.</p>";
                            return;
                        }

                        if (query.trim() !== "") {
                            data.forEach(product => {
                                const col = document.createElement("div");
                                col.className = "col";
                                col.innerHTML = `
                                    <div class="card h-100">
                                        <img src="/Blattwerk/Blattwerk-/Backend/${product.image}" class="card-img-top" alt="${product.name}">
                                        <div class="card-body">
                                            <h5 class='card-title'>${product.name}</h5>
                                            <p class='card-text'>${product.description}</p>
                                            <p class='text-success fw-bold'>${product.price} €</p>
                                            <button class="btn btn-success w-100 add-to-cart" data-id="${product.id}">In den Warenkorb</button>
                                        </div>
                                    </div>
                                `;
                                productList.appendChild(col);
                            });
                        }
                    })
                    .catch(error => {
                        console.warn("Produkte konnten nicht geladen werden:", error);
                        // Bei Fehler: statischer HTML-Inhalt bleibt erhalten
                    });
            };

            // Initial ohne Suchtext laden
            fetchProducts();

            // Live-Suche aktivieren
            searchInput.addEventListener("input", () => {
                const query = searchInput.value.trim();
                fetchProducts(query);
            });
        }
    }
});