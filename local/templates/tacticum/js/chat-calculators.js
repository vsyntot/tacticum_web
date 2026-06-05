(function () {
    window.TacticumChatSurfaces = window.TacticumChatSurfaces || {};

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
        const chat = window.TacticumChatRuntime;
        const div = chat.createTextBlock(
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

        if (role !== "user" && options.showLeadHandoff && typeof options.onLeadHandoff === "function") {
            const handoffRow = document.createElement("p");
            handoffRow.className = "mt-3";
            const handoffButton = document.createElement("button");
            handoffButton.type = "button";
            handoffButton.className = "inline-flex items-center gap-2 rounded-button bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90 transition-colors";
            handoffButton.setAttribute("data-chat-lead-handoff", "true");
            handoffButton.innerHTML = '<i class="ri-arrow-down-line"></i><span></span>';
            handoffButton.querySelector("span").textContent = options.handoffLabel || "Передать вводные в заявку";
            handoffButton.addEventListener("click", (event) => {
                event.preventDefault();
                options.onLeadHandoff();
            });
            handoffRow.appendChild(handoffButton);
            div.appendChild(handoffRow);
        }

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    };

    const initDarkCalculatorChat = () => {
        const chat = window.TacticumChatRuntime;
        const section = document.querySelector("section#calculator");
        if (!chat || !section || section.dataset.tacticumChatBound === "true") return;

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
            const typing = chat.createTyping("bg-white/10 rounded-lg p-3 text-white", true);
            messages.appendChild(typing);
            messages.scrollTop = messages.scrollHeight;

            try {
                chat.trackChat("tacticum_chat_send", "dark_calculator");
                const result = await chat.sendChatMessage(message, groupId);
                const res = result.data || {};
                typing.remove();
                if (result.ok && res.response) {
                    chat.trackChat("tacticum_chat_success", "dark_calculator", {
                        status: result.status,
                        has_group_id: Boolean(res.group_id || groupId),
                    });
                    appendDarkCalculatorMessage(messages, "ai", res.response);
                    if (res.group_id) groupId = res.group_id;
                } else {
                    chat.trackChat("tacticum_chat_error", "dark_calculator", {
                        status: result.status,
                        code: chat.getResultCode(result),
                    });
                    appendDarkCalculatorMessage(messages, "ai", chat.getChatErrorMessage(result), {
                        showOfferLink: false,
                    });
                }
            } catch (error) {
                typing.remove();
                chat.trackChat("tacticum_chat_error", "dark_calculator", {
                    status: "network",
                    code: "fetch_error",
                });
                appendDarkCalculatorMessage(messages, "ai", `Ошибка запроса: ${error.message}`, {
                    showOfferLink: false,
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
        const chat = window.TacticumChatRuntime;
        if (!chat) return;

        document.querySelectorAll('[data-tacticum-chat="light"]').forEach((chatRoot) => {
            if (chatRoot.dataset.tacticumChatBound === "true") return;

            const input = chatRoot.querySelector("[data-chat-input]");
            const sendButton = chatRoot.querySelector("[data-chat-send]");
            const messages = chatRoot.querySelector("[data-chat-messages]");
            const quickReplies = chatRoot.querySelectorAll("[data-chat-quick-reply][data-message]");
            if (!input || !sendButton || !messages) return;

            chatRoot.dataset.tacticumChatBound = "true";
            const surface = chatRoot.dataset.chatSurface || "light_calculator";
            const showLeadHandoff = surface === "calculator" || surface === "price";
            let groupId = null;
            let isSending = false;

            const findLeadForm = () => {
                const contactSection = document.getElementById("contact-form");
                const sectionForm = contactSection?.querySelector("[data-tacticum-form]");
                if (sectionForm) return sectionForm;

                const expectedFormId = surface === "price" ? "price-cta" : "calculator-cta";
                return document.querySelector(`[data-tacticum-form][data-form-id="${expectedFormId}"]`);
            };

            const applyLeadHandoff = (summary, effectiveGroupId) => {
                const form = findLeadForm();
                const messageField = form?.querySelector('[name="message"]');
                if (!form || !messageField) {
                    chat.trackChat("tacticum_prefill_error", surface, {
                        code: "missing_target_form",
                    });
                    return false;
                }

                const value = (summary || "").trim();
                if (value) {
                    const existingValue = (messageField.value || "").trim();
                    if (existingValue === "") {
                        messageField.value = value;
                    } else if (!existingValue.includes(value)) {
                        messageField.value = `${existingValue}\n\n${value}`;
                    }
                    messageField.dispatchEvent(new Event("input", { bubbles: true }));
                }

                if (effectiveGroupId) {
                    form.dataset.tacticumOfferGroupId = effectiveGroupId;
                }

                document.getElementById("contact-form")?.scrollIntoView({ behavior: "smooth" });
                messageField.focus();
                return true;
            };

            const handoffToLeadForm = async ({ userMessage, assistantResponse, groupId: handoffGroupId }) => {
                let summary = chat.buildLeadHandoffSummary(surface, userMessage, assistantResponse);
                let effectiveGroupId = handoffGroupId || "";

                if (effectiveGroupId) {
                    try {
                        chat.trackChat("tacticum_prefill_submit", surface);
                        const result = await chat.sendPrefillRequest(effectiveGroupId);
                        if (result.ok && result.data?.success) {
                            summary = result.data.summary || summary;
                            effectiveGroupId = result.data.group_id || effectiveGroupId;
                            chat.trackChat("tacticum_prefill_success", surface, {
                                status: result.status,
                            });
                        } else {
                            chat.trackChat("tacticum_prefill_error", surface, {
                                status: result.status,
                                code: result.data?.code || "unknown",
                            });
                        }
                    } catch (error) {
                        chat.trackChat("tacticum_prefill_error", surface, {
                            status: "network",
                            code: "fetch_error",
                        });
                    }
                }

                if (applyLeadHandoff(summary, effectiveGroupId)) {
                    chat.trackChat("tacticum_chat_lead_handoff", surface, {
                        has_group_id: Boolean(effectiveGroupId),
                        has_prefill_summary: Boolean(summary),
                    });
                }
            };

            const send = async (rawMessage) => {
                const message = (rawMessage || "").trim();
                if (!message || isSending) return;

                input.value = "";
                isSending = true;
                sendButton.disabled = true;
                appendLightCalculatorMessage(messages, "user", message);
                const typing = chat.createTyping("ai-typing bg-primary/10 rounded-lg p-4 inline-block");
                messages.appendChild(typing);
                messages.scrollTop = messages.scrollHeight;

                try {
                    chat.trackChat("tacticum_chat_send", surface);
                    const result = await chat.sendChatMessage(message, groupId);
                    const res = result.data || {};
                    typing.remove();
                    if (result.ok && res.response) {
                        chat.trackChat("tacticum_chat_success", surface, {
                            status: result.status,
                            has_group_id: Boolean(res.group_id || groupId),
                            has_offer_url: Boolean(res.bitrix_url),
                        });
                        const nextGroupId = res.group_id || groupId;
                        appendLightCalculatorMessage(messages, "ai", res.response, {
                            bitrixUrl: res.bitrix_url || "",
                            showLeadHandoff,
                            handoffLabel: surface === "price"
                                ? "Передать вводные в заявку на команду"
                                : "Передать вводные в заявку",
                            onLeadHandoff: () => handoffToLeadForm({
                                userMessage: message,
                                assistantResponse: res.response,
                                groupId: nextGroupId,
                            }),
                        });
                        if (res.group_id) groupId = res.group_id;
                    } else {
                        chat.trackChat("tacticum_chat_error", surface, {
                            status: result.status,
                            code: chat.getResultCode(result),
                        });
                        appendLightCalculatorMessage(messages, "ai", chat.getChatErrorMessage(result), {
                            showOfferLink: false,
                        });
                    }
                } catch (error) {
                    typing.remove();
                    chat.trackChat("tacticum_chat_error", surface, {
                        status: "network",
                        code: "fetch_error",
                    });
                    appendLightCalculatorMessage(messages, "ai", `Ошибка запроса: ${error.message}`, {
                        showOfferLink: false,
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
                button.addEventListener("click", () => send(button.dataset.message || ""));
            });
        });
    };

    window.TacticumChatSurfaces.initDarkCalculatorChat = initDarkCalculatorChat;
    window.TacticumChatSurfaces.initLightCalculatorChats = initLightCalculatorChats;
})();
