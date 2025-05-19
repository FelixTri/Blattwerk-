// Warenkorb-Logik
// Verarbeitet das Hinzufügen, Anzeigen und Bestellen von Produkten im Warenkorb

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

    // Doppelte Einträge zusammenfassen
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

    // Wenn der Warenkorb leer ist
    if (cart.length === 0) {
        cartItems.innerHTML = '<tr><td colspan="5">Dein Warenkorb ist leer.</td></tr>';
        cartTotal.textContent = "0.00 €";
        return;
    }

    let processed = 0;

    // Produktdetails vom Server abrufen
    cart.forEach(item => {
        fetch(`/Blattwerk/Blattwerk-/Backend/logic/getProduct.php?id=${item.id}`)
            .then(res => res.json())
            .then(product => {
                if (!product || product.error || !product.price) {
                    console.warn("Produktfehler:", product);
                    return;
                }

                // Einzelpreis und Gesamtpreis berechnen
                const price = parseFloat(product.price);
                const subtotal = item.quantity * price;
                total += subtotal;

                // Tabellenzeile einfügen
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

                // Event-Listener für +/- und Löschen
                row.querySelector(".increase").addEventListener("click", () => updateQuantity(item.id, 1));
                row.querySelector(".decrease").addEventListener("click", () => updateQuantity(item.id, -1));
                row.querySelector(".remove").addEventListener("click", () => removeFromCart(item.id));

                processed++;
                if (processed === cart.length) {
                    // Gesamtpreis anzeigen, sobald alle Produkte geladen sind
                    cartTotal.textContent = total.toFixed(2) + " €";
                }
            })
            .catch(err => {
                console.error("Fehler beim Laden des Produkts:", err);
            });
    });
}

