// Warenkorb-Management
// Datei enthält Funktionen zur Verwaltung des Warenkorbs, einschließlich
// Hinzufügen, Entfernen und Aktualisieren von Produkten im Warenkorb

function updateOrderButtonState() { // Bestellbutton aktivieren/deaktivieren
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const orderButton = document.getElementById("order-button");
    if (orderButton) {
        orderButton.disabled = cart.length === 0;
        orderButton.classList.toggle("disabled", cart.length === 0);
    }
}

function addToCart(productId) { // Produkt zum Warenkorb hinzufügen
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const id = parseInt(productId, 10);

    const index = cart.findIndex(item => item.id === id);
    if (index >= 0) {
        cart[index].quantity += 1;
    } else {
        cart.push({ id: id, quantity: 1 });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    syncCartToBackend();
    updateOrderButtonState();
}

function updateCartCount() { // Warenkorb-Zähler aktualisieren
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const counter = document.getElementById("cart-count");
    if (counter) {
        counter.textContent = totalCount;
    }
}

function loadCart() { // Warenkorb laden und anzeigen
    let rawCart = JSON.parse(localStorage.getItem("cart")) || [];

    const cart = [];
    rawCart.forEach(item => {
        const id = parseInt(item.id, 10);
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

    updateOrderButtonState();

    if (!cartItems || !cartTotal) return;

    cartItems.innerHTML = "";
    let total = 0;

    if (cart.length === 0) {
        cartItems.innerHTML = '<tr><td colspan="5">Dein Warenkorb ist leer.</td></tr>';
        cartTotal.textContent = "0.00 €";
        return;
    }

    let processed = 0;

    cart.forEach(item => { // Prokuktinformationen abrufen
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

function updateQuantity(productId, change) { // Produktmenge im Warenkorb aktualisieren
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const id = parseInt(productId, 10);
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
    updateOrderButtonState();
}

function removeFromCart(productId) { // Produkt aus dem Warenkorb entfernen
    const id = parseInt(productId, 10);
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
    updateCartCount();
    syncCartToBackend();
    updateOrderButtonState();
}

function syncCartToBackend() { // Warenkorb mit Backend synchronisieren
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
    updateOrderButtonState();
    if (window.location.pathname.includes("cart.html")) {
        loadCart();
    }

    loadPaymentOptions();

    const orderBtn = document.getElementById("order-button");
    if (orderBtn) {
        orderBtn.addEventListener("click", () => submitOrder());
    }
});
 
function loadPaymentOptions() { // Zahlungsmethoden laden
    const pmContainer = document.getElementById("payment-methods");
    const couponRadio  = document.getElementById("pay-coupon-radio");
    const couponInput  = document.getElementById("coupon-code");
    const orderBtn     = document.getElementById("order-button");

    if (!pmContainer || !couponRadio || !couponInput || !orderBtn) {
        return;
    }

    couponInput.disabled = true;

    couponRadio.addEventListener("change", () => {
        couponInput.disabled = false;
        document.querySelectorAll(".pay-radio").forEach(r => r.checked = false);
    });

    fetch('../../Backend/logic/getPaymentMethods.php', { credentials: 'include' })
      .then(r => r.json())
      .then(json => {
        if (!json.success || !json.methods.length) {
          pmContainer.innerHTML = `
            <div class="alert alert-warning">
              Sie haben noch keine Zahlungsmethode hinterlegt.
              <a href="account.html">Jetzt hinzufügen</a>
            </div>`;
          orderBtn.disabled = true;
          return;
        }

        let html = '';
        json.methods.forEach((m, i) => {
          html += `
            <div class="form-check">
              <input
                class="form-check-input pay-radio"
                type="radio"
                name="payOption"
                id="pay-${i}"
                value="${m.id}"
                ${i===0?'checked':''}
              >
              <label class="form-check-label" for="pay-${i}">
                ${m.type} **** **** **** ${m.last4}
              </label>
            </div>
          `;
        });
        pmContainer.innerHTML = html;

        document.querySelectorAll(".pay-radio").forEach(radio =>
          radio.addEventListener("change", () => couponInput.disabled = true)
        );
      })
      .catch(err => {
        console.error("Fehler beim Laden der Zahlungsmethoden:", err);
      });
}

function submitOrder() { // Bestellung abschicken
    const items = JSON.parse(localStorage.getItem("cart")) || [];
    const sel = document.querySelector('input[name="payOption"]:checked');
    const isCoupon = sel && sel.id === 'pay-coupon-radio';
    const payload = {
      items: items,
      payment_method: isCoupon ? '' : (sel ? sel.value : ''),
      gift_code:      isCoupon
        ? (document.getElementById("coupon-code").value.trim() || '')
        : ''
    };

    fetch("/Blattwerk/Blattwerk-/Backend/logic/submitOrder.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Bestellung erfolgreich aufgegeben!");
            localStorage.removeItem("cart");
            loadCart();
            updateCartCount();
            updateOrderButtonState();
            window.location.href = "orders.html";
        } else {
            alert("Fehler: " + (data.message || "Unbekannter Fehler"));
            if (data.error === "no_payment_info") {
                window.location.href = "account.html";
            }
        }
    })
    .catch(err => {
        alert("Fehler beim Senden der Bestellung.");
        console.error(err);
    });
}