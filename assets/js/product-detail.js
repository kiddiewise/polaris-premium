document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector(".pd-shell");
  if (!root) return;

  const accordion = root.querySelector("[data-pd-accordion]");
  const setAccordionState = (trigger, open) => {
    const panelId = trigger.getAttribute("aria-controls");
    const panel = panelId ? document.getElementById(panelId) : null;
    const item = trigger.closest(".pd-accordion__item");
    if (!panel || !item) return;

    trigger.setAttribute("aria-expanded", open ? "true" : "false");
    panel.hidden = !open;
    item.classList.toggle("is-open", open);
  };

  accordion?.querySelectorAll("[data-pd-accordion-trigger]").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      setAccordionState(trigger, trigger.getAttribute("aria-expanded") !== "true");
    });
  });

  root.querySelectorAll("[data-pd-open-accordion]").forEach((link) => {
    link.addEventListener("click", () => {
      const key = link.getAttribute("data-pd-open-accordion");
      const trigger = accordion?.querySelector(`[data-pd-accordion-trigger="${key}"]`);
      if (!trigger) return;
      setAccordionState(trigger, true);
      window.setTimeout(() => trigger.focus({ preventScroll: true }), 0);
    });
  });

  if (["#reviews", "#comments", "#review_form"].includes(window.location.hash)) {
    const reviewsTrigger = accordion?.querySelector('[data-pd-accordion-trigger="reviews"]');
    if (reviewsTrigger) setAccordionState(reviewsTrigger, true);
  }

  const gallery = document.getElementById("polarisProductGalleryV2");
  if (!gallery) return;

  const viewer = gallery.querySelector(".pd-gallery__viewer");
  const slides = Array.from(gallery.querySelectorAll("[data-pd-slide]"));
  const thumbs = Array.from(gallery.querySelectorAll("[data-pd-thumb]"));
  const dots = Array.from(gallery.querySelectorAll("[data-pd-dot]"));
  const status = gallery.querySelector("[data-pd-gallery-status]");
  const previousButton = gallery.querySelector("[data-pd-prev]");
  const nextButton = gallery.querySelector("[data-pd-next]");
  const lightbox = document.getElementById("polarisProductLightbox");
  const lightboxSlides = Array.from(lightbox?.querySelectorAll("[data-pd-lightbox-slide]") || []);
  const lightboxTrack = lightbox?.querySelector(".pd-lightbox__track");
  const lightboxClose = lightbox?.querySelector("[data-pd-lightbox-close]");
  const lightboxPrevious = lightbox?.querySelector("[data-pd-lightbox-prev]");
  const lightboxNext = lightbox?.querySelector("[data-pd-lightbox-next]");

  let current = 0;
  let galleryStartX = 0;
  let galleryDiffX = 0;
  let lightboxStartX = 0;
  let lightboxDiffX = 0;
  let suppressOpen = false;
  let returnFocus = null;

  const normalizeIndex = (index) => {
    if (!slides.length) return 0;
    return (index + slides.length) % slides.length;
  };

  const applySlides = (index, announce = true) => {
    current = normalizeIndex(index);

    slides.forEach((slide, slideIndex) => {
      const active = slideIndex === current;
      slide.classList.toggle("is-active", active);
      slide.setAttribute("aria-hidden", active ? "false" : "true");
      slide.tabIndex = active ? 0 : -1;
    });

    thumbs.forEach((thumb, thumbIndex) => {
      const active = thumbIndex === current;
      thumb.classList.toggle("is-active", active);
      thumb.setAttribute("aria-current", active ? "true" : "false");
    });

    dots.forEach((dot, dotIndex) => {
      const active = dotIndex === current;
      dot.classList.toggle("is-active", active);
      dot.setAttribute("aria-current", active ? "true" : "false");
    });

    lightboxSlides.forEach((slide, slideIndex) => {
      const active = slideIndex === current;
      slide.classList.toggle("is-active", active);
      slide.setAttribute("aria-hidden", active ? "false" : "true");
    });

    thumbs[current]?.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" });
    if (status && announce) status.textContent = `${current + 1} / ${slides.length}`;
  };

  const previous = () => applySlides(current - 1);
  const next = () => applySlides(current + 1);

  const openLightbox = (index, opener) => {
    if (!lightbox || suppressOpen) return;
    returnFocus = opener;
    applySlides(index, false);
    lightbox.classList.remove("hidden");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.classList.add("is-locked");
    window.requestAnimationFrame(() => {
      lightbox.classList.add("is-open");
      lightboxClose?.focus();
    });
  };

  const closeLightbox = () => {
    if (!lightbox) return;
    lightbox.classList.remove("is-open");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.classList.remove("is-locked");
    window.setTimeout(() => {
      lightbox.classList.add("hidden");
      returnFocus?.focus();
    }, 200);
  };

  slides.forEach((slide, index) => slide.addEventListener("click", () => openLightbox(index, slide)));
  thumbs.forEach((thumb, index) => thumb.addEventListener("click", () => applySlides(index)));
  dots.forEach((dot, index) => dot.addEventListener("click", () => applySlides(index)));
  previousButton?.addEventListener("click", previous);
  nextButton?.addEventListener("click", next);
  lightboxPrevious?.addEventListener("click", previous);
  lightboxNext?.addEventListener("click", next);
  lightboxClose?.addEventListener("click", closeLightbox);
  lightbox?.addEventListener("click", (event) => {
    if (event.target === lightbox) closeLightbox();
  });

  viewer?.addEventListener("touchstart", (event) => {
    galleryStartX = event.changedTouches[0]?.clientX || 0;
    galleryDiffX = 0;
  }, { passive: true });

  viewer?.addEventListener("touchmove", (event) => {
    galleryDiffX = (event.changedTouches[0]?.clientX || 0) - galleryStartX;
  }, { passive: true });

  viewer?.addEventListener("touchend", () => {
    if (Math.abs(galleryDiffX) < 40) return;
    suppressOpen = true;
    galleryDiffX < 0 ? next() : previous();
    window.setTimeout(() => { suppressOpen = false; }, 220);
  }, { passive: true });

  lightboxTrack?.addEventListener("touchstart", (event) => {
    lightboxStartX = event.changedTouches[0]?.clientX || 0;
    lightboxDiffX = 0;
  }, { passive: true });

  lightboxTrack?.addEventListener("touchmove", (event) => {
    lightboxDiffX = (event.changedTouches[0]?.clientX || 0) - lightboxStartX;
  }, { passive: true });

  lightboxTrack?.addEventListener("touchend", () => {
    if (Math.abs(lightboxDiffX) < 45) return;
    lightboxDiffX < 0 ? next() : previous();
  }, { passive: true });

  document.addEventListener("keydown", (event) => {
    if (!lightbox?.classList.contains("is-open")) return;

    if (event.key === "Escape") closeLightbox();
    if (event.key === "ArrowLeft") previous();
    if (event.key === "ArrowRight") next();

    if (event.key === "Tab") {
      const controls = Array.from(lightbox.querySelectorAll("button:not([disabled])"));
      if (!controls.length) return;
      const first = controls[0];
      const last = controls[controls.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  applySlides(0, false);
});
