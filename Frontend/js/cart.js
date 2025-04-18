function addToCart(productId) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const id = parseInt(productId);

    const index = cart.findIndex(item => item.id === id);
    if (index >= 0) {
        cart[index].quantity += 1;
    } else {
        cart.push({ id: id, quantity: 1 });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    syncCartToBackend();
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const counter = document.getElementById("cart-count");
    if (counter) {
        counter.textContent = totalCount;
    }
}

function loadCart() {
    let rawCart = JSON.parse(localStorage.getItem("cart")) || [];

    // ✅ Duplikate zusammenfassen
    const cart = [];
    rawCart.forEach(item => {
        const id = parseInt(item.id);
        const existing = cart.find(i => i.id === id);
        if (existing) {
            existing.quantity += item.quantity;
        } else {
            cart.push({ id, quantity: item.quantity });
        }
    });
    localStorage.setItem("cart", JSON.stringify(cart));

    const cartItems = document.getElementById("cart-items");
    const cartTotal = document.getElementById("cart-total");

    if (!cartItems || !cartTotal) return;

    cartItems.innerHTML = "";
    let total = 0;

    if (cart.length === 0) {
        cartItems.innerHTML = '<tr><td colspan="5">Dein Warenkorb ist leer.</td></tr>';
        cartTotal.textContent = "0.00 €";
        return;
    }

    let processed = 0;

    cart.forEach(item => {
        fetch(`/Blattwerk/Blattwerk-/Backend/logic/getProduct.php?id=${item.id}`)
            .then(res => res.json())
            .then(product => {
                if (!product || product.error || !product.price) {
                    console.warn("Produktfehler:", product);
                    return;
                }

                const price = parseFloat(product.price);
                const subtotal = item.quantity * price;
                total += subtotal;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><strong>${product.name}</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary decrease" data-id="${item.id}">–</button>
                            <span>${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary increase" data-id="${item.id}">+</button>
                        </div>
                    </td>
                    <td>${price.toFixed(2)} €</td>
                    <td>${subtotal.toFixed(2)} €</td>
                    <td><button class="btn btn-sm btn-danger remove" data-id="${item.id}">X</button></td>
                `;
                cartItems.appendChild(row);

                row.querySelector(".increase").addEventListener("click", () => updateQuantity(item.id, 1));
                row.querySelector(".decrease").addEventListener("click", () => updateQuantity(item.id, -1));
                row.querySelector(".remove").addEventListener("click", () => removeFromCart(item.id));

                processed++;
                if (processed === cart.length) {
                    cartTotal.textContent = total.toFixed(2) + " €";
                }
            })
            .catch(err => {
                console.error("Fehler beim Laden des Produkts:", err);
            });
    });
}

function updateQuantity(productId, change) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const id = parseInt(productId);
    const index = cart.findIndex(item => item.id === id);

    if (index >= 0) {
        cart[index].quantity += change;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
    updateCartCount();
    syncCartToBackend();
}

function removeFromCart(productId) {
    const id = parseInt(productId);
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
    updateCartCount();
    syncCartToBackend();
}

function syncCartToBackend() {
    fetch("/Blattwerk/Blattwerk-/Backend/logic/saveCart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: localStorage.getItem("cart")
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.warn("Cart Sync fehlgeschlagen:", data);
        }
    })
    .catch(err => {
        console.error("Fehler beim Sync mit Backend:", err);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    updateCartCount();
    if (window.location.pathname.includes("cart.html")) {
        loadCart();
    }
});

function submitOrder() {
    fetch("/Blattwerk/Blattwerk-/Backend/logic/submitOrder.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: localStorage.getItem("cart")
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Bestellung erfolgreich aufgegeben!");
            localStorage.removeItem("cart");
            loadCart();
            updateCartCount();
        } else {
            alert("Fehler: " + (data.message || "Unbekannter Fehler"));
        }
    })
    .catch(err => {
        alert("Fehler beim Senden der Bestellung.");
        console.error(err);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const orderBtn = document.getElementById("order-button");
    if (orderBtn) {
        orderBtn.addEventListener("click", () => {
            submitOrder();
        });
    }
});