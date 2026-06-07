document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumFaqInit === "true") return;
    root.dataset.tacticumFaqInit = "true";

    const faqItems = document.querySelectorAll(".faq-item");
    if (faqItems.length) {
        const setFaqItemExpanded = (item, expanded) => {
            const question = item.querySelector(".faq-question");
            const answer = item.querySelector(".faq-answer");
            item.classList.toggle("active", expanded);
            if (question) {
                question.setAttribute("aria-expanded", expanded ? "true" : "false");
            }
            if (answer) {
                answer.setAttribute("aria-hidden", expanded ? "false" : "true");
            }
        };

        faqItems.forEach((item, index) => {
            const question = item.querySelector(".faq-question");
            const answer = item.querySelector(".faq-answer");
            if (!question) return;

            if (question.tagName !== "BUTTON") {
                question.setAttribute("role", "button");
                question.setAttribute("tabindex", "0");
            }

            if (answer && !answer.id) {
                answer.id = `tacticum-faq-answer-${index}`;
            }
            if (!question.id) {
                question.id = `tacticum-faq-question-${index}`;
            }
            if (answer) {
                question.setAttribute("aria-controls", answer.id);
                answer.setAttribute("role", "region");
                answer.setAttribute("aria-labelledby", question.id);
            }

            setFaqItemExpanded(item, item.classList.contains("active"));

            if (question.dataset.tacticumFaqBound) return;
            question.dataset.tacticumFaqBound = "true";
            question.addEventListener("click", function () {
                const isActive = item.classList.contains("active");
                faqItems.forEach((faqItem) => setFaqItemExpanded(faqItem, false));
                if (!isActive) setFaqItemExpanded(item, true);
            });
            if (question.tagName !== "BUTTON") {
                question.addEventListener("keydown", function (event) {
                    if (event.key !== "Enter" && event.key !== " ") return;
                    event.preventDefault();
                    question.click();
                });
            }
        });
    }

});
