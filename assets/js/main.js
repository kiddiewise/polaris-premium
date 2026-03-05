document.addEventListener("DOMContentLoaded", () => {
  // =========================================================
  // Helpers
  // =========================================================
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const hasAjaxConfig = () => !!(window.polaris_ajax?.ajax_url && window.polaris_ajax?.nonce);

  // pop anim helper (qty)
  function popQty(el) {
    if (!el) return;
    el.classList.remove("is-pop");
    // reflow to restart animation
    void el.offsetWidth;
    el.classList.add("is-pop");
  }

  // =========================================================
  // Search Overlay + AJAX Live Search
  // =========================================================
  const openBtn = $(".js-search-open");
  const closeBtn = $(".js-search-close");
  const overlay = $("#polarisSearchOverlay");
  const input = $("#polarisSearchInput");
  const results = $("#polarisSearchResults");

  const state = { timer: null, abort: null };

  function openSearch() {
    if (!overlay) return;
    overlay.classList.remove("hidden");
    requestAnimationFrame(() => overlay.classList.add("is-open"));
    document.body.classList.add("is-locked");
    overlay.setAttribute("aria-hidden", "false");
    setTimeout(() => input && input.focus(), 120);
  }

  function closeSearch() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
    document.body.classList.remove("is-locked");
    overlay.setAttribute("aria-hidden", "true");
    setTimeout(() => overlay.classList.add("hidden"), 220);
  }

  function debounce(fn, delay = 220) {
    return (...args) => {
      clearTimeout(state.timer);
      state.timer = setTimeout(() => fn(...args), delay);
    };
  }

  function renderLoading() {
    if (!results) return;
    results.innerHTML = `<div class="search-empty">Aranıyor…</div>`;
  }
  function renderEmpty(msg = "Sonuç bulunamadı.") {
    if (!results) return;
    results.innerHTML = `<div class="search-empty">${msg}</div>`;
  }
  function renderItems(items) {
    if (!results) return;
    if (!items || !items.length) return renderEmpty("Sonuç bulunamadı.");
    results.innerHTML = items
      .map(
        (it) => `
        <a class="search-item" href="${it.url}">
          <div class="search-thumb"><img src="${it.image}" alt=""></div>
          <div>
            <div class="search-title">${it.title}</div>
            <div class="search-meta">${it.category || ""}</div>
          </div>
          <div class="search-price">${it.price || ""}</div>
        </a>
      `
      )
      .join("");
  }

  const doSearch = debounce(async (q) => {
    q = (q || "").trim();
    if (!q) return renderEmpty("Ürün adı yazmaya başlayın…");
    if (!hasAjaxConfig()) return renderEmpty("Arama yapılandırması eksik.");

    if (state.abort) state.abort.abort();
    state.abort = new AbortController();

    renderLoading();

    try {
      const form = new FormData();
      form.append("action", "polaris_live_search");
      form.append("nonce", polaris_ajax.nonce);
      form.append("q", q);

      const res = await fetch(polaris_ajax.ajax_url, {
        method: "POST",
        body: form,
        signal: state.abort.signal,
      });

      const data = await res.json();
      if (!data || !data.success) return renderEmpty("Arama hatası.");
      renderItems(data.data);
    } catch (e) {
      if (e.name === "AbortError") return;
      renderEmpty("Bağlantı hatası.");
    }
  }, 240);

  if (openBtn) openBtn.addEventListener("click", openSearch);
  if (closeBtn) closeBtn.addEventListener("click", closeSearch);

  if (overlay) {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) closeSearch();
    });
  }

  if (input && results) {
    renderEmpty("Ürün adı yazmaya başlayın…");
    input.addEventListener("input", (e) => doSearch(e.target.value));
  }

  // =========================================================
  // Reveal animations (fade-up)
  // =========================================================
  const fadeElements = $$(".fade-up");
  const observer = new IntersectionObserver(
    (entries) => entries.forEach((entry) => entry.isIntersecting && entry.target.classList.add("active")),
    { threshold: 0.18 }
  );
  fadeElements.forEach((el) => observer.observe(el));

  // =========================================================
  // Bottom nav active state
  // =========================================================
  (function setBottomNavActive() {
    const nav = $(".bottom-nav");
    if (!nav) return;

    const path = window.location.pathname || "/";
    const links = $$("a[data-nav]", nav);
    links.forEach((a) => a.classList.remove("is-active"));

    const match = (key) => $(`a[data-nav="${key}"]`, nav);

    if (path === "/" || path === "/home/" || path === "/anasayfa/") match("home")?.classList.add("is-active");
    else if (path.includes("/cart") || path.includes("/sepet")) match("cart")?.classList.add("is-active");
    else if (path.includes("/my-account") || path.includes("/hesabim")) match("account")?.classList.add("is-active");
    else if (
      path.includes("/shop") ||
      path.includes("/urun") ||
      path.includes("/product") ||
      path.includes("/product-category")
    )
      match("shop")?.classList.add("is-active");
    else match("home")?.classList.add("is-active");
  })();

  // =========================================================
  // Wishlist (Like) — localStorage
  // =========================================================
  function getLikes() {
    try {
      return JSON.parse(localStorage.getItem("polaris_likes") || "[]");
    } catch {
      return [];
    }
  }
  function setLikes(arr) {
    localStorage.setItem("polaris_likes", JSON.stringify(arr));
  }
  function toggleLike(id) {
    const likes = getLikes();
    const idx = likes.indexOf(id);
    if (idx >= 0) likes.splice(idx, 1);
    else likes.push(id);
    setLikes(likes);
    return likes;
  }
  function isLiked(id) {
    return getLikes().includes(id);
  }

  $$(".js-like").forEach((btn) => {
    const id = btn.getAttribute("data-like-id");
    if (!id) return;
    if (isLiked(id)) btn.classList.add("is-liked");

    btn.addEventListener("click", () => {
      const likes = toggleLike(id);
      const liked = likes.includes(id);
      btn.classList.toggle("is-liked", liked);

      const icon = $("i", btn);
      if (icon) {
        icon.classList.toggle("fa-regular", !liked);
        icon.classList.toggle("fa-solid", liked);
      }
    });
  });

  // =========================================================
  // Share (Web Share API fallback to clipboard)
  // =========================================================
  $$(".js-share").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const url = btn.getAttribute("data-share-url") || window.location.href;
      const title = btn.getAttribute("data-share-title") || document.title || "Polaris";
      try {
        if (navigator.share) await navigator.share({ title, url });
        else {
          await navigator.clipboard.writeText(url);
          btn.classList.add("is-liked");
          setTimeout(() => btn.classList.remove("is-liked"), 650);
        }
      } catch {}
    });
  });

  // =========================================================
  // Rail drag-to-scroll (desktop) + prevent accidental click
  // =========================================================
  $$("[data-rail]").forEach((rail) => {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;
    let moved = false;

    rail.addEventListener("mousedown", (e) => {
      if (e.button !== 0) return;
      isDown = true;
      moved = false;
      startX = e.pageX - rail.offsetLeft;
      scrollLeft = rail.scrollLeft;
      rail.classList.add("is-dragging");
    });

    rail.addEventListener("mouseleave", () => {
      isDown = false;
      rail.classList.remove("is-dragging");
    });

    rail.addEventListener("mouseup", () => {
      isDown = false;
      setTimeout(() => (moved = false), 0);
      rail.classList.remove("is-dragging");
    });

    rail.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - rail.offsetLeft;
      const walk = (x - startX) * 1.35;
      if (Math.abs(walk) > 6) moved = true;
      rail.scrollLeft = scrollLeft - walk;
    });

    rail.addEventListener("click", (e) => {
      if (!moved) return;
      const a = e.target.closest("a");
      if (a) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });

  // =========================================================
  // Mini-cart drawer + Toast + Add-to-cart UX
  // =========================================================
  const cartDrawer = $("#polarisCartDrawer");
  const miniCartEl = $("#polarisMiniCart");
  const toastEl = $("#polarisToast");
  const cartCountEl = $(".cart-count");
  const cartIcon = $(".cart-icon");

  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.remove("hidden");
    requestAnimationFrame(() => toastEl.classList.add("is-show"));
    setTimeout(() => {
      toastEl.classList.remove("is-show");
      setTimeout(() => toastEl.classList.add("hidden"), 240);
    }, 1400);
  }

  function bumpCartIcon() {
    if (!cartIcon) return;
    cartIcon.classList.remove("cart-bump");
    void cartIcon.offsetWidth;
    cartIcon.classList.add("cart-bump");
  }

  async function fetchMiniCart() {
    if (!miniCartEl || !hasAjaxConfig()) return;

    try {
      const form = new FormData();
      form.append("action", "polaris_get_minicart");
      form.append("nonce", polaris_ajax.nonce);

      const res = await fetch(polaris_ajax.ajax_url, { method: "POST", body: form });
      const data = await res.json();
      if (!data || !data.success) return;

      miniCartEl.innerHTML = data.data.html;

      if (cartCountEl) cartCountEl.textContent = data.data.count ?? "0";

      const fs = data.data?.freeship;
      const fsText = $("#polarisFreeShipText");
      const fsFill = $("#polarisFreeShipFill");
      const fsWrap = $("#polarisFreeShip");

      if (fsText && fsFill && fsWrap) {
        if (!fs) {
          fsText.textContent = "Kargo durumu hesaplanamadı.";
          fsFill.style.width = "0%";
          fsWrap.classList.remove("is-done");
        } else {
          const remaining = Number(fs.remaining || 0);
          const percent = Number(fs.percent || 0);

          if (remaining <= 0) {
            fsText.textContent = "Tebrikler! Ücretsiz kargo kazandınız 🎉";
            fsWrap.classList.add("is-done");
          } else {
            fsText.textContent = `${Math.ceil(remaining)}₺ daha ekleyin → Ücretsiz kargo`;
            fsWrap.classList.remove("is-done");
          }
          fsFill.style.width = `${Math.max(0, Math.min(100, percent))}%`;
        }
      }
    } catch {}
  }

  function openCartDrawer() {
    if (!cartDrawer) return;
    cartDrawer.classList.remove("hidden");
    requestAnimationFrame(() => cartDrawer.classList.add("is-open"));
    document.body.classList.add("is-locked");
    cartDrawer.setAttribute("aria-hidden", "false");
    fetchMiniCart();
  }

  function closeCartDrawer() {
    if (!cartDrawer) return;
    cartDrawer.classList.remove("is-open");
    document.body.classList.remove("is-locked");
    cartDrawer.setAttribute("aria-hidden", "true");
    setTimeout(() => cartDrawer.classList.add("hidden"), 220);
  }

  $$("[data-cart-close]").forEach((el) => el.addEventListener("click", closeCartDrawer));

  document.addEventListener("click", (e) => {
    const cartIconLink = e.target.closest(".cart-icon");
    if (!cartIconLink) return;
    if (!cartDrawer) return;
    e.preventDefault();
    openCartDrawer();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    if (overlay && overlay.classList.contains("is-open")) closeSearch();
    if (cartDrawer && cartDrawer.classList.contains("is-open")) closeCartDrawer();
  });

  // Woo add_to_cart via wc-ajax (fast)
  async function addToCart(productId, qty = 1) {
    const url = `/?wc-ajax=add_to_cart`;
    const form = new FormData();
    form.append("product_id", String(productId));
    form.append("quantity", String(qty));

    const res = await fetch(url, { method: "POST", body: form });

    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      return { success: true, raw: text };
    }
  }

  // =========================================================
  // CTA -> Qty Pill (morph) + Pop anim
  // =========================================================
  function ensureCardStepper(cardEl, initialQty = 1) {
    const body = cardEl?.querySelector(".p-card__body");
    const cta = cardEl?.querySelector(".p-card__cta");
    if (!body) return;

    // already converted
    const existing = body.querySelector(".p-qty");
    if (existing) {
      const val = existing.querySelector("[data-card-qty]");
      if (val) {
        val.textContent = String(initialQty);
        popQty(val);
      }
      return;
    }

    // Morph-out CTA (do not remove immediately to avoid layout jump)
    if (cta) {
      cta.classList.add("is-morph-out");
      cta.style.pointerEvents = "none";
    }

    const wrap = document.createElement("div");
    wrap.className = "p-card__cta-wrap";
    wrap.innerHTML = `
      <div class="p-qty is-morph-in" role="group" aria-label="Adet">
        <button class="p-qty__btn" type="button" data-card-minus aria-label="Azalt">
          <i class="fa-solid fa-minus"></i>
        </button>

        <div class="p-qty__mid">
          <span class="p-qty__label">Sepette</span>
          <span class="p-qty__val" data-card-qty>${initialQty}</span>
        </div>

        <button class="p-qty__btn" type="button" data-card-plus aria-label="Arttır">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
    `;

    body.appendChild(wrap);

    // remove CTA after its animation ends (or fallback timeout)
    if (cta) {
      const kill = () => {
        if (cta && cta.parentNode) cta.remove();
      };
      cta.addEventListener("animationend", kill, { once: true });
      setTimeout(kill, 260);
    }

    // pop initial qty
    const qtyEl = wrap.querySelector("[data-card-qty]");
    popQty(qtyEl);
  }

  // Add-to-cart clicks on cards
  document.addEventListener("click", async (e) => {
    const atc = e.target.closest(".p-card__cta.ajax_add_to_cart");
    if (!atc) return;

    e.preventDefault();

    const productId = atc.getAttribute("data-product_id");
    const cardEl = atc.closest(".p-card");
    if (!productId || !cardEl) return;

    atc.style.pointerEvents = "none";
    atc.style.opacity = "0.92";

    try {
      await addToCart(productId, 1);
      toast("Sepete eklendi.");
      bumpCartIcon();

      // morph CTA -> pill smoothly
      ensureCardStepper(cardEl, 1);

      await fetchMiniCart();

      // istersen kapat: drawer otomatik açılmasın
      openCartDrawer();
    } catch {
      toast("Sepete eklenemedi.");
    } finally {
      atc.style.pointerEvents = "";
      atc.style.opacity = "";
    }
  });

  // Card stepper (+/-)
  document.addEventListener("click", async (e) => {
    const plus = e.target.closest("[data-card-plus]");
    const minus = e.target.closest("[data-card-minus]");
    if (!plus && !minus) return;

    const cardEl = e.target.closest(".p-card");
    if (!cardEl) return;

    const productId = cardEl.getAttribute("data-product-id");
    if (!productId) return;

    const qtyEl = cardEl.querySelector("[data-card-qty]");
    let current = qtyEl ? parseInt(qtyEl.textContent || "1", 10) : 1;

    if (plus) {
      current += 1;
      if (qtyEl) {
        qtyEl.textContent = String(current);
        popQty(qtyEl); // ✅ mini pop anim
      }

      // micro feedback: disable plus briefly to avoid double taps
      plus.disabled = true;
      setTimeout(() => (plus.disabled = false), 220);

      try {
        await addToCart(productId, 1);
        toast("Adet arttırıldı.");
        bumpCartIcon();
        await fetchMiniCart();
      } catch {
        toast("Güncellenemedi.");
        await fetchMiniCart();
      }
      return;
    }

    // Minus: can't safely decrement without cart_key (open drawer)
    toast("Azaltmak için sepeti açtık.");
    openCartDrawer();
  });

  // Drawer qty +/- updates real cart quantities
  async function setCartQty(cartKey, qty) {
    if (!hasAjaxConfig()) throw new Error("no_ajax_config");

    const form = new FormData();
    form.append("action", "polaris_set_cart_qty");
    form.append("nonce", polaris_ajax.nonce);
    form.append("cart_key", cartKey);
    form.append("qty", String(qty));

    const res = await fetch(polaris_ajax.ajax_url, { method: "POST", body: form });
    const data = await res.json();
    if (!data?.success) throw new Error("set_qty_failed");
    return data;
  }

  document.addEventListener("click", async (e) => {
    const plus = e.target.closest("[data-qty-plus]");
    const minus = e.target.closest("[data-qty-minus]");
    if (!plus && !minus) return;

    const item = e.target.closest(".polaris-minicart-item");
    if (!item) return;

    const cartKey = item.getAttribute("data-cart-key");
    const valEl = item.querySelector("[data-qty-val]");
    if (!cartKey || !valEl) return;

    let qty = parseInt(valEl.textContent || "1", 10);
    qty = plus ? qty + 1 : qty - 1;
    qty = Math.max(0, qty);

    valEl.textContent = String(qty);

    try {
      await setCartQty(cartKey, qty);
      await fetchMiniCart();
      toast(qty === 0 ? "Ürün sepetten kaldırıldı." : "Sepet güncellendi.");
      bumpCartIcon();
    } catch {
      toast("Sepet güncellenemedi.");
      await fetchMiniCart();
    }
  });

  // =========================================================
  // Hero Slider (2026 Modern)
  // =========================================================
  const heroSlider = $("#polarisHeroSlider");
  if (heroSlider) {
    const slides = $$(".polaris-hero-slide", heroSlider);
    const indicators = $$(".polaris-hero-indicator", heroSlider);
    const prevBtn = $(".polaris-hero-btn--prev", heroSlider);
    const nextBtn = $(".polaris-hero-btn--next", heroSlider);
    const autoplay = heroSlider.getAttribute("data-autoplay") === "true";

    let currentIndex = 0;
    let autoplayTimer = null;

    function goToSlide(index) {
      if (slides.length === 0) return;
      
      // Normalize index
      currentIndex = ((index % slides.length) + slides.length) % slides.length;
      
      // Update slides
      slides.forEach((slide, idx) => {
        slide.classList.toggle("is-active", idx === currentIndex);
      });

      // Update indicators
      indicators.forEach((indicator, idx) => {
        indicator.classList.toggle("is-active", idx === currentIndex);
        indicator.setAttribute("aria-selected", idx === currentIndex ? "true" : "false");
      });
    }

    function nextSlide() {
      goToSlide(currentIndex + 1);
      resetAutoplay();
    }

    function prevSlide() {
      goToSlide(currentIndex - 1);
      resetAutoplay();
    }

    function resetAutoplay() {
      if (autoplayTimer) clearInterval(autoplayTimer);
      if (autoplay) {
        autoplayTimer = setInterval(nextSlide, 4500);
      }
    }

    // Set initial active slide
    goToSlide(0);

    // Attach click handlers
    if (prevBtn) prevBtn.addEventListener("click", prevSlide);
    if (nextBtn) nextBtn.addEventListener("click", nextSlide);

    indicators.forEach((indicator, idx) => {
      indicator.addEventListener("click", () => {
        goToSlide(idx);
        resetAutoplay();
      });
    });

    // Keyboard nav (left/right arrows)
    document.addEventListener("keydown", (e) => {
      if (!heroSlider.closest("body")) return; // ensure visible
      if (e.key === "ArrowLeft") prevSlide();
      if (e.key === "ArrowRight") nextSlide();
    });

    // Start autoplay
    resetAutoplay();
  }
});