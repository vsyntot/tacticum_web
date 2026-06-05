(function () {
    const init = () => {
        const root = document.documentElement;
        if (root.dataset.tacticumChatAgentInit === "true") return;
        if (!window.TacticumChatRuntime || !window.TacticumChatSurfaces) return;
        root.dataset.tacticumChatAgentInit = "true";

        window.TacticumChatSurfaces.initHeroChat?.();
        window.TacticumChatSurfaces.initDarkCalculatorChat?.();
        window.TacticumChatSurfaces.initLightCalculatorChats?.();
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
