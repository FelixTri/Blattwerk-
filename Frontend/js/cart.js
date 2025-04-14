function addToCart(productId) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const index = cart.findIndex(item => item.id === productId);

    if (index >= 0) {
        cart[index].quantity += 1;
    } else {
        cart.push({ id: productId, quantity: 1 });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
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
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartList = document.getElementById("cart-items");
    const cartTotal = document.getElementById("cart-total");

    if (!cartList || !cartTotal) return;

    cartList.innerHTML = "";
    let total = 0;

    cart.forEach(item => {
        fetch(`../../Backend/logic/getProduct.php?id=${item.id}`)
            .then(res => res.json())
            .then(product => {
                const row = document.createElement("div");
                row.className = "mb-3";
                row.innerHTML = `
                    <strong>${product.name}</strong><br>
                    ${item.quantity} x ${product.price} € = ${(item.quantity * product.price).toFixed(2)} €
                    <hr>
                `;
                cartList.appendChild(row);

                total += item.quantity * product.price;
                cartTotal.textContent = total.toFixed(2) + " €";
            });
    });
}