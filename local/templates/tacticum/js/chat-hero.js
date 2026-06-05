(function () {
    window.TacticumChatSurfaces = window.TacticumChatSurfaces || {};

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

        const inputRow = chatArea.querySelector("input")?.closest("div");
        if (inputRow?.parentElement === chatArea) {
            chatArea.insertBefore(div, inputRow);
        } else {
            chatArea.appendChild(div);
        }
        chatArea.scrollTop = chatArea.scrollHeight;
    };

    const prepareHeroChatArea = (root) => {
        const existingMessages = root.querySelector("[data-hero-chat-messages]");
        if (existingMessages) {
            existingMessages.parentElement?.setAttribute("data-hero-chat-shell", "");
            return existingMessages;
        }

        const legacyShell = root.querySelector(".space-y-4");
        if (!legacyShell) return null;

        const inputRow = root.querySelector("#aichat")?.closest("div");
        if (!inputRow || inputRow.parentElement !== legacyShell) {
            legacyShell.setAttribute("data-hero-chat-messages", "");
            return legacyShell;
        }

        const messages = document.createElement("div");
        messages.className = "flex-1 min-h-0 overflow-y-auto mb-4 space-y-4";
        messages.setAttribute("data-hero-chat-messages", "");

        Array.from(legacyShell.children).forEach((child) => {
            if (child !== inputRow) {
                messages.appendChild(child);
            }
        });

        legacyShell.className = "flex flex-col h-[400px]";
        legacyShell.setAttribute("data-hero-chat-shell", "");
        legacyShell.append(messages, inputRow);

        return messages;
    };

    const initHeroChat = () => {
        const chat = window.TacticumChatRuntime;
        const root = document.getElementById("main_chat");
        if (!chat || !root || root.dataset.tacticumChatBound === "true") return;

        const chatArea = prepareHeroChatArea(root);
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
            const typing = chat.createTyping("bg-primary/20 rounded-lg p-3 text-white", true);
            const inputRow = chatArea.querySelector("input")?.closest("div");
            if (inputRow?.parentElement === chatArea) {
                chatArea.insertBefore(typing, inputRow);
            } else {
                chatArea.appendChild(typing);
            }
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
                chat.trackChat("tacticum_chat_send", "hero");
                const result = await chat.sendChatMessage(message, groupId);
                const res = result.data || {};
                typing?.remove();

                if (result.ok && res.response) {
                    const showFinalActions = chat.hasFinalOfferActions(res);
                    chat.trackChat("tacticum_chat_success", "hero", {
                        status: result.status,
                        has_group_id: Boolean(res.group_id || groupId),
                        has_offer_url: Boolean(res.bitrix_url),
                        is_final: showFinalActions,
                    });
                    appendHeroMessage(chatArea, "ai", res.response, {
                        showOfferLink: showFinalActions,
                        groupId: res.group_id || groupId,
                        bitrixUrl: res.bitrix_url || "",
                    });
                    if (res.group_id) groupId = res.group_id;
                    return;
                }

                chat.trackChat("tacticum_chat_error", "hero", {
                    status: result.status,
                    code: chat.getResultCode(result),
                });
                appendHeroMessage(chatArea, "ai", chat.getChatErrorMessage(result), { showOfferLink: false });
            } catch (error) {
                typing?.remove();
                chat.trackChat("tacticum_chat_error", "hero", {
                    status: "network",
                    code: "fetch_error",
                });
                appendHeroMessage(chatArea, "ai", `Ошибка запроса: ${error.message}`, { showOfferLink: false });
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
                chat.trackChat("tacticum_prefill_error", "hero", {
                    code: "missing_group_id",
                });
                appendHeroMessage(chatArea, "ai", "Не найден идентификатор обращения. Оставьте заявку вручную.", {
                    showOfferLink: false,
                });
                return;
            }

            try {
                chat.trackChat("tacticum_prefill_submit", "hero");
                const result = await chat.sendPrefillRequest(groupId);
                const nameField = document.getElementById("cta-name");
                const messageField = document.getElementById("cta-message");
                if (result.ok && result.data?.success) {
                    chat.trackChat("tacticum_prefill_success", "hero", {
                        status: result.status,
                    });
                    if (nameField) nameField.value = result.data.client_name || "";
                    if (messageField) messageField.value = result.data.summary || "";
                    window.tacticum_offer_context = { groupId: result.data.group_id, task: result.data.summary };
                } else if (messageField && lastMessage) {
                    chat.trackChat("tacticum_prefill_error", "hero", {
                        status: result.status,
                        code: result.data?.code || "unknown",
                    });
                    messageField.value = lastMessage;
                    window.tacticum_offer_context = { groupId, task: lastMessage };
                }
                document.getElementById("contact-form")?.scrollIntoView({ behavior: "smooth" });
                nameField?.focus();
            } catch (error) {
                chat.trackChat("tacticum_prefill_error", "hero", {
                    status: "network",
                    code: "fetch_error",
                });
                appendHeroMessage(chatArea, "ai", `Ошибка получения данных: ${error.message}`, {
                    showOfferLink: false,
                });
            }
        });
    };

    window.TacticumChatSurfaces.initHeroChat = initHeroChat;
})();
