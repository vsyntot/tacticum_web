document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumMenuInit === "true") return;
    root.dataset.tacticumMenuInit = "true";

    const menuButton = document.querySelector(".ri-menu-line")?.parentElement;
    if (!menuButton) return;

    const mobileMenu = document.getElementById("tacticum-mobile-menu");
    if (!mobileMenu) return;

    menuButton.addEventListener("click", function () {
        mobileMenu.classList.toggle("translate-x-full");
        document.body.classList.toggle("overflow-hidden");
    });

    const closeButton = mobileMenu.querySelector(".tacticum-mobile-menu-close");
    closeButton?.addEventListener("click", function () {
        mobileMenu.classList.add("translate-x-full");
        document.body.classList.remove("overflow-hidden");
    });
});
