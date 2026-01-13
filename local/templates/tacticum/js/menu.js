document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumMenuInit === "true") return;
    root.dataset.tacticumMenuInit = "true";

    const menuButton = document.querySelector(".ri-menu-line")?.parentElement;
    if (!menuButton) return;

    if (document.getElementById("tacticum-mobile-menu")) return;

    const mobileMenu = document.createElement("div");
    mobileMenu.id = "tacticum-mobile-menu";
    mobileMenu.className =
        "fixed inset-0 bg-secondary/95 z-50 flex flex-col items-center justify-center transform translate-x-full transition-transform duration-300";
    mobileMenu.innerHTML = `
      <div class="absolute top-4 right-4 w-10 h-10 flex items-center justify_center cursor-pointer text-white">
        <i class="ri-close-line text-2xl"></i>
      </div>
      <nav class="flex flex-col items-center space-y-6 text-xl">
        <a href="/" class="text-white hover:text-primary transition-colors">Главная</a>
        <a href="/services/" data-readdy="true" class="text-white hover:text-primary transition-colors">Услуги</a>
        <a href="/about/" class="text-white hover:text-primary transition-colors">О компании</a>
        <a href="/contacts/" data-readdy="true" class="text-white hover:text-primary transition-colors">Контакты</a>
      </nav>
      <div class="mt-12">
        <button class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap tacticum-contact-btn">Связаться с нами</button>
      </div>
      `;
    document.body.appendChild(mobileMenu);

    menuButton.addEventListener("click", function () {
        mobileMenu.classList.toggle("translate-x-full");
        document.body.classList.toggle("overflow-hidden");
    });

    const closeButton = mobileMenu.querySelector(".ri-close-line")?.parentElement;
    closeButton?.addEventListener("click", function () {
        mobileMenu.classList.add("translate-x-full");
        document.body.classList.remove("overflow-hidden");
    });
});
