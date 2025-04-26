document.addEventListener("DOMContentLoaded", () => {
    const isInSites = window.location.pathname.includes("/Frontend/sites/");
    const frontendPath = isInSites ? "../../Frontend/" : "./";
    const backendPath = isInSites ? "../../Backend/" : "../Backend/";

    // Navbar laden
    fetch(`${frontendPath}components/navbar.html`)
        .then(res => res.text())
        .then(data => {
            const navbarPlaceholder = document.getElementById("navbar-placeholder");
            if (!navbarPlaceholder) return;
            navbarPlaceholder.innerHTML = data;


            const dropTarget = navbarPlaceholder.querySelector("#cart-dropzone");
            if (dropTarget) {
                dropTarget.addEventListener("dragover", (e) => e.preventDefault());

                dropTarget.addEventListener("dragenter", () => {
                    dropTarget.classList.add("drag-over");
                });

                dropTarget.addEventListener("dragleave", () => {
                    dropTarget.classList.remove("drag-over");
                });

                dropTarget.addEventListener("drop", (e) => {
                    dropTarget.classList.remove("drag-over");

                    e.preventDefault();
                    const productId = e.dataTransfer.getData("text/plain");
                    if (productId) {
                        addToCart(productId);
                    }
                });
            }

            updateCartCount();

            const nav = navbarPlaceholder.querySelector("ul.navbar-nav");
            if (!nav) return;

            fetch(`${backendPath}logic/requestHandler.php?action=getSessionInfo`)
                .then(res => res.json())
                .then(user => {
                    // Basis-Links (für alle sichtbar)
                    nav.innerHTML = `
                        <li class="nav-item"><a class="nav-link" href="/Blattwerk/Blattwerk-/Frontend/index.html">Startseite</a></li>
                        <li class="nav-item"><a class="nav-link" href="${frontendPath}sites/products.html">Produkte</a></li>
                        <li class="nav-item"><a class="nav-link" href="${frontendPath}sites/cart.html">Warenkorb</a></li>
                    `;

                    if (user.role !== 'guest') {
                        // Nur eingeloggte User sehen „Meine Bestellungen“
                        nav.innerHTML += `
                            <li class="nav-item">
                            <a class="nav-link" href="${frontendPath}sites/orders.html">Meine Bestellungen</a>
                            </li>
                        `;
                    }

                    if (user.role === "admin") {
                        nav.innerHTML += `
                            <li class="nav-item">
                            <a class="nav-link" href="${frontendPath}sites/admin.html">Adminbereich</a>
                            </li>`;
                    }

                    if (user.role !== "guest") {
                        nav.innerHTML += `
                            <li class="nav-item">
                            <a class="nav-link" href="${frontendPath}sites/account.html">Mein Account</a>
                            </li>
                            <li class="nav-item">
                            <a class="nav-link" href="${backendPath}logic/logout.php">Logout</a>
                            </li>`;
                    } else {
                        nav.innerHTML += `
                            <li class="nav-item"><a class="nav-link" href="${frontendPath}sites/login.html">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="${frontendPath}sites/register.html">Register</a></li>`;
                    }
                })
                .catch(err => {
                    console.error("Fehler beim Laden der Session:", err);
                });
        });

    // Footer laden
    fetch(`${frontendPath}components/footer.html`)
        .then(res => res.text())
        .then(data => {
            const footerPlaceholder = document.getElementById("footer-placeholder");
            if (footerPlaceholder) {
                footerPlaceholder.innerHTML = data;
            }
        });
});