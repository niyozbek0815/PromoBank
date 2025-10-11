<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FCM Test - Promobank</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        body { background: #f8f9fa; }
        .container { margin-top: 40px; }
        textarea { resize: none; }
        .msg-card img { object-fit: cover; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4 text-center">🔥 Firebase Push Notification Test</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <button id="getTokenBtn" class="btn btn-primary mb-3">📲 Get FCM Token</button>
            <p><strong>Your FCM Token:</strong></p>
            <textarea id="tokenBox" class="form-control" cols="100" rows="4" readonly></textarea>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h4>📩 Received Messages</h4>
            <ul id="messages" class="list-group"></ul>
        </div>
    </div>
</div>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging.js";

    const firebaseConfig = {
        apiKey: "{{ config('firebase.web.api_key') }}",
        authDomain: "{{ config('firebase.web.auth_domain') }}",
        projectId: "{{ config('firebase.web.project_id') }}",
        storageBucket: "{{ config('firebase.web.project_id') }}.appspot.com",
        messagingSenderId: "{{ config('firebase.web.messaging_sender_id') }}",
        appId: "{{ config('firebase.web.app_id') }}",
    };

    const vapidKey = "{{ config('firebase.web.vapid_key') }}";

    // Firebase init
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Toastr setup
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 6000
    };

    // ✅ Get token
    document.getElementById("getTokenBtn").addEventListener("click", async () => {
        try {
            const token = await getToken(messaging, { vapidKey });
            if (token) {
                document.getElementById("tokenBox").value = token;
                console.log("FCM Token:", token);
                toastr.success("✅ FCM Token olindi! Backend test route-ga yubor.", "Success");
            } else {
                toastr.warning("⚠️ Token olinmadi. Notification ruxsatini tekshir!", "Warning");
            }
        } catch (err) {
            console.error("❌ Token olishda xatolik:", err);
            toastr.error("❌ Token olishda xatolik!", "Error");
        }
    });

// 📩 Foreground listener
onMessage(messaging, (payload) => {
    const now = new Date();
    const timeStr = now.toLocaleString(); // foydalanuvchi lokal vaqti

    console.log("📩 Yangi xabar:", payload, "⏰ Received at:", timeStr);

    const notif = payload.notification || {};
    const data  = payload.data || {};

    const title = notif.title || "Promobank";
    let body    = notif.body || data.text || "";
    const image = notif.image || data.image || "/default-logo.png";
    const link  = data.link || "";

    // Agar text JSON string bo‘lsa → parse qilamiz
    try {
        if (body && (body.startsWith("{") || body.startsWith("["))) {
            const parsed = JSON.parse(body);
            body = parsed.uz || Object.values(parsed)[0] || body;
        }
    } catch {}

    // ✅ Toastr bilan chiqazish (vaqtini ham qo‘shamiz)
    toastr.info(`${body} <br><small>⏰ ${timeStr}</small>`, title);

    // ✅ UI listga chiqarish
    const li = document.createElement("li");
    li.classList.add("list-group-item", "msg-card", "py-3");
    li.innerHTML = `
        <div class="d-flex align-items-center">
            ${image ? `<img src="${image}" width="50" height="50" class="rounded me-3">` : ""}
            <div class="flex-grow-1">
                <strong class="d-block">${title}</strong>
                <small class="text-muted d-block">${body}</small>
                <small class="text-muted">⏰ ${timeStr}</small><br>
                ${link ? `<a href="${link}" target="_blank" class="small">🔗 ${data.link_type || "Link"}</a>` : ""}
            </div>
        </div>
    `;
    document.getElementById("messages").prepend(li);

    // ✅ Native browser notification
    if (Notification.permission === "granted") {
        new Notification(title, { body: `${body}\n⏰ ${timeStr}`, icon: image });
    }
});
    if (Notification.permission === "default") {
        Notification.requestPermission();
    }
</script>
</body>
</html>
