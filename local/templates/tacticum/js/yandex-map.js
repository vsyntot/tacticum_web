document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    if (root.dataset.tacticumYandexMapInit === "true") return;
    root.dataset.tacticumYandexMapInit = "true";

    document.querySelectorAll("[data-yandex-constructor-map]").forEach((container) => {
        if (container.dataset.tacticumYandexMapBound === "true") return;

        const src = (container.dataset.yandexConstructorSrc || "").trim();
        if (!src.startsWith("https://api-maps.yandex.ru/")) return;

        container.dataset.tacticumYandexMapBound = "true";
        const script = document.createElement("script");
        script.type = "text/javascript";
        script.charset = "utf-8";
        script.async = true;
        script.src = src;
        script.dataset.tacticumYandexMapScript = "true";
        container.appendChild(script);
    });
});
