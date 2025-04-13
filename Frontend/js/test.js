const PATH_PREFIX = window.location.pathname.includes('/sites/') ? '../..' : '..';

document.addEventListener("DOMContentLoaded", () => {
    // Eventlistener für "In den Warenkorb"-Buttons (statisch im HTML vorhanden)
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", () => {
            const productId = button.getAttribute("data-id");
            addToCart(productId);
        });
    });

    // Cart-Ansicht laden, wenn cart.html geöffnet ist
    if (window.location.pathname.includes("cart.html")) {
        loadCart();
    }

    // 🔁 Cart-Zähler immer beim Laden aktualisieren
    updateCartCount();

    // Produktsuchfunktion aktivieren, wenn auf products.html
    if (window.location.pathname.includes("products.html")) {
        const searchInput = document.getElementById("product-search");
        const productList = document.getElementById("product-list");

        if (searchInput && productList) {
            const fetchProducts = (query = "") => {
                fetch(`${PATH_PREFIX}/Backend/logic/search_products.php?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        productList.innerHTML = "";

                        if (data.length === 0) {
                            productList.innerHTML = "<p>Keine Produkte gefunden.</p>";
                            return;
                        }

                        data.forEach(product => {
                            const col = document.createElement("div");
                            col.className = "col";
                            col.innerHTML = `
                                <div class="card h-100">
                                    <img src="${PATH_PREFIX}/Backend/productpictures/${product.image}" class="card-img-top" alt="${product.name}">
                                    <div class="card-body">
                                        <h5 class="card-title">${product.name}</h5>
                                        <p class="card-text">${product.description}</p>
                                    </div>
                                </div>`;
                            productList.appendChild(col);
                        });
                    });
            };

            fetchProducts(); // initial laden

            searchInput.addEventListener("input", () => {
                fetchProducts(searchInput.value);
            });
        }
    }
});

// Produkt in Warenkorb legen
function addToCart(productId) {
    fetch(`${PATH_PREFIX}/Backend/logic/requestHandler.php?action=addToCart`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `productId=${productId}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cartCount);
                console.log(`Produkt ${productId} zum Warenkorb hinzugefügt`);
            } else {
                console.error("Fehler beim Hinzufügen:", data.error);
            }
        })
        .catch(err => console.error("Fetch-Fehler:", err));
}

// Cart-Zähler aktualisieren
function updateCartCount(countFromAdd = null) {
    if (countFromAdd !== null) {
        setCartBadge(countFromAdd);
        return;
    }

    fetch(`${PATH_PREFIX}/Backend/logic/requestHandler.php?action=getCartCount`)
        .then(res => res.json())
        .then(data => setCartBadge(data.count));
}

// Badge-Anzeige aktualisieren
function setCartBadge(count) {
    const badge = document.getElementById("cart-count");
    if (badge) badge.textContent = count;
}

// Cart-Inhalte laden
function loadCart() {
    fetch(`${PATH_PREFIX}/Backend/logic/requestHandler.php?action=getCart`)
        .then(res => res.json())
        .then(products => {
            const container = document.querySelector("#cart-items");
            const totalElem = document.querySelector("#cart-total");
            if (!container || !totalElem) return;

            container.innerHTML = "";
            let total = 0;

            products.forEach(p => {
                total += p.price * p.quantity;
                const item = document.createElement("div");
                item.classList.add("cart-item", "border", "p-2", "mb-2");
                item.innerHTML = `
                    <h4>${p.name}</h4>
                    <p>${p.price} € × ${p.quantity}</p>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${p.id})">Entfernen</button>
                `;
                container.appendChild(item);
            });

            totalElem.textContent = total.toFixed(2) + " €";
        });
}

// Produkt aus dem Cart entfernen
function removeFromCart(productId) {
    fetch(`${PATH_PREFIX}/Backend/logic/requestHandler.php?action=removeFromCart`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `productId=${productId}`
    })
        .then(() => {
            loadCart();
            updateCartCount();
        });
}