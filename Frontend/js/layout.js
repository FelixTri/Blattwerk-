document.addEventListener("DOMContentLoaded", () => {
    const basePath = window.location.pathname.includes("/sites/") ? "../" : "./";

    // Navbar laden
    fetch(`${basePath}components/navbar.html`)
        .then(res => res.text())
        .then(data => {
            const navbarPlaceholder = document.getElementById("navbar-placeholder");
            if (!navbarPlaceholder) return;
            navbarPlaceholder.innerHTML = data;

            const nav = navbarPlaceholder.querySelector("ul.navbar-nav");
            if (!nav) return;

            fetch(`${basePath}Backend/logic/requestHandler.php?action=getSessionInfo`)
                .then(res => res.json())
                .then(user => {
                    // Standardlinks
                    nav.innerHTML = `
                        <li class="nav-item"><a class="nav-link" href="${basePath}index.html">Startseite</a></li>
                        <li class="nav-item"><a class="nav-link" href="${basePath}sites/products.html">Produkte</a></li>
                        <li class="nav-item"><a class="nav-link" href="${basePath}sites/cart.html">Warenkorb</a></li>
                    `;

                    if (user.role === "admin") {
                        nav.innerHTML += `<li class="nav-item"><a class="nav-link" href="${basePath}sites/admin.html">Adminbereich</a></li>`;
                    }

                    if (user.role !== "guest") {
                        nav.innerHTML += `<li class="nav-item"><a class="nav-link" href="${basePath}Backend/logic/logout.php">Logout</a></li>`;
                    } else {
                        nav.innerHTML += `<li class="nav-item"><a class="nav-link" href="${basePath}sites/login.html">Login</a></li>`;
                        nav.innerHTML += `<li class="nav-item"><a class="nav-link" href="${basePath}sites/register.html">Register</a></li>`;
                    }
                })
                .catch(err => {
                    console.error("Fehler beim Laden der Session:", err);
                });
        });

    // Footer laden
    fetch(`${basePath}components/footer.html`)
        .then(res => res.text())
        .then(data => {
            const footerPlaceholder = document.getElementById("footer-placeholder");
            if (footerPlaceholder) {
                footerPlaceholder.innerHTML = data;
            }
        });
});