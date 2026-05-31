document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumMenuInit === "true") return;
    root.dataset.tacticumMenuInit = "true";

    const menuButton = document.querySelector("[data-tacticum-menu-toggle]") || document.querySelector(".ri-menu-line")?.parentElement;
    if (!menuButton) return;

    const mobileMenu = document.getElementById("tacticum-mobile-menu");
    if (!mobileMenu) return;

    const closeButton = mobileMenu.querySelector(".tacticum-mobile-menu-close");

    const setOpen = function (isOpen) {
        mobileMenu.classList.toggle("translate-x-full", !isOpen);
        mobileMenu.setAttribute("aria-hidden", isOpen ? "false" : "true");
        menuButton.setAttribute("aria-expanded", isOpen ? "true" : "false");
        document.body.classList.toggle("overflow-hidden", isOpen);

        if ("inert" in mobileMenu) {
            mobileMenu.inert = !isOpen;
        }

        if (isOpen) {
            closeButton?.focus();
        }
    };

    setOpen(false);

    menuButton.addEventListener("click", function () {
        setOpen(mobileMenu.classList.contains("translate-x-full"));
    });

    closeButton?.addEventListener("click", function () {
        setOpen(false);
    });

    mobileMenu.querySelectorAll("a[href]").forEach(function (link) {
        link.addEventListener("click", function () {
            setOpen(false);
        });
    });

    mobileMenu.querySelectorAll(".tacticum-contact-btn").forEach(function (button) {
        button.addEventListener("click", function () {
            setOpen(false);
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !mobileMenu.classList.contains("translate-x-full")) {
            setOpen(false);
            menuButton.focus();
        }
    });
});
