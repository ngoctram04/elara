/* ==============================
   HELPER
============================== */
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
}

/* ==============================
   MỞ / ĐÓNG AI CHAT
============================== */
window.toggleAIChat = function () {
    const box = document.getElementById("ai-chat-box");
    const chat = document.getElementById("ai-messages");

    if (!box || !chat) return;

    if (box.style.display === "flex") {
        box.style.display = "none";
    } else {
        box.style.display = "flex";

        if (chat.innerHTML.trim() === "") {
            chat.innerHTML = `
                <div><b>AI:</b> Xin chào! Tôi là trợ lý ELARA.
                Bạn cần tư vấn mỹ phẩm gì?</div>
            `;
        }
    }
};

/* ==============================
   GỬI CÂU HỎI AI
============================== */
window.sendAI = function () {
    const input = document.getElementById("ai-input");
    const chat = document.getElementById("ai-messages");

    if (!input || !chat) return;

    const msg = input.value.trim();
    if (!msg) return;

    chat.innerHTML += `<div><b>Bạn:</b> ${escapeHtml(msg)}</div>`;

    input.value = "";
    chat.scrollTop = chat.scrollHeight;

    const loading = document.createElement("div");
    loading.innerHTML = "<b>AI:</b> Đang tư vấn...";
    chat.appendChild(loading);

    fetch("/ai-chat/send", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({ message: msg })
    })
        .then(async (res) => {
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.reply || "Server error");
            }

            return data;
        })
        .then((data) => {
            loading.remove();

            chat.innerHTML += `<div class="ai-msg"><b>AI:</b> ${data.reply}</div>`;

            if (data.products && Array.isArray(data.products)) {
                data.products.forEach((p) => {
                    chat.innerHTML += `
                        <div style="display:flex;gap:8px;margin-top:6px;">
                            <img src="${p.image}" style="width:50px;height:50px;border-radius:6px;object-fit:cover;">
                            <div>
                                <a href="${p.url}" target="_blank">${p.name}</a>
                                <div style="color:#e74c3c;font-weight:600;">
                                    ${p.formatted_price}
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            chat.scrollTop = chat.scrollHeight;
        })
        .catch((err) => {
            loading.innerHTML = `<b>AI:</b> ${escapeHtml(err.message)}`;
        });
};

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("search-input");
    const box = document.getElementById("search-suggest-box");
    const voiceBtn = document.getElementById("voice-btn");
    const voicePopup = document.getElementById("voice-popup");
    const aiInput = document.getElementById("ai-input");
    const markAllBtn = document.getElementById("markAllRead");
    const unreadChatUrl = document.body.dataset.chatUnreadUrl;
    const markAllReadUrl = document.body.dataset.markAllReadUrl;
    const isLoggedIn = document.body.dataset.auth === "1";

    /* ==============================
       LOAD HISTORY
    ============================== */
    function loadHistory() {
        if (!box) return;

        fetch("/search/history")
            .then((res) => res.json())
            .then((data) => {
                box.innerHTML = "";

                if (!data.length) {
                    box.style.display = "none";
                    return;
                }

                [...new Set(data)].forEach((item) => {
                    box.innerHTML += `
                        <div class="search-history-item history-row">
                            <div class="history-left suggest-item">
                                <i class="bi bi-clock"></i>
                                <span>${item}</span>
                            </div>

                            <span class="delete-history" data-key="${item}">
                                <i class="bi bi-x"></i>
                            </span>
                        </div>
                    `;
                });

                box.style.display = "block";
            });
    }

    /* ==============================
       SEARCH AUTOCOMPLETE
    ============================== */
    if (input && box) {
        input.addEventListener("focus", loadHistory);

        input.addEventListener("input", function () {
            const q = this.value.trim();

            if (q.length === 0) {
                loadHistory();
                return;
            }

            fetch(`/search/suggest?q=${encodeURIComponent(q)}`)
                .then((res) => res.json())
                .then((data) => {
                    box.innerHTML = "";

                    if (data.length === 0) {
                        box.style.display = "none";
                        return;
                    }

                    data.forEach((item) => {
                        box.innerHTML += `
                            <div class="search-history-item suggest-item">
                                ${item}
                            </div>
                        `;
                    });

                    box.style.display = "block";
                });
        });
    }

    /* ==============================
       CHAT NHÂN VIÊN - UNREAD BADGE
    ============================== */
    function loadUnreadChat() {
        if (!isLoggedIn || !unreadChatUrl) return;

        fetch(unreadChatUrl)
            .then((res) => res.json())
            .then((data) => {
                const badge = document.getElementById("chat-badge");
                if (!badge) return;

                if (data.count > 0) {
                    badge.innerText = data.count;
                    badge.style.display = "block";
                } else {
                    badge.style.display = "none";
                }
            });
    }

    if (isLoggedIn && unreadChatUrl) {
        loadUnreadChat();
        setInterval(loadUnreadChat, 5000);
    }

    /* ==============================
       VOICE SEARCH
    ============================== */
    if (voiceBtn && voicePopup && input) {
        voiceBtn.addEventListener("click", () => {
            const SpeechRecognition =
                window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                alert("Trình duyệt không hỗ trợ tìm kiếm giọng nói");
                return;
            }

            const recognition = new SpeechRecognition();

            recognition.lang = "vi-VN";
            recognition.interimResults = false;
            recognition.start();

            voicePopup.style.display = "flex";
            voiceBtn.innerHTML = '<i class="bi bi-mic-fill text-danger"></i>';

            const silenceTimer = setTimeout(() => {
                recognition.stop();
            }, 5000);

            recognition.onresult = function (event) {
                clearTimeout(silenceTimer);

                const text = event.results[0][0].transcript;
                input.value = text;

                voicePopup.style.display = "none";
                input.form.submit();
            };

            recognition.onend = function () {
                voicePopup.style.display = "none";
                voiceBtn.innerHTML = '<i class="bi bi-mic"></i>';
            };
        });
    }

    /* ==============================
       MARK ALL NOTIFICATIONS AS READ
    ============================== */
    if (markAllBtn && markAllReadUrl) {
        markAllBtn.addEventListener("click", function () {
            fetch(markAllReadUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
                }
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        location.reload();
                    }
                });
        });
    }

    /* ==============================
       GLOBAL CLICK EVENTS
    ============================== */
    document.addEventListener("click", function (e) {
        const deleteBtn = e.target.closest(".delete-history");
        if (deleteBtn) {
            e.stopPropagation();

            const key = deleteBtn.dataset.key;
            const row = deleteBtn.closest(".history-row");

            if (row) {
                row.style.transition = "all .2s ease";
                row.style.opacity = "0";
                row.style.transform = "translateX(10px)";

                setTimeout(() => {
                    row.remove();
                }, 200);
            }

            fetch("/search/history/delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
                },
                body: JSON.stringify({ keyword: key })
            });

            return;
        }

        const suggestItem = e.target.closest(".suggest-item");
        if (suggestItem && input) {
            const keyword = suggestItem.innerText.trim();
            input.value = keyword;
            input.form.submit();
            return;
        }

        if (!e.target.closest(".search-pill") && box) {
            box.style.display = "none";
        }
    });

    /* ==============================
       ENTER TO SEND AI CHAT
    ============================== */
    if (aiInput) {
        aiInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                window.sendAI();
            }
        });
    }
});
function loadUnreadChat() {
    fetch('/chat/unread-count')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('chat-badge');

            if (!badge) return;

            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
}

// load lần đầu
loadUnreadChat();

// auto refresh mỗi 3s
setInterval(loadUnreadChat, 3000);