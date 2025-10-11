
(function () {
    const loader = document.getElementById('siteLoader');
    const loaderText = document.getElementById('loaderText');

    // Min va max vaqtlar (millisekundlarda)
    const minDisplay = 1500; // 2s dan oldin yopilmaydi
    const maxWait = 6000; // 6s dan keyin yopiladi

    let shownAt = Date.now();
    let isHidden = false;

    function hideLoader(force = false) {
        if (!loader || isHidden) return;
        const elapsed = Date.now() - shownAt;

        // agar force=true yoki max kutish vaqti o'tgan bo'lsa
        if (force || elapsed >= minDisplay) {
            // loaderni yopish
            loader.setAttribute('aria-hidden', 'true');
            loader.removeAttribute('data-visible');

            // Fade animatsiyadan keyin DOMdan olib tashlash
            setTimeout(() => {
                try { loader.remove(); } catch (e) { }
            }, 400);

            isHidden = true;
        } else {
            // min vaqt to'lmaguncha kutib turish
            setTimeout(() => hideLoader(true), minDisplay - elapsed);
        }
    }

    // Page load event
    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', () => hideLoader(), { once: true, passive: true });
    }

    // Max kutish fallback
    setTimeout(() => {
        if (!isHidden) {
            loaderText && (loaderText.textContent = 'Yuklanish davom etmoqda…');
            hideLoader(true);
        }
    }, maxWait);
})();

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('languageSwitcher').addEventListener('change', function () {
        window.location.href = '/lang/' + this.value;
    });
    // 1️⃣ Layout qayta hisoblash
    function recalcLayout() {
        const download = document.getElementById("download");
        const promoLastChild = document.querySelector(".promo-download .content>.container-sm:last-child");

        if (download && promoLastChild) {
            const top = parseInt(window.getComputedStyle(download).top, 10) || 0;
            const x = download.offsetHeight - Math.abs(top);
            promoLastChild.style.marginTop = `${x}px`;
        }

        document.querySelectorAll("[data-relative]").forEach(relative => {
            const content = relative.querySelector("[data-content]");
            if (content) relative.style.minHeight = content.offsetHeight + "px";
        });
    }

    // 2️⃣ ScrollTop tugmasi
    const scrollBtn = document.querySelector(".scrollTop");
    if (scrollBtn) {
        window.addEventListener("scroll", () => {
            scrollBtn.classList.toggle("show", window.scrollY > 300);
        });
        scrollBtn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // 3️⃣ Portfolio hover effect
    document.querySelectorAll(".portfolio-item .img-wrap").forEach(wrap => {
        const img = wrap.querySelector("img");
        wrap.addEventListener("mouseenter", () => {
            const wrapHeight = wrap.offsetHeight;
            const imgHeight = img.naturalHeight * (wrap.offsetWidth / img.naturalWidth);
            const moveDistance = imgHeight - wrapHeight;
            if (moveDistance > 0) {
                img.style.transitionDuration = `${moveDistance / 50}s`;
                img.style.transform = `translateY(-${moveDistance}px)`;
            }
        });
        wrap.addEventListener("mouseleave", () => {
            img.style.transitionDuration = "2s";
            img.style.transform = "translateY(0)";
        });
    });



    // 5️⃣ Benefit grid builder
    function buildBenefitGrid() {
        const container = document.querySelector(".benefit-card");
        const items = [...document.querySelectorAll(".benefit-item")];
        if (!container || !items.length) return;

        container.innerHTML = "";
        let columnsCount = window.innerWidth <= 576 ? 1 : window.innerWidth <= 992 ? 2 : 3;
        const columns = Array.from({ length: columnsCount }, () => {
            const col = document.createElement("div");
            col.classList.add("column");
            container.appendChild(col);
            return col;
        });

        items.forEach((item, i) => {
            if (columnsCount === 1) item.style.height = "auto";
            columns[i % columnsCount].appendChild(item);
        });
    }
    buildBenefitGrid();

    // 6️⃣ Menu overlay
    const menuOverlay = document.getElementById("menuOverlay");

    // 🔓 Menu ochish
    document.querySelector(".btn_bars")?.addEventListener("click", () =>
        menuOverlay.classList.add("active")
    );

    // ❌ Close tugmasi
    document.getElementById("closeMenu")?.addEventListener("click", () =>
        menuOverlay.classList.remove("active")
    );

    // 🖱 Overlayning bo‘sh joyini bosganda yopish
    menuOverlay?.addEventListener("click", e => {
        if (e.target === menuOverlay) menuOverlay.classList.remove("active");
    });

    // 🔗 Menu ichidagi link bosilganda yopish
    document.querySelectorAll("#menuOverlay .menu .nav-link, #menuOverlay .menu .btn_social")
        .forEach(link => {
            link.addEventListener("click", () => menuOverlay.classList.remove("active"));
        });
    // 7️⃣ Scene parallax
    document.querySelectorAll(".scene").forEach(scene => {
        const container = scene.closest("section")?.querySelector(".content") || scene;
        const items = scene.querySelectorAll("div");
        let targetX = 0, targetY = 0, currentX = 0, currentY = 0;
        let moveRange = window.innerWidth >= 768 ? 30 : 15;

        window.addEventListener("resize", () => {
            moveRange = window.innerWidth >= 768 ? 30 : 15;
            recalcLayout();
            buildBenefitGrid();
        });

        container.addEventListener("mousemove", e => {
            const rect = scene.getBoundingClientRect();
            const percentX = (e.clientX - rect.left) / rect.width;
            const percentY = (e.clientY - rect.top) / rect.height;
            targetX = -(percentX - 0.5) * moveRange * 2;
            targetY = -(percentY - 0.5) * moveRange * 2;
        });

        (function animate() {
            currentX += (targetX - currentX) * 0.1;
            currentY += (targetY - currentY) * 0.1;
            items.forEach(el => el.style.transform = `translate(${currentX}px, ${currentY}px)`);
            requestAnimationFrame(animate);
        })();
    });

    // 🚀 Init
    recalcLayout();
});

