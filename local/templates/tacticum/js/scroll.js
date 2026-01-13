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

    function updateCountdown() {
        const targetDate = new Date("2025-06-30T23:59:59");
        const now = new Date();
        const difference = targetDate - now;

        if (difference > 0) {
            const days = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));

            const timerBoxes = document.querySelectorAll(".timer-box");
            if (timerBoxes.length >= 3) {
                timerBoxes[0].textContent = days.toString().padStart(2, "0");
                timerBoxes[1].textContent = hours.toString().padStart(2, "0");
                timerBoxes[2].textContent = minutes.toString().padStart(2, "0");
            }
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 60000);

    const filterTabs = document.querySelectorAll(".container button");
    filterTabs.forEach((tab) => {
        const tx = tab.textContent.trim();
        if (
            tx !== "Заказать специалиста" &&
            tx !== "Получить полную оценку" &&
            tx !== "Скачать полный прайс-лист" &&
            tx !== "Получить коммерческое предложение" &&
            tx !== "Связаться с нами"
        ) {
            if (tab.dataset.tacticumFilterBound) return;
            tab.dataset.tacticumFilterBound = "true";
            tab.addEventListener("click", function () {
                filterTabs.forEach((t) => {
                    const tt = t.textContent.trim();
                    if (
                        tt !== "Заказать специалиста" &&
                        tt !== "Получить полную оценку" &&
                        tt !== "Скачать полный прайс-лист" &&
                        tt !== "Получить коммерческое предложение" &&
                        tt !== "Связаться с нами"
                    ) {
                        t.classList.remove("bg-primary", "text-white");
                        t.classList.add("bg-white", "border", "border-gray-200", "text-gray-700");
                    }
                });
                this.classList.remove("bg-white", "border", "border-gray-200", "text-gray-700");
                this.classList.add("bg-primary", "text-white");
            });
        }
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
