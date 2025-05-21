const PATH_PREFIX = window.location.pathname.includes('/sites/') ? '../..' : '..';

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", () => {
            const productId = button.getAttribute("data-id");
            addToCart(productId);
        });
    });

    updateCartCount();

    if (window.location.pathname.includes("products.html")) {
        const searchInput = document.getElementById("product-search");
        const categorySelect = document.getElementById("category-filter");
        const productList = document.getElementById("product-list");

        // Kategorien ins Dropdown-Menü laden
        function loadCategories() {
            fetch(`${PATH_PREFIX}/Backend/logic/get_categories.php`)
                .then(res => res.json())
                .then(categories => {
                    categories.forEach(cat => {
                        const option = document.createElement("option");
                        option.value = cat.id;
                        option.textContent = cat.name;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(err => console.error("Kategorien konnten nicht geladen werden:", err));
        }

        // Produkte basierend auf Filter anzeigen
        const fetchProducts = () => {
            const query = searchInput.value.trim();
            const category = categorySelect.value;

            let url = `${PATH_PREFIX}/Backend/logic/search_products.php?query=${encodeURIComponent(query)}`;
            if (category) url += `&category=${encodeURIComponent(category)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    productList.innerHTML = "";

                    if (!Array.isArray(data) || data.length === 0) {
                        productList.innerHTML = "<p>Keine Produkte gefunden.</p>";
                        return;
                    }

                    data.forEach(product => {
                        const imgPath = product.image.includes('/')
                            ? `${PATH_PREFIX}/Backend/${product.image}`
                            : `${PATH_PREFIX}/Backend/productpictures/${product.image}`;

                        // Produkt-Karten erstellen    
                        const col = document.createElement("div");
                        col.className = "col";
                        col.innerHTML = `
                        <div class="card h-100 w-100 shadow-sm product-card d-flex flex-column" draggable="true" data-id="${product.id}">
                            <img src="${imgPath}" class="card-img-top" alt="${product.name}">
                            <div class="card-body d-flex flex-column flex-grow-1">
                                <h5 class='card-title'>${product.name}</h5>
                                <p class='card-text'>${product.description}</p>
                                <p class='text-success fw-bold'>${parseFloat(product.price).toFixed(2)} €</p>
                                <button class="btn btn-success w-100 add-to-cart mt-auto" data-id="${product.id}">In den Warenkorb</button>
                            </div>
                        </div>
                        `;
                        productList.appendChild(col);

                        col.querySelector(".product-card").addEventListener("dragstart", e => {
                            e.dataTransfer.setData("text/plain", product.id);
                        });

                        col.querySelector(".add-to-cart").addEventListener("click", () => {
                            addToCart(product.id);
                        });
                    });
                })
                .catch(error => {
                    console.warn("Produkte konnten nicht geladen werden:", error);
                });
        };

        // Event-Listener registrieren
        searchInput.addEventListener("input", fetchProducts);
        categorySelect.addEventListener("change", fetchProducts);

        loadCategories();
        fetchProducts();
    }
});