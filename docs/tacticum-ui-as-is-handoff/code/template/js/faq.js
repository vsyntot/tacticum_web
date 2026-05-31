document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumFaqInit === "true") return;
    root.dataset.tacticumFaqInit = "true";

    const faqItems = document.querySelectorAll(".faq-item");
    if (faqItems.length) {
        faqItems.forEach((item) => {
            const question = item.querySelector(".faq-question");
            if (!question) return;
            if (question.dataset.tacticumFaqBound) return;
            question.dataset.tacticumFaqBound = "true";
            question.addEventListener("click", function () {
                const isActive = item.classList.contains("active");
                faqItems.forEach((faqItem) => faqItem.classList.remove("active"));
                if (!isActive) item.classList.add("active");
            });
        });
    }

    const teamMembers = document.querySelectorAll(".team-member");
    teamMembers.forEach((member) => {
        if (member.dataset.tacticumTeamBound) return;
        member.dataset.tacticumTeamBound = "true";
        member.addEventListener("mouseenter", function () {
            const overlay = this.querySelector(".member-overlay");
            if (overlay) overlay.style.opacity = "1";
        });
        member.addEventListener("mouseleave", function () {
            const overlay = this.querySelector(".member-overlay");
            if (overlay) overlay.style.opacity = "0";
        });
    });
});
