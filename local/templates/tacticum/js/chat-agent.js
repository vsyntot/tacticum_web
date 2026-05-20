document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    if (root.dataset.tacticumChatAgentInit === "true") return;
    root.dataset.tacticumChatAgentInit = "true";

    const CHAT_ENDPOINT = "/local/rest/tacticum_chat.php";
    const PREFILL_ENDPOINT = "/local/rest/tacticum_prefill.php";

    const trackEvent = (eventName, params = {}) => {
        if (typeof window.tacticumTrackEvent === "function") {
            window.tacticumTrackEvent(eventName, params);
        }
    };

    const getResultCode = (result) => result?.data?.code || (result?.ok ? "ok" : "unknown");

    const trackChat = (eventName, surface, params = {}) => {
        trackEvent(eventName, {
            surface,
            ...params,
        });
    };

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

    const createTextBlock = (text, className) => {
        const block = document.createElement("div");
        block.className = className;
        const paragraph = document.createElement("p");
        paragraph.textContent = text || "";
        paragraph.style.whiteSpace = "pre-line";
        block.appendChild(paragraph);
        return block;
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

    const appendHeroMessage = (chatArea, role, text, options = {}) => {
        const div = document.createElement("div");
        div.className = role === "user"
            ? "bg-white/10 rounded-lg p-3 text-white"
            : "bg-primary/20 rounded-lg p-3 text-white";

        const label = document.createElement("p");
        label.className = "text-sm text-white/70 mb-1";
        label.textContent = role === "user" ? "Пользователь:" : "AI-ассистент:";
        div.appendChild(label);

        const message = document.createElement("p");
        message.textContent = text || "";
        message.style.whiteSpace = "pre-line";
        div.appendChild(message);

        if (options.showOfferLink) {
            const links = document.createElement("p");
            links.className = "mt-3 flex flex-wrap gap-x-3 gap-y-2";

            const offer = document.createElement("a");
            offer.href = "#contact-form";
            offer.className = options.groupId ? "offer-link underline" : "fallback-offer-link underline";
            offer.textContent = "Оформить заявку";
            links.appendChild(offer);

            if (options.bitrixUrl) {
                const full = document.createElement("a");
                full.href = options.bitrixUrl;
                full.target = "_blank";
                full.rel = "noopener";
                full.className = "underline";
                full.textContent = "Полный расчет";
                links.appendChild(full);

                const fresh = document.createElement("a");
                fresh.href = "/";
                fresh.className = "underline";
                fresh.textContent = "Новый расчет";
                links.appendChild(fresh);
            }

            div.appendChild(links);
        }

        chatArea.insertBefore(div, chatArea.lastElementChild);
        chatArea.scrollTop = chatArea.scrollHeight;
    };

    const appendDarkCalculatorMessage = (container, role, text, options = {}) => {
        const div = document.createElement("div");
        div.className = role === "user"
            ? "bg-white/5 rounded-lg p-3 text-white"
            : "bg-white/10 rounded-lg p-3 text-white";

        const label = document.createElement("p");
        label.className = "text-sm text-white/70 mb-1";
        label.textContent = role === "user" ? "Пользователь:" : "AI-ассистент:";
        div.appendChild(label);

        const message = document.createElement("p");
        message.textContent = text || "";
        message.style.whiteSpace = "pre-line";
        div.appendChild(message);

        if (options.showOfferLink) {
            const offer = document.createElement("a");
            offer.href = "#contact-form";
            offer.className = "mt-3 inline-block underline";
            offer.textContent = "Оформить заявку";
            div.appendChild(offer);
        }

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    };

    const appendLightCalculatorMessage = (container, role, text, options = {}) => {
        const div = createTextBlock(
            text,
            role === "user"
                ? "bg-gray-100 rounded-lg p-4 ml-auto max-w-[80%]"
                : "bg-primary/10 rounded-lg p-4"
        );

        if (role !== "user" && options.bitrixUrl) {
            const linkRow = document.createElement("p");
            linkRow.className = "mt-3";
            const link = document.createElement("a");
            link.className = "underline text-primary";
            link.href = options.bitrixUrl;
            link.target = "_blank";
            link.rel = "noopener";
            link.textContent = "Полный расчет";
            linkRow.appendChild(link);
            div.appendChild(linkRow);
        }

        if (role !== "user" && options.showOfferLink) {
            const offerRow = document.createElement("p");
            offerRow.className = "mt-3";
            const offer = document.createElement("a");
            offer.className = "underline text-primary";
            offer.href = "#contact-form";
            offer.textContent = "Оформить заявку";
            offerRow.appendChild(offer);
            div.appendChild(offerRow);
        }

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
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

    const initHeroChat = () => {
        const root = document.getElementById("main_chat");
        if (!root || root.dataset.tacticumChatBound === "true") return;

        const chatArea = root.querySelector(".space-y-4");
        const chatInput = root.querySelector("input");
        const sendBtn = root.querySelector("#aichat");
        if (!chatArea || !chatInput || !sendBtn) return;

        root.dataset.tacticumChatBound = "true";
        let groupId = null;
        let isSending = false;
        let lastMessage = "";

        const setSending = (value) => {
            isSending = value;
            sendBtn.disabled = value;
        };

        const showTyping = () => {
            if (chatArea.querySelector(".typing-indicator-container")) return null;
            const typing = createTyping("bg-primary/20 rounded-lg p-3 text-white", true);
            chatArea.insertBefore(typing, chatArea.lastElementChild);
            chatArea.scrollTop = chatArea.scrollHeight;
            return typing;
        };

        const send = async () => {
            const message = (chatInput.value || "").trim();
            if (!message || isSending) return;

            lastMessage = message;
            appendHeroMessage(chatArea, "user", message);
            chatInput.value = "";
            setSending(true);
            const typing = showTyping();

            try {
                trackChat("tacticum_chat_send", "hero");
                const result = await sendChatMessage(message, groupId);
                const res = result.data || {};
                typing?.remove();

                if (result.ok && res.response) {
                    trackChat("tacticum_chat_success", "hero", {
                        status: result.status,
                        has_group_id: Boolean(res.group_id || groupId),
                        has_offer_url: Boolean(res.bitrix_url),
                    });
                    appendHeroMessage(chatArea, "ai", res.response, {
                        showOfferLink: true,
                        groupId: res.group_id || groupId,
                        bitrixUrl: res.bitrix_url || "",
                    });
                    if (res.group_id) groupId = res.group_id;
                    return;
                }

                trackChat("tacticum_chat_error", "hero", {
                    status: result.status,
                    code: getResultCode(result),
                });
                appendHeroMessage(chatArea, "ai", getChatErrorMessage(result), { showOfferLink: true });
            } catch (error) {
                typing?.remove();
                trackChat("tacticum_chat_error", "hero", {
                    status: "network",
                    code: "fetch_error",
                });
                appendHeroMessage(chatArea, "ai", `Ошибка запроса: ${error.message}`, { showOfferLink: true });
            } finally {
                setSending(false);
            }
        };

        sendBtn.addEventListener("click", send);
        chatInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") send();
        });

        chatArea.addEventListener("click", async (event) => {
            const fallbackLink = event.target.closest(".fallback-offer-link");
            if (fallbackLink) {
                event.preventDefault();
                const messageField = document.getElementById("cta-message");
                if (messageField && lastMessage) {
                    messageField.value = lastMessage;
                    window.tacticum_offer_context = { task: lastMessage };
                }
                document.getElementById("contact-form")?.scrollIntoView({ behavior: "smooth" });
                messageField?.focus();
                return;
            }

            const offerLink = event.target.closest(".offer-link");
            if (!offerLink) return;
            event.preventDefault();

            if (!groupId) {
                trackChat("tacticum_prefill_error", "hero", {
                    code: "missing_group_id",
                });
                appendHeroMessage(chatArea, "ai", "Не найден идентификатор обращения. Оставьте заявку вручную.", {
                    showOfferLink: false,
                });
                return;
            }

            const sessid = getSessid();
            const prefillPayload = { group_id: groupId };
            if (sessid) {
                prefillPayload.sessid = sessid;
            }

            try {
                trackChat("tacticum_prefill_submit", "hero");
                const response = await fetch(PREFILL_ENDPOINT, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(prefillPayload),
                });
                const result = await response.json().catch(() => null);
                const nameField = document.getElementById("cta-name");
                const messageField = document.getElementById("cta-message");
                if (result?.success) {
                    trackChat("tacticum_prefill_success", "hero", {
                        status: response.status,
                    });
                    if (nameField) nameField.value = result.client_name || "";
                    if (messageField) messageField.value = result.summary || "";
                    window.tacticum_offer_context = { groupId: result.group_id, task: result.summary };
                } else if (messageField && lastMessage) {
                    trackChat("tacticum_prefill_error", "hero", {
                        status: response.status,
                        code: result?.code || "unknown",
                    });
                    messageField.value = lastMessage;
                    window.tacticum_offer_context = { groupId, task: lastMessage };
                }
                document.getElementById("contact-form")?.scrollIntoView({ behavior: "smooth" });
                nameField?.focus();
            } catch (error) {
                trackChat("tacticum_prefill_error", "hero", {
                    status: "network",
                    code: "fetch_error",
                });
                appendHeroMessage(chatArea, "ai", `Ошибка получения данных: ${error.message}`, {
                    showOfferLink: true,
                });
            }
        });
    };

    const initDarkCalculatorChat = () => {
        const section = document.querySelector("section#calculator");
        if (!section || section.dataset.tacticumChatBound === "true") return;

        const messages = section.querySelector("#chatMessages");
        const input = section.querySelector("#userMessage");
        const button = section.querySelector("#sendMessage");
        if (!messages || !input || !button) return;

        section.dataset.tacticumChatBound = "true";
        let groupId = null;
        let isSending = false;

        const send = async () => {
            const message = (input.value || "").trim();
            if (!message || isSending) return;

            appendDarkCalculatorMessage(messages, "user", message);
            input.value = "";
            isSending = true;
            button.disabled = true;
            const typing = createTyping("bg-white/10 rounded-lg p-3 text-white", true);
            messages.appendChild(typing);
            messages.scrollTop = messages.scrollHeight;

            try {
                trackChat("tacticum_chat_send", "dark_calculator");
                const result = await sendChatMessage(message, groupId);
                const res = result.data || {};
                typing.remove();
                if (result.ok && res.response) {
                    trackChat("tacticum_chat_success", "dark_calculator", {
                        status: result.status,
                        has_group_id: Boolean(res.group_id || groupId),
                    });
                    appendDarkCalculatorMessage(messages, "ai", res.response);
                    if (res.group_id) groupId = res.group_id;
                } else {
                    trackChat("tacticum_chat_error", "dark_calculator", {
                        status: result.status,
                        code: getResultCode(result),
                    });
                    appendDarkCalculatorMessage(messages, "ai", getChatErrorMessage(result), { showOfferLink: true });
                }
            } catch (error) {
                typing.remove();
                trackChat("tacticum_chat_error", "dark_calculator", {
                    status: "network",
                    code: "fetch_error",
                });
                appendDarkCalculatorMessage(messages, "ai", `Ошибка запроса: ${error.message}`, {
                    showOfferLink: true,
                });
            } finally {
                isSending = false;
                button.disabled = false;
            }
        };

        button.addEventListener("click", send);
        input.addEventListener("keydown", (event) => {
            if (event.key === "Enter") send();
        });
    };

    const initLightCalculatorChats = () => {
        document.querySelectorAll(".ai-chat-container").forEach((chatRoot) => {
            if (chatRoot.dataset.tacticumChatBound === "true") return;

            const input = chatRoot.querySelector("input[type='text'], input:not([type])");
            const sendButton = input?.parentElement?.querySelector("button");
            const messages = chatRoot.querySelector(".p-6");
            const quickReplies = chatRoot.querySelectorAll(".mt-3 button");
            if (!input || !sendButton || !messages) return;

            chatRoot.dataset.tacticumChatBound = "true";
            let groupId = null;
            let isSending = false;

            const send = async (rawMessage) => {
                const message = (rawMessage || "").trim();
                if (!message || isSending) return;

                input.value = "";
                isSending = true;
                sendButton.disabled = true;
                appendLightCalculatorMessage(messages, "user", message);
                const typing = createTyping("ai-typing bg-primary/10 rounded-lg p-4 inline-block");
                messages.appendChild(typing);
                messages.scrollTop = messages.scrollHeight;

                try {
                    trackChat("tacticum_chat_send", "light_calculator");
                    const result = await sendChatMessage(message, groupId);
                    const res = result.data || {};
                    typing.remove();
                    if (result.ok && res.response) {
                        trackChat("tacticum_chat_success", "light_calculator", {
                            status: result.status,
                            has_group_id: Boolean(res.group_id || groupId),
                            has_offer_url: Boolean(res.bitrix_url),
                        });
                        appendLightCalculatorMessage(messages, "ai", res.response, {
                            bitrixUrl: res.bitrix_url || "",
                        });
                        if (res.group_id) groupId = res.group_id;
                    } else {
                        trackChat("tacticum_chat_error", "light_calculator", {
                            status: result.status,
                            code: getResultCode(result),
                        });
                        appendLightCalculatorMessage(messages, "ai", getChatErrorMessage(result), {
                            showOfferLink: true,
                        });
                    }
                } catch (error) {
                    typing.remove();
                    trackChat("tacticum_chat_error", "light_calculator", {
                        status: "network",
                        code: "fetch_error",
                    });
                    appendLightCalculatorMessage(messages, "ai", `Ошибка запроса: ${error.message}`, {
                        showOfferLink: true,
                    });
                } finally {
                    isSending = false;
                    sendButton.disabled = false;
                }
            };

            sendButton.addEventListener("click", () => send(input.value));
            input.addEventListener("keydown", (event) => {
                if (event.key === "Enter") send(input.value);
            });
            quickReplies.forEach((button) => {
                button.addEventListener("click", () => send(button.textContent));
            });
        });
    };

    initHeroChat();
    initDarkCalculatorChat();
    initLightCalculatorChats();
});