// Promopage js codelari


// 4️⃣ Sponsors carousel (OwlCarousel)
function initCarousel() {
    const $owl = $(".sponsors-list");
    $owl.owlCarousel({
        loop: true,
        margin: 20,
        autoplay: true,
        autoplayTimeout: 15000,
        autoplayHoverPause: true,
        slideTransition: "linear",
        smartSpeed: 15000,
        responsive: {
            0: { items: 1 }, // juda kichik ekranlar
            576: { items: 2 }, // telefon / kichik planshet
            768: { items: 3 }, // planshet
            1200: { items: Math.max(1, Math.floor(window.innerWidth / 320)) } // katta ekranlarda dinamik
        }
    });

    // carousel to'g'ri boshlanishi uchun
    setTimeout(() => $owl.trigger("next.owl.carousel"), 50);
}
initCarousel();
$(document).ready(function () {
    $(".media-gallery").owlCarousel({
        items: 3,
        margin: 40,
        loop: true,
        nav: true,
        dots: false,
        center: true,
        autoplay: true,
        autoplayTimeout: 6000,   // 4s harakat oralig‘i
        autoplayHoverPause: true, // hoverda to‘xtaydi
        smartSpeed: 2000,
        responsive: {
            0: { items: 2 },
            600: { items: 2 },
            1000: { items: 3 }
        }
    });
    const modal = $("#mediaModal");
    const modalBody = $(".modal-body");
    const items = $(".gallery-item");
    let currentIndex = 0;

    function openModal(index) {
        currentIndex = index;
        const item = items.eq(index);
        const type = item.data("type");
        const src = item.data("src");

        modalBody.empty();

        if (type === "image") {
            modalBody.html(`<img src="${src}" alt="media" />`);
        }
        else if (type === "video") {
            modalBody.html(`
                <video controls autoplay>
                    <source src="${src}" type="video/mp4">
                    Sizning brauzeringiz video formatini qo‘llab-quvvatlamaydi.
                </video>
            `);
        }
        else if (type === "youtube") {
            // YouTube linkdan ID olish (ikkita variantni ham qo‘llab-quvvatlaydi)
            let videoId = null;
            if (src.includes("youtube.com/watch?v=")) {
                videoId = src.split("v=")[1]?.split("&")[0];
            } else if (src.includes("youtu.be/")) {
                videoId = src.split("youtu.be/")[1]?.split("?")[0];
            }
            if (videoId) {
                modalBody.html(`
                    <iframe width="100%" height="480"
                            src="https://www.youtube.com/embed/${videoId}?autoplay=1"
                            frameborder="0"
                            allow="autoplay; encrypted-media"
                            allowfullscreen>
                    </iframe>
                `);
            } else {
                modalBody.html(`<p style="color:#fff">Noto‘g‘ri YouTube URL</p>`);
            }
        }

        modal.fadeIn(200);
    }

    // Modal ochish
    items.on("click", function () {
        openModal(items.index(this));
    });

    // Yopish tugmasi
    $(".close").on("click", function () {
        modal.fadeOut(200, () => modalBody.empty());
    });

    // Prev/Next
    $(".prev").on("click", function (e) {
        e.stopPropagation();
        openModal((currentIndex - 1 + items.length) % items.length);
    });
    $(".next").on("click", function (e) {
        e.stopPropagation();
        openModal((currentIndex + 1) % items.length);
    });

    // Modal tashqarisiga bosganda yopish
    modal.on("click", function (e) {
        if ($(e.target).is(".media-modal")) {
            modal.fadeOut(200, () => modalBody.empty());
        }
    });

    // ESC tugmasi bilan yopish
    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && modal.is(":visible")) {
            modal.fadeOut(200, () => modalBody.empty());
        }
    });
});

function adjustBannerHeight() {
    const banner = document.querySelector(".banner");
    const footer = document.querySelector(".footer");
    if (!banner || !footer) return;

    const footerHeight = footer.offsetHeight;
    banner.style.minHeight = `calc(100vh - ${footerHeight}px)`;
}

// Bir marta chaqiramiz
document.addEventListener("DOMContentLoaded", adjustBannerHeight);

// Resize bo‘lganda qayta hisoblaymiz
window.addEventListener("resize", adjustBannerHeight, {
    passive: true
});
