(function () {
    if (window.TacticumChatRuntime) return;

    const CHAT_ENDPOINT = "/local/rest/tacticum_chat.php";
    const PREFILL_ENDPOINT = "/local/rest/tacticum_prefill.php";

    const trackEvent = (eventName, params = {}) => {
        if (typeof window.tacticumTrackEvent === "function") {
            window.tacticumTrackEvent(eventName, params);
        }
    };

    const trackChat = (eventName, surface, params = {}) => {
        trackEvent(eventName, {
            surface,
            ...params,
        });
    };

    const getResultCode = (result) => result?.data?.code || (result?.ok ? "ok" : "unknown");

    const getSessid = () => {
        if (window.BX && typeof BX.bitrix_sessid === "function") {
            return BX.bitrix_sessid();
        }
        return "";
    };

    const parseChatResult = (response) =>
        response.json()
            .catch(() => null)
            .then((data) => ({ ok: response.ok, status: response.status, data }));

    const getChatErrorMessage = (result) => {
        const data = result?.data || null;
        if (data?.code === "upstream_timeout") {
            return "AI-сервис не ответил вовремя. Попробуйте повторить запрос.";
        }
        if (
            data?.code === "upstream_http_error" ||
            data?.code === "upstream_error" ||
            data?.code === "upstream_contract_error" ||
            data?.code === "curl_error"
        ) {
            return "AI-сервис временно перегружен. Оставьте заявку, и мы подготовим оценку вручную.";
        }
        if (data?.message || data?.error) {
            return data.message || data.error;
        }
        return result?.status
            ? `Ошибка сервера: ${result.status}`
            : "AI-сервис не вернул ответ. Попробуйте повторить запрос.";
    };

    const hasFinalOfferActions = (data) => {
        return typeof data?.bitrix_url === "string" && data.bitrix_url.trim() !== "";
    };

    const createTextBlock = (text, className) => {
        const block = document.createElement("div");
        block.className = className;
        const paragraph = document.createElement("p");
        paragraph.textContent = text || "";
        paragraph.style.whiteSpace = "pre-line";
        block.appendChild(paragraph);
        return block;
    };

    const clampText = (text, maxLength = 1800) => {
        const value = (text || "").replace(/\s+\n/g, "\n").trim();
        return value.length > maxLength ? `${value.slice(0, maxLength - 3).trim()}...` : value;
    };

    const buildLeadHandoffSummary = (surface, userMessage, assistantResponse) => {
        const title = surface === "price"
            ? "Контекст из AI-калькулятора команды"
            : "Контекст из AI-калькулятора проекта";
        return clampText([
            title,
            userMessage ? `Запрос: ${userMessage}` : "",
            assistantResponse ? `Ответ AI: ${assistantResponse}` : "",
        ].filter(Boolean).join("\n\n"));
    };

    const createTyping = (className, withLabel = false) => {
        const el = document.createElement("div");
        el.className = `${className} typing-indicator-container`;
        if (withLabel) {
            const label = document.createElement("p");
            label.className = "text-sm text-white/70 mb-1";
            label.textContent = "AI-ассистент:";
            el.appendChild(label);
        }
        const indicator = document.createElement("div");
        indicator.className = "typing-indicator";
        indicator.innerHTML = "<span></span><span></span><span></span>";
        el.appendChild(indicator);
        return el;
    };

    const buildPayload = (message, groupId) => {
        const payload = { user_message: message };
        if (groupId) {
            payload.group_id = groupId;
        } else {
            payload.startAgent = "ITExpertAgent";
        }

        const sessid = getSessid();
        if (sessid) {
            payload.sessid = sessid;
        }

        return payload;
    };

    const sendChatMessage = async (message, groupId) => {
        const response = await fetch(CHAT_ENDPOINT, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(buildPayload(message, groupId)),
        });
        return parseChatResult(response);
    };

    const buildPrefillPayload = (groupId) => {
        const payload = { group_id: groupId };
        const sessid = getSessid();
        if (sessid) {
            payload.sessid = sessid;
        }
        return payload;
    };

    const sendPrefillRequest = async (groupId) => {
        const response = await fetch(PREFILL_ENDPOINT, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(buildPrefillPayload(groupId)),
        });
        const data = await response.json().catch(() => null);
        return { ok: response.ok, status: response.status, data };
    };

    window.TacticumChatRuntime = {
        buildLeadHandoffSummary,
        createTextBlock,
        createTyping,
        getChatErrorMessage,
        getResultCode,
        hasFinalOfferActions,
        sendChatMessage,
        sendPrefillRequest,
        trackChat,
    };
})();
