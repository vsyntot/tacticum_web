document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumScrollInit === "true") return;
    root.dataset.tacticumScrollInit = "true";

    const header = document.querySelector("header");
    if (header) {
        window.addEventListener("scroll", function () {
            if (window.scrollY > 50) header.classList.add("shadow-md");
            else header.classList.remove("shadow-md");
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        if (anchor.dataset.tacticumSmoothBound) return;
        anchor.dataset.tacticumSmoothBound = "true";
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const targetId = this.getAttribute("href");
            if (targetId === "#") return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({ top: targetElement.offsetTop - 100, behavior: "smooth" });
            }
        });
    });

    const backToTopButton = document.getElementById("backToTop");
    if (backToTopButton && !backToTopButton.dataset.tacticumScrollBound) {
        backToTopButton.dataset.tacticumScrollBound = "true";
        window.addEventListener("scroll", function () {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove("opacity-0", "invisible");
                backToTopButton.classList.add("opacity-100", "visible");
            } else {
                backToTopButton.classList.remove("opacity-100", "visible");
                backToTopButton.classList.add("opacity-0", "invisible");
            }
        });
        backToTopButton.addEventListener("click", function () {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
});
