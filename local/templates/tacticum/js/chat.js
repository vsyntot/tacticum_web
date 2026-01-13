document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumChatInit === "true") return;
    root.dataset.tacticumChatInit = "true";

    const chatMessages = document.getElementById("chatMessages");
    const userMessage = document.getElementById("userMessage");
    const sendMessage = document.getElementById("sendMessage");

    if (!chatMessages || !userMessage || !sendMessage) return;

    const aiResponses = [
        "Спасибо за информацию! В какой отрасли работает ваша компания?",
        "Какой примерный масштаб проекта вы рассматриваете?",
        "Есть ли у вас уже какие-то наработки или данные для обучения AI?",
        "Какие сроки реализации вы рассматриваете?",
        "Отлично! Давайте я подготовлю предварительную оценку. Оставьте, пожалуйста, ваши контактные данные для связи.",
    ];

    let messageCount = 0;

    function addMessage(text, isUser) {
        const messageDiv = document.createElement("div");
        messageDiv.className = "bg-white/5 rounded-lg p-3";
        messageDiv.innerHTML = `
      <p class="text-sm text-white/70 mb-1">${isUser ? "Вы" : "AI-ассистент"}:</p>
      <p class="text-white">${text}</p>
      `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function handleUserMessage() {
        const message = userMessage.value.trim();
        if (!message) return;
        addMessage(message, true);
        userMessage.value = "";

        setTimeout(() => {
            if (messageCount < aiResponses.length) {
                addMessage(aiResponses[messageCount], false);
                messageCount++;
            }
        }, 1000);
    }

    if (!sendMessage.dataset.tacticumChatBound) {
        sendMessage.dataset.tacticumChatBound = "true";
        sendMessage.addEventListener("click", handleUserMessage);
    }

    if (!userMessage.dataset.tacticumChatBound) {
        userMessage.dataset.tacticumChatBound = "true";
        userMessage.addEventListener("keypress", function (e) {
            if (e.key === "Enter") handleUserMessage();
        });
    }
});
