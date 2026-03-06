(function () {
  "use strict";

  const cfg = window.polarisGoogleLogin || null;
  if (!cfg || !cfg.isEnabled) return;

  const wrappers = Array.from(document.querySelectorAll("[data-google-login-wrap]"));
  const buttons = Array.from(document.querySelectorAll("[data-google-login-btn]"));

  const SESSION_KEYS = {
    state: "polarisGoogleLoginState",
    redirect: "polarisGoogleLoginRedirect",
  };

  function sanitizeMessage(value) {
    return String(value || "").trim();
  }

  function getMessage(key, fallback) {
    if (cfg.messages && cfg.messages[key]) {
      return sanitizeMessage(cfg.messages[key]);
    }
    return fallback;
  }

  function setStatus(message, type) {
    const text = sanitizeMessage(message);
    wrappers.forEach((wrap) => {
      const status = wrap.querySelector("[data-google-login-status]");
      if (!status) return;

      status.textContent = text;
      status.classList.remove("is-success", "is-error", "is-info");
      status.classList.add(type || "is-info");
    });
  }

  function setLoading(loading) {
    buttons.forEach((btn) => {
      btn.disabled = !!loading;
      btn.classList.toggle("is-loading", !!loading);
      btn.setAttribute("aria-busy", loading ? "true" : "false");
    });
  }

  async function ajaxPost(action, payload) {
    const data = new URLSearchParams();
    data.append("action", action);

    Object.keys(payload || {}).forEach((key) => {
      const value = payload[key];
      if (value === undefined || value === null) return;
      data.append(key, String(value));
    });

    const response = await fetch(cfg.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: data.toString(),
    });

    let json;
    try {
      json = await response.json();
    } catch (error) {
      throw new Error(getMessage("failed", "Sunucu yaniti okunamadi."));
    }

    if (!response.ok || !json || json.success !== true) {
      const message = json && json.data && json.data.message
        ? String(json.data.message)
        : getMessage("failed", "Islem basarisiz oldu.");
      throw new Error(message);
    }

    return json.data || {};
  }

  function cleanOAuthParamsFromUrl() {
    const url = new URL(window.location.href);
    const keys = ["code", "scope", "authuser", "prompt", "state", "error", "error_subtype"];
    keys.forEach((key) => url.searchParams.delete(key));

    const search = url.searchParams.toString();
    const clean = url.pathname + (search ? `?${search}` : "") + url.hash;
    window.history.replaceState({}, document.title, clean);
  }

  function getRedirectTarget(button) {
    const buttonRedirect = button ? button.getAttribute("data-redirect") : "";
    return buttonRedirect || cfg.defaultRedirect || window.location.href;
  }

  async function startGoogleFlow(button) {
    if (!window.google || !window.google.accounts || !window.google.accounts.oauth2) {
      throw new Error(getMessage("notReady", "Google kutuphanesi henuz hazir degil."));
    }

    // Backend'den tek kullanimlik state aliyoruz (CSRF/state kontrolu icin).
    const prep = await ajaxPost(cfg.prepareAction, {
      nonce: cfg.nonce,
    });

    if (!prep.state) {
      throw new Error(getMessage("failed", "State olusturulamadi."));
    }

    const redirectTarget = getRedirectTarget(button);
    window.sessionStorage.setItem(SESSION_KEYS.state, String(prep.state));
    window.sessionStorage.setItem(SESSION_KEYS.redirect, String(redirectTarget));

    const codeClient = window.google.accounts.oauth2.initCodeClient({
      client_id: cfg.clientId,
      scope: cfg.scope || "openid email profile",
      ux_mode: "redirect",
      redirect_uri: cfg.redirectUri,
      state: String(prep.state),
      select_account: true,
    });

    codeClient.requestCode();
  }

  async function exchangeCodeIfPresent() {
    const params = new URLSearchParams(window.location.search);
    const code = params.get("code");
    const stateFromUrl = params.get("state");
    const error = params.get("error");

    if (error) {
      cleanOAuthParamsFromUrl();
      setStatus(getMessage("cancelled", "Google girisi iptal edildi."), "is-error");
      return;
    }

    if (!code || !stateFromUrl) {
      return;
    }

    const expectedState = window.sessionStorage.getItem(SESSION_KEYS.state) || "";
    if (!expectedState || expectedState !== stateFromUrl) {
      cleanOAuthParamsFromUrl();
      setStatus(getMessage("failed", "State dogrulamasi basarisiz."), "is-error");
      return;
    }

    setLoading(true);
    setStatus(getMessage("loading", "Google girisi tamamlaniyor..."), "is-info");

    try {
      // Redirect sonrasi gelen code'u AJAX endpoint'ine gonderiyoruz.
      // Token exchange islemi JS tarafinda degil, backend tarafinda yapilir.
      const redirectTarget = window.sessionStorage.getItem(SESSION_KEYS.redirect) || cfg.defaultRedirect;

      const result = await ajaxPost(cfg.exchangeAction, {
        nonce: cfg.nonce,
        code,
        state: stateFromUrl,
        redirect_to: redirectTarget,
      });

      cleanOAuthParamsFromUrl();
      setStatus(getMessage("success", "Giris basarili. Yonlendiriliyorsunuz..."), "is-success");

      window.sessionStorage.removeItem(SESSION_KEYS.state);
      window.sessionStorage.removeItem(SESSION_KEYS.redirect);

      const target = result.redirect_url || cfg.defaultRedirect || window.location.href;
      window.location.assign(target);
    } catch (errorObj) {
      cleanOAuthParamsFromUrl();
      const message = errorObj && errorObj.message
        ? errorObj.message
        : getMessage("failed", "Google girisi tamamlanamadi.");
      setStatus(message, "is-error");
      setLoading(false);
    }
  }

  buttons.forEach((button) => {
    button.addEventListener("click", async function (event) {
      event.preventDefault();
      setLoading(true);
      setStatus(getMessage("loading", "Google ile giris baslatiliyor..."), "is-info");

      try {
        await startGoogleFlow(button);
      } catch (errorObj) {
        const message = errorObj && errorObj.message
          ? errorObj.message
          : getMessage("failed", "Google girisi baslatilamadi.");

        setStatus(message, "is-error");
        setLoading(false);
      }
    });
  });

  exchangeCodeIfPresent();
})();
