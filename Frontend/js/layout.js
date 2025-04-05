document.addEventListener("DOMContentLoaded", () => {
    const basePath = window.location.pathname.includes("/sites/") ? "../" : "./";

    // Navbar
    fetch(`${basePath}components/navbar.html`)
    .then(res => res.text())
    .then(data => {
    document.getElementById("navbar-placeholder").innerHTML = data;

    // Nachträglich Links anpassen
    const links = document.querySelectorAll("#navbar-placeholder a");
    links.forEach(link => {
        if (!link.href.includes("http")) return;

        const isSitesPage = window.location.pathname.includes("/sites/");
        const pathUp = isSitesPage ? "../" : "./";

        // Nur wenn href nicht absolut beginnt
        if (!link.getAttribute("href").startsWith("http") && !link.getAttribute("href").startsWith("/")) {
            link.setAttribute("href", pathUp + link.getAttribute("href"));
        }
    });
});

        

    // Footer
    fetch(`${basePath}components/footer.html`)
        .then(res => res.text())
        .then(data => {
            document.getElementById("footer-placeholder").innerHTML = data;
        });
});