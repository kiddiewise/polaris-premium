document.addEventListener("DOMContentLoaded", () => {
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const ajaxConfig = window.polaris_ajax || {};
  const hasAjax = Boolean(ajaxConfig.ajax_url && ajaxConfig.nonce);

  const body = document.body;
  const searchOverlay = $("#polarisSearchOverlay");
  const searchInput = $("#polarisSearchInput");
  const searchResults = $("#polarisSearchResults");
  const searchCloseButtons = $$(".js-search-close");
  const searchOpenButtons = $$(".js-search-open");

  const cartDrawer = $("#polarisCartDrawer");
  const miniCartEl = $("#polarisMiniCart");
  const cartCountEls = $$(".cart-count");
  const cartIcons = $$(".cart-icon");

  const toastEl = $("#polarisToast");
  const freeshipWrap = $("#polarisFreeShip");
  const freeshipText = $("#polarisFreeShipText");
  const freeshipFill = $("#polarisFreeShipFill");
  const miniCartSubtotalEl = $("#polarisMiniCartSubtotal");
  const miniCartShippingEl = $("#polarisMiniCartShipping");
  const miniCartTotalEl = $("#polarisMiniCartTotal");

  const searchState = { timer: null, controller: null };
  const cartState = new Map();

  async function parseJsonResponse(response) {
    const raw = await response.text();
    const clean = raw.replace(/^\uFEFF/, "").trim();

    if (!clean) {
      throw new Error("empty_response");
    }

    return JSON.parse(clean);
  }

  function lockBody() {
    body.classList.add("is-locked");
  }

  function unlockBody() {
    if (
      cartDrawer?.classList.contains("is-open") ||
      searchOverlay?.classList.contains("is-open") ||
      $("#polarisProductLightbox")?.classList.contains("is-open")
    ) {
      return;
    }
    body.classList.remove("is-locked");
  }

  function showToast(message) {
    if (!toastEl) return;

    toastEl.textContent = message;
    toastEl.classList.remove("hidden");
    requestAnimationFrame(() => toastEl.classList.add("is-show"));

    setTimeout(() => {
      toastEl.classList.remove("is-show");
      setTimeout(() => toastEl.classList.add("hidden"), 220);
    }, 1400);
  }

  function bumpCartIcon() {
    if (!cartIcons.length) return;

    cartIcons.forEach((icon) => {
      icon.classList.remove("cart-bump");
      void icon.offsetWidth;
      icon.classList.add("cart-bump");
    });
  }

  function updateCartCounts(count) {
    if (!cartCountEls.length) return;
    cartCountEls.forEach((countEl) => {
      countEl.textContent = String(count || 0);
    });
  }

  function setCardBusy(card, busy) {
    if (!card) return;

    $$("[data-card-plus], [data-card-minus], .js-add-to-cart", card).forEach((el) => {
      el.disabled = !!busy;
    });
  }

  function applyCardState(card, item) {
    if (!card) return;

    const addBtn = $(".js-add-to-cart", card);
    const qtyWrap = $("[data-card-qty-wrap]", card);
    const qtyVal = $("[data-card-qty]", card);

    if (!addBtn || !qtyWrap || !qtyVal) return;

    if (item && Number(item.qty) > 0) {
      addBtn.classList.add("hidden");
      qtyWrap.classList.remove("hidden");
      qtyVal.textContent = String(item.qty);
    } else {
      qtyWrap.classList.add("hidden");
      addBtn.classList.remove("hidden");
      qtyVal.textContent = "1";
    }
  }

  function syncAllProductCards() {
    $$("[data-product-card][data-product-id], .p-card[data-product-id]").forEach((card) => {
      const productId = String(card.getAttribute("data-product-id") || "");
      const item = cartState.get(productId) || null;
      applyCardState(card, item);
    });
  }

  function updateCartState(items) {
    cartState.clear();

    if (Array.isArray(items)) {
      items.forEach((item) => {
        const pid = String(item?.product_id || "");
        const qty = Number(item?.qty || 0);
        if (!pid || qty <= 0) return;

        cartState.set(pid, {
          product_id: pid,
          qty,
          cart_key: String(item.cart_key || ""),
        });
      });
    }

    syncAllProductCards();
  }

  function openSearch() {
    if (!searchOverlay) return;

    searchOverlay.classList.remove("hidden");
    requestAnimationFrame(() => searchOverlay.classList.add("is-open"));
    searchOverlay.setAttribute("aria-hidden", "false");
    lockBody();

    if (searchInput) {
      setTimeout(() => searchInput.focus(), 100);
    }
  }

  function closeSearch() {
    if (!searchOverlay) return;

    searchOverlay.classList.remove("is-open");
    searchOverlay.setAttribute("aria-hidden", "true");
    setTimeout(() => {
      searchOverlay.classList.add("hidden");
      unlockBody();
    }, 220);
  }

  function renderSearchMessage(message) {
    if (!searchResults) return;
    searchResults.innerHTML = `<div class="search-empty">${message}</div>`;
  }

  function renderSearchItems(items) {
    if (!searchResults) return;

    if (!Array.isArray(items) || items.length === 0) {
      renderSearchMessage("Sonuç bulunamadı.");
      return;
    }

    searchResults.innerHTML = items
      .map(
        (item) => `
          <a class="search-item" href="${item.url || "#"}">
            <div class="search-thumb"><img src="${item.image || ""}" alt=""></div>
            <div>
              <div class="search-title">${item.title || ""}</div>
              <div class="search-meta">${item.category || ""}</div>
            </div>
            <div class="search-price">${item.price || ""}</div>
          </a>
        `
      )
      .join("");
  }

  async function fetchSearch(query) {
    if (!hasAjax) {
      renderSearchMessage("Arama yapılandırması bulunamadı.");
      return;
    }

    if (searchState.controller) {
      searchState.controller.abort();
    }
    searchState.controller = new AbortController();

    const payload = new FormData();
    payload.append("action", "polaris_live_search");
    payload.append("nonce", ajaxConfig.nonce);
    payload.append("q", query);

    const response = await fetch(ajaxConfig.ajax_url, {
      method: "POST",
      body: payload,
      signal: searchState.controller.signal,
      credentials: "same-origin",
    });

    const data = await parseJsonResponse(response);
    if (!data?.success) {
      renderSearchMessage("Arama isteği başarısız.");
      return;
    }

    renderSearchItems(data.data);
  }

  function debounceSearch(query) {
    clearTimeout(searchState.timer);
    searchState.timer = setTimeout(async () => {
      const q = (query || "").trim();

      if (q.length < 2) {
        renderSearchMessage("En az 2 karakter yazın.");
        return;
      }

      renderSearchMessage("Aranıyor...");

      try {
        await fetchSearch(q);
      } catch (error) {
        if (error.name !== "AbortError") {
          renderSearchMessage("Arama sırasında bir hata oluştu.");
        }
      }
    }, 250);
  }

  searchOpenButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      openSearch();
    });
  });

  searchCloseButtons.forEach((button) => {
    button.addEventListener("click", closeSearch);
  });

  if (searchOverlay) {
    searchOverlay.addEventListener("click", (event) => {
      if (event.target === searchOverlay) {
        closeSearch();
      }
    });
  }

  if (searchInput) {
    renderSearchMessage("Ürün adı yazarak aramaya başlayın...");
    searchInput.addEventListener("input", (event) => debounceSearch(event.target.value));
  }

  function openCartDrawer() {
    if (!cartDrawer) return;

    cartDrawer.classList.remove("hidden");
    requestAnimationFrame(() => cartDrawer.classList.add("is-open"));
    cartDrawer.setAttribute("aria-hidden", "false");
    lockBody();
    fetchMiniCart();
  }

  function closeCartDrawer() {
    if (!cartDrawer) return;

    cartDrawer.classList.remove("is-open");
    cartDrawer.setAttribute("aria-hidden", "true");
    setTimeout(() => {
      cartDrawer.classList.add("hidden");
      unlockBody();
    }, 220);
  }

  $$("[data-cart-close]").forEach((el) => el.addEventListener("click", closeCartDrawer));

  document.addEventListener("click", (event) => {
    const cartLink = event.target.closest(".cart-icon");
    if (!cartLink || !cartDrawer) return;

    event.preventDefault();
    openCartDrawer();
  });

  function updateFreeship(freeship) {
    if (!freeshipWrap || !freeshipText || !freeshipFill) return;

    if (!freeship) {
      freeshipText.textContent = "Kargo bilgisi alınamadı.";
      freeshipFill.style.width = "0%";
      freeshipWrap.classList.remove("is-done");
      return;
    }

    const remaining = Number(freeship.remaining || 0);
    const percent = Math.max(0, Math.min(100, Number(freeship.percent || 0)));

    if (remaining <= 0) {
      freeshipText.textContent = "Tebrikler! Ücretsiz kargo kazandınız.";
      freeshipWrap.classList.add("is-done");
    } else {
      freeshipText.textContent = `Ücretsiz kargo için ${Math.ceil(remaining)} TL daha ekleyin.`;
      freeshipWrap.classList.remove("is-done");
    }

    freeshipFill.style.width = `${percent}%`;
  }

  function updateCartSummary(summary) {
    if (!miniCartSubtotalEl || !miniCartShippingEl || !miniCartTotalEl) return;

    miniCartSubtotalEl.innerHTML = summary?.subtotal || "-";
    miniCartShippingEl.innerHTML = summary?.shipping || "-";
    miniCartTotalEl.innerHTML = summary?.total || "-";
  }

  async function fetchMiniCart() {
    if (!hasAjax || !miniCartEl) return;

    const payload = new FormData();
    payload.append("action", "polaris_get_minicart");
    payload.append("nonce", ajaxConfig.nonce);

    try {
      const response = await fetch(ajaxConfig.ajax_url, {
        method: "POST",
        body: payload,
        credentials: "same-origin",
      });
      const data = await parseJsonResponse(response);

      if (!data?.success) return;

      miniCartEl.innerHTML = data.data?.html || "";
      updateCartCounts(data.data?.count || 0);
      updateFreeship(data.data?.freeship);
      updateCartSummary(data.data?.summary);
      updateCartState(data.data?.items || []);
    } catch {
      miniCartEl.innerHTML = '<div class="search-empty">Sepet yüklenemedi.</div>';
      updateCartSummary();
    }
  }

  async function setCartQuantity(cartKey, qty) {
    if (!hasAjax) {
      throw new Error("missing_ajax");
    }

    const payload = new FormData();
    payload.append("action", "polaris_set_cart_qty");
    payload.append("nonce", ajaxConfig.nonce);
    payload.append("cart_key", cartKey);
    payload.append("qty", String(qty));

    const response = await fetch(ajaxConfig.ajax_url, {
      method: "POST",
      body: payload,
      credentials: "same-origin",
    });
    const data = await parseJsonResponse(response);

    if (!data?.success) {
      throw new Error("qty_update_failed");
    }

    return data;
  }

  async function addToCart(productId, qty = 1) {
    const payload = new FormData();
    payload.append("product_id", String(productId));
    payload.append("quantity", String(qty));

    const response = await fetch("/?wc-ajax=add_to_cart", {
      method: "POST",
      body: payload,
      credentials: "same-origin",
    });
    const data = await parseJsonResponse(response).catch(() => null);

    if (data?.error) {
      throw new Error("add_to_cart_failed");
    }

    return data;
  }

  document.addEventListener("click", async (event) => {
    const addButton = event.target.closest(".js-add-to-cart");
    if (!addButton) return;

    event.preventDefault();

    const card = addButton.closest("[data-product-card], .p-card");
    const productId = addButton.getAttribute("data-product-id") || card?.getAttribute("data-product-id");
    if (!productId) return;

    setCardBusy(card, true);

    try {
      await addToCart(productId, 1);
      await fetchMiniCart();
      openCartDrawer();
      bumpCartIcon();
      showToast("Ürün sepete eklendi.");
    } catch {
      showToast("Ürün sepete eklenemedi.");
    } finally {
      setCardBusy(card, false);
    }
  });

  document.addEventListener("click", async (event) => {
    const plus = event.target.closest("[data-card-plus]");
    const minus = event.target.closest("[data-card-minus]");
    if (!plus && !minus) return;

    event.preventDefault();

    const sourceButton = plus || minus;
    const card = sourceButton?.closest("[data-product-card], .p-card");
    const productId = String(sourceButton?.getAttribute("data-product-id") || card?.getAttribute("data-product-id") || "");
    if (!productId) return;

    const current = cartState.get(productId);
    setCardBusy(card, true);

    try {
      if (plus) {
        if (current?.cart_key) {
          await setCartQuantity(current.cart_key, Number(current.qty) + 1);
        } else {
          await addToCart(productId, 1);
        }
        await fetchMiniCart();
        bumpCartIcon();
        showToast("Sepet adedi artırıldı.");
      }

      if (minus) {
        if (!current?.cart_key) {
          setCardBusy(card, false);
          return;
        }
        const nextQty = Math.max(0, Number(current.qty) - 1);
        await setCartQuantity(current.cart_key, nextQty);
        await fetchMiniCart();
        showToast(nextQty === 0 ? "Ürün sepetten kaldırıldı." : "Sepet adedi azaltıldı.");
      }
    } catch {
      showToast("Sepet güncellenemedi.");
      await fetchMiniCart();
    } finally {
      setCardBusy(card, false);
    }
  });

  document.addEventListener("click", async (event) => {
    const plus = event.target.closest("[data-qty-plus]");
    const minus = event.target.closest("[data-qty-minus]");
    const remove = event.target.closest("[data-qty-remove]");
    if (!plus && !minus && !remove) return;

    const item = event.target.closest(".polaris-minicart-item");
    if (!item) return;

    const cartKey = item.getAttribute("data-cart-key");
    const valueEl = item.querySelector("[data-qty-val]");

    if (!cartKey || !valueEl) return;

    let qty = parseInt(valueEl.textContent || "1", 10);
    if (remove) {
      qty = 0;
    } else {
      qty = plus ? qty + 1 : qty - 1;
    }
    qty = Math.max(0, qty);

    valueEl.textContent = String(qty);

    try {
      await setCartQuantity(cartKey, qty);
      await fetchMiniCart();
      showToast(qty === 0 ? "Ürün sepetten kaldırıldı." : "Sepet güncellendi.");
      bumpCartIcon();
    } catch {
      showToast("Sepet güncellenemedi.");
      await fetchMiniCart();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;

    if (searchOverlay?.classList.contains("is-open")) {
      closeSearch();
    }

    if (cartDrawer?.classList.contains("is-open")) {
      closeCartDrawer();
    }
  });

  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("active");
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15 }
  );

  $$(".fade-up").forEach((node) => revealObserver.observe(node));

  $$("[data-rail]").forEach((rail) => {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    rail.addEventListener("mousedown", (event) => {
      if (event.button !== 0) return;
      isDown = true;
      startX = event.pageX - rail.offsetLeft;
      scrollLeft = rail.scrollLeft;
      rail.classList.add("is-dragging");
    });

    rail.addEventListener("mouseleave", () => {
      isDown = false;
      rail.classList.remove("is-dragging");
    });

    rail.addEventListener("mouseup", () => {
      isDown = false;
      rail.classList.remove("is-dragging");
    });

    rail.addEventListener("mousemove", (event) => {
      if (!isDown) return;
      event.preventDefault();
      const x = event.pageX - rail.offsetLeft;
      const walk = (x - startX) * 1.2;
      rail.scrollLeft = scrollLeft - walk;
    });
  });

  const bottomNav = $(".bottom-nav");
  if (bottomNav) {
    const path = window.location.pathname.toLowerCase();
    const tabs = $$("a[data-nav]", bottomNav);

    tabs.forEach((tab) => tab.classList.remove("is-active"));

    const mark = (key) => $("a[data-nav=\"" + key + "\"]", bottomNav)?.classList.add("is-active");

    if (path === "/" || path.includes("/anasayfa") || path.includes("/home")) {
      mark("home");
    } else if (path.includes("/shop") || path.includes("/magaza") || path.includes("/product") || path.includes("/category")) {
      mark("shop");
    } else if (path.includes("/cart") || path.includes("/sepet")) {
      mark("cart");
    } else if (path.includes("/my-account") || path.includes("/hesabim")) {
      mark("account");
    } else {
      mark("home");
    }
  }

  if (hasAjax) {
    fetchMiniCart();
  }

  const cartPageForm = $(".polaris-cart-form");
  if (cartPageForm) {
    const updateButton = $("[name='update_cart']", cartPageForm);

    const commitCartUpdate = () => {
      if (!updateButton) return;
      updateButton.disabled = false;
      updateButton.click();
    };

    cartPageForm.addEventListener("click", (event) => {
      const plus = event.target.closest("[data-cart-qty-plus]");
      const minus = event.target.closest("[data-cart-qty-minus]");
      if (!plus && !minus) return;

      event.preventDefault();

      const wrap = (plus || minus).closest("[data-cart-qty]");
      const input = $("input.qty", wrap || document);
      if (!input) return;

      const current = Number(input.value || 0);
      const min = Number(input.getAttribute("min") || 0);
      const maxAttr = Number(input.getAttribute("max"));
      const max = Number.isNaN(maxAttr) || maxAttr <= 0 ? Infinity : maxAttr;
      const stepAttr = Number(input.getAttribute("step"));
      const step = Number.isNaN(stepAttr) || stepAttr <= 0 ? 1 : stepAttr;
      const precision = String(step).includes(".") ? String(step).split(".")[1].length : 0;

      let next = plus ? current + step : current - step;
      next = Math.max(min, Math.min(max, next));

      input.value = precision > 0 ? next.toFixed(precision) : String(Math.round(next));
      input.dispatchEvent(new Event("change", { bubbles: true }));
      commitCartUpdate();
    });
  }

  const checkoutPage = $(".polaris-checkout-page");
  if (checkoutPage) {
    const summary = $("[data-checkout-summary]", checkoutPage);
    const toggle = $("[data-checkout-summary-toggle]", checkoutPage);

    const setSummaryOpen = (open) => {
      if (!summary) return;
      summary.classList.toggle("is-open", open);
      toggle?.setAttribute("aria-expanded", open ? "true" : "false");
    };

    const mobileQuery = window.matchMedia("(max-width: 900px)");
    const syncCheckoutSummary = () => setSummaryOpen(!mobileQuery.matches);

    syncCheckoutSummary();
    if (typeof mobileQuery.addEventListener === "function") {
      mobileQuery.addEventListener("change", syncCheckoutSummary);
    } else if (typeof mobileQuery.addListener === "function") {
      mobileQuery.addListener(syncCheckoutSummary);
    }

    toggle?.addEventListener("click", () => {
      const isOpen = summary?.classList.contains("is-open");
      setSummaryOpen(!isOpen);
    });
  }

  const productTabs = $("#polarisProductTabs");
  if (productTabs) {
    const tabButtons = $$("[data-pd-tab-btn]", productTabs);
    const tabPanels = $$("[data-pd-tab-panel]", productTabs);

    const setActiveTab = (key) => {
      tabButtons.forEach((button) => {
        const isActive = button.getAttribute("data-pd-tab-btn") === key;
        button.classList.toggle("is-active", isActive);
        button.setAttribute("aria-selected", isActive ? "true" : "false");
      });

      tabPanels.forEach((panel) => {
        const isActive = panel.getAttribute("data-pd-tab-panel") === key;
        panel.classList.toggle("is-active", isActive);
      });
    };

    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const key = button.getAttribute("data-pd-tab-btn") || "description";
        setActiveTab(key);
      });
    });
  }

  const productGallery = $("#polarisProductGallery");
  if (productGallery) {
    const viewer = $(".pd-gallery__viewer", productGallery);
    const slides = $$(".pd-gallery__slide", productGallery);
    const thumbs = $$(".pd-gallery__thumb", productGallery);
    const prevButton = $("[data-pd-prev]", productGallery);
    const nextButton = $("[data-pd-next]", productGallery);

    const lightbox = $("#polarisProductLightbox");
    const lightboxTrack = $(".pd-lightbox__track", lightbox || document);
    const lightboxSlides = $$("[data-pd-lightbox-slide]", lightbox || document);
    const lightboxPrev = $("[data-pd-lightbox-prev]", lightbox || document);
    const lightboxNext = $("[data-pd-lightbox-next]", lightbox || document);
    const lightboxClose = $("[data-pd-lightbox-close]", lightbox || document);

    let current = 0;
    let touchStartX = 0;
    let touchDiffX = 0;
    let galleryTouchStartX = 0;
    let galleryTouchDiffX = 0;
    let preventLightboxClick = false;

    const slideCount = slides.length;

    const applyGallery = (index) => {
      if (!slideCount) return;
      current = (index + slideCount) % slideCount;

      slides.forEach((slide, idx) => {
        slide.classList.toggle("is-active", idx === current);
      });

      thumbs.forEach((thumb, idx) => {
        thumb.classList.toggle("is-active", idx === current);
      });
    };

    const applyLightbox = (index) => {
      if (!lightboxSlides.length) return;
      lightboxSlides.forEach((slide, idx) => {
        slide.classList.toggle("is-active", idx === index);
      });
    };

    const openLightbox = (index) => {
      if (!lightbox) return;

      applyGallery(index);
      applyLightbox(current);

      lightbox.classList.remove("hidden");
      requestAnimationFrame(() => lightbox.classList.add("is-open"));
      lightbox.setAttribute("aria-hidden", "false");
      lockBody();
    };

    const closeLightbox = () => {
      if (!lightbox) return;

      lightbox.classList.remove("is-open");
      lightbox.setAttribute("aria-hidden", "true");
      setTimeout(() => {
        lightbox.classList.add("hidden");
        unlockBody();
      }, 200);
    };

    slides.forEach((slide, idx) => {
      slide.addEventListener("click", () => {
        if (preventLightboxClick) return;
        openLightbox(idx);
      });
    });

    thumbs.forEach((thumb, idx) => {
      thumb.addEventListener("click", () => applyGallery(idx));
    });

    prevButton?.addEventListener("click", () => applyGallery(current - 1));
    nextButton?.addEventListener("click", () => applyGallery(current + 1));

    const showPrev = () => {
      applyGallery(current - 1);
      applyLightbox(current);
    };

    const showNext = () => {
      applyGallery(current + 1);
      applyLightbox(current);
    };

    lightboxPrev?.addEventListener("click", showPrev);
    lightboxNext?.addEventListener("click", showNext);
    lightboxClose?.addEventListener("click", closeLightbox);

    lightbox?.addEventListener("click", (event) => {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    lightboxTrack?.addEventListener("touchstart", (event) => {
      touchStartX = event.changedTouches?.[0]?.clientX || 0;
      touchDiffX = 0;
    });

    lightboxTrack?.addEventListener("touchmove", (event) => {
      const currentX = event.changedTouches?.[0]?.clientX || 0;
      touchDiffX = currentX - touchStartX;
    });

    lightboxTrack?.addEventListener("touchend", () => {
      if (Math.abs(touchDiffX) < 45) return;
      if (touchDiffX < 0) showNext();
      if (touchDiffX > 0) showPrev();
    });

    viewer?.addEventListener("touchstart", (event) => {
      galleryTouchStartX = event.changedTouches?.[0]?.clientX || 0;
      galleryTouchDiffX = 0;
    });

    viewer?.addEventListener("touchmove", (event) => {
      const currentX = event.changedTouches?.[0]?.clientX || 0;
      galleryTouchDiffX = currentX - galleryTouchStartX;
    });

    viewer?.addEventListener("touchend", () => {
      if (Math.abs(galleryTouchDiffX) < 40) return;

      preventLightboxClick = true;
      if (galleryTouchDiffX < 0) applyGallery(current + 1);
      if (galleryTouchDiffX > 0) applyGallery(current - 1);

      setTimeout(() => {
        preventLightboxClick = false;
      }, 220);
    });

    document.addEventListener("keydown", (event) => {
      if (!lightbox?.classList.contains("is-open")) return;

      if (event.key === "Escape") {
        closeLightbox();
      }

      if (event.key === "ArrowLeft") {
        showPrev();
      }

      if (event.key === "ArrowRight") {
        showNext();
      }
    });

    applyGallery(0);
  }

  const heroRoot = $("#polarisHero");
  if (heroRoot) {
    const slides = $$(".hero__slide", heroRoot);
    const dots = $$("[data-hero-dot]", heroRoot);
    const prevButton = $("[data-hero-prev]", heroRoot);
    const nextButton = $("[data-hero-next]", heroRoot);
    const autoplay = heroRoot.getAttribute("data-autoplay") === "true";
    const intervalMs = Number(heroRoot.getAttribute("data-interval") || 5000);

    let current = 0;
    let timer = null;

    function applySlide(index) {
      if (!slides.length) return;

      current = (index + slides.length) % slides.length;

      slides.forEach((slide, i) => {
        slide.classList.toggle("is-active", i === current);
      });

      dots.forEach((dot, i) => {
        dot.classList.toggle("is-active", i === current);
        dot.setAttribute("aria-selected", i === current ? "true" : "false");
      });
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (!autoplay || slides.length < 2) return;
      timer = setInterval(() => applySlide(current + 1), intervalMs);
    }

    prevButton?.addEventListener("click", () => {
      applySlide(current - 1);
      start();
    });

    nextButton?.addEventListener("click", () => {
      applySlide(current + 1);
      start();
    });

    dots.forEach((dot) => {
      dot.addEventListener("click", () => {
        const index = parseInt(dot.getAttribute("data-hero-dot") || "0", 10);
        applySlide(index);
        start();
      });
    });

    heroRoot.addEventListener("mouseenter", stop);
    heroRoot.addEventListener("mouseleave", start);

    applySlide(0);
    start();
  }
});