function updateQuantity(productId, change) { // Produktanzahl im Warenkorb ändern
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const id = parseInt(productId, 10);
    const index = cart.findIndex(item => item.id === id);

    if (index >= 0) {
        cart[index].quantity += change;

        // Produkt entfernen, wenn Menge 0 oder weniger
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart(); // Anzeige neu aufbauen
    updateCartCount(); // Zähler aktualisieren
    syncCartToBackend(); // Server synchronisieren
    updateOrderButtonState();
}

function removeFromCart(productId) { // Produkt aus Warenkorb löschen
    const id = parseInt(productId, 10);
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCart();
    updateCartCount();
    syncCartToBackend();
    updateOrderButtonState();
}

function syncCartToBackend() { // Warenkorb-Daten per POST an Server senden
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
        loadCart(); // Produkte im Warenkorb anzeigen
    }

    loadPaymentOptions(); // Zahlungsmethoden laden

    const orderBtn = document.getElementById("order-button");
    if (orderBtn) {
        // Bestellung abschicken, wenn auf "Jetzt bestellen" geklickt wird
        orderBtn.addEventListener("click", () => submitOrder());
    }

    // Gutscheincode einlösen
    const applyVoucherButton = document.getElementById("apply-voucher");
    const voucherInput = document.getElementById("voucher-code");
    const voucherMessage = document.getElementById("voucher-message");
    const cartTotalEl = document.getElementById("cart-total");

    let originalTotal = null;

    // Beim Klick auf "Einlösen"-Button
    applyVoucherButton?.addEventListener("click", () => {
        const code = voucherInput.value.trim();
        if (!code) {
            voucherMessage.textContent = "Bitte einen Gutscheincode eingeben.";
            return;
        }
    
        fetch(`/Blattwerk/Blattwerk-/Backend/logic/getVouchers.php?code=${encodeURIComponent(code)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.voucher) {
                    voucherMessage.textContent = "Ungültiger oder abgelaufener Gutschein.";
                    return;
                }
    
                const amount = parseFloat(data.voucher.amount);
                const active = data.voucher.active;
                const expiresAt = data.voucher.expires_at;
    
                // Prüfen ob aktiv
                if (!active || isNaN(amount)) {
                    voucherMessage.textContent = "Dieser Gutschein ist nicht mehr gültig.";
                    return;
                }
    
                // Prüfen ob abgelaufen (wenn expires_at gesetzt ist)
                if (expiresAt) {
                    const today = new Date().setHours(0,0,0,0);
                    const expiry = new Date(expiresAt).setHours(0,0,0,0);
                    if (expiry < today) {
                        voucherMessage.textContent = "Dieser Gutschein ist abgelaufen.";
                        return;
                    }
                }
    
                const currentText = cartTotalEl.textContent.replace("€", "").replace(",", ".").trim();
                if (originalTotal === null) {
                    originalTotal = parseFloat(currentText);
                }
    
                const abzug = Math.min(originalTotal, amount);
                const restwert = amount - abzug;
                const newTotal = Math.max(originalTotal - abzug, 0);
    
                cartTotalEl.textContent = newTotal.toFixed(2).replace(".", ",") + " €";
    
                voucherMessage.textContent = restwert > 0
                    ? `Gutschein eingelöst: -${abzug.toFixed(2).replace(".", ",")} € – Restwert: ${restwert.toFixed(2).replace(".", ",")} €`
                    : `Gutschein eingelöst: -${abzug.toFixed(2).replace(".", ",")} €`;
            })
            .catch(err => {
                console.error("Fehler beim Einlösen:", err);
                voucherMessage.textContent = "Fehler beim Einlösen des Gutscheins.";
            });
    });
});


function loadPaymentOptions() { // Zahlungsoptionen laden
    const pmContainer = document.getElementById("payment-methods");
    const orderBtn = document.getElementById("order-button");

    if (!pmContainer || !orderBtn) return;

    // Lade gespeicherte Zahlungsmethoden
    fetch('../../Backend/logic/getPaymentMethods.php', { credentials: 'include' })
        .then(r => r.json())
        .then(json => {
            // Wenn keine Zahlungsmethoden vorhanden sind
            if (!json.success || !json.methods.length) {
                pmContainer.innerHTML = `
                    <div class="alert alert-warning">
                        Sie haben noch keine Zahlungsmethode hinterlegt.
                        <a href="account.html">Jetzt hinzufügen</a>
                    </div>`;
                orderBtn.disabled = true;
                return;
            }

            // HTML für gespeicherte Zahlungsmethoden aufbauen
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
                            ${i === 0 ? 'checked' : ''}
                        >
                        <label class="form-check-label" for="pay-${i}">
                            ${m.type} **** **** **** ${m.last4}
                        </label>
                    </div>
                `;
            });

            pmContainer.innerHTML = html;

            const customPaymentInput = document.getElementById("custom-payment");
            const customPaymentRadio = document.getElementById("pay-custom");

            // Event-Listener für alle Zahlungsmethoden setzen
            document.querySelectorAll(".pay-radio").forEach(radio => {
                radio.addEventListener("change", () => {
                    if (!customPaymentInput || !customPaymentRadio) return;

                    // Benutzerdefinierte Eingabe aktivieren/deaktivieren
                    if (radio === customPaymentRadio) {
                        customPaymentInput.disabled = false;
                    } else {
                        customPaymentInput.disabled = true;
                        customPaymentInput.value = '';
                    }
                });
            });
        })
        .catch(err => {
            console.error("Fehler beim Laden der Zahlungsmethoden:", err);
            pmContainer.innerHTML = `
                <div class="alert alert-danger">
                    Zahlungsdaten konnten nicht geladen werden.
                </div>`;
        });
}

function submitOrder() { // Sendet die Bestellung an das Backend
    const items = JSON.parse(localStorage.getItem("cart")) || [];
    const selectedRadio = document.querySelector('input[name="payOption"]:checked');
    const isCustom = selectedRadio?.id === 'pay-custom';

    const giftCode = document.getElementById("coupon-code")?.value.trim() || '';
    const customInput = document.getElementById("custom-payment")?.value.trim() || '';

    // Daten für Bestellung zusammenstellen
    const payload = {
        items: items,
        gift_code: giftCode,
        payment_method: isCustom ? '' : (selectedRadio?.value || ''),
        custom_payment: isCustom ? customInput : ''
    };

    // Bestellung per POST ans Backend senden
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