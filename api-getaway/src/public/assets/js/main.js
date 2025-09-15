

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
                0: { items: 2 },
                576: { items: 2 },
                768: { items: 3 },
                1200: { items: Math.floor(window.innerWidth / 320) } // 300+20 margin
            }
        });
        setTimeout(() => $owl.trigger("next.owl.carousel"), 50);
    }
    initCarousel();

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
    document.querySelector(".btn_bars")?.addEventListener("click", () => menuOverlay.classList.add("active"));
    document.getElementById("closeMenu")?.addEventListener("click", () => menuOverlay.classList.remove("active"));
    menuOverlay?.addEventListener("click", e => { if (e.target === menuOverlay) menuOverlay.classList.remove("active"); });

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
