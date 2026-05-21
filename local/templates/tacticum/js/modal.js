document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumModalInit === "true") return;
    root.dataset.tacticumModalInit = "true";

    const tacticumModal = document.getElementById("tacticum-modal");
    if (tacticumModal && !tacticumModal.dataset.tacticumModalBound) {
        tacticumModal.dataset.tacticumModalBound = "true";
        const backdrop = tacticumModal.firstElementChild;
        const closeBtn = document.getElementById("tacticum-modal-close");
        const form = document.getElementById("tacticum-modal-form");

        const openModal = () => {
            tacticumModal.classList.remove("hidden");
            document.body.classList.add("overflow-hidden");
            trapFocus();
            setTimeout(() => document.getElementById("modal-name")?.focus(), 50);
        };

        document.getElementById("contactUsBtn")?.addEventListener("click", (e) => {
            e.preventDefault();
            openModal();
        });

        document.querySelectorAll(".tacticum-contact-btn").forEach((btn) =>
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                openModal();
            })
        );

        const closeModal = () => {
            tacticumModal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
            form?.reset();
            tacticumModal.querySelectorAll("[data-error]").forEach((el) => el.classList.add("hidden"));
        };

        closeBtn?.addEventListener("click", closeModal);
        backdrop?.addEventListener("click", closeModal);
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && !tacticumModal.classList.contains("hidden")) closeModal();
        });

        function trapFocus() {
            const focusable = tacticumModal.querySelectorAll(
                'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
            );
            const list = Array.from(focusable).filter((el) => !el.hasAttribute("disabled"));
            if (!list.length) return;
            const first = list[0];
            const last = list[list.length - 1];
            function loop(e) {
                if (e.key !== "Tab") return;
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
            tacticumModal.addEventListener("keydown", loop);
        }

    }
});
