(function () {
  "use strict";

  const form = document.querySelector("[data-password-reset-form]");
  if (!form) return;

  const password = form.querySelector("[data-password-primary]");
  const confirmation = form.querySelector("[data-password-confirm]");
  const toggle = form.querySelector("[data-password-toggle]");
  const strength = form.querySelector("[data-password-strength]");
  const strengthLabel = form.querySelector("[data-password-strength-label]");

  const labels = {
    empty: "En az 10 karakter kullanın.",
    weak: "Şifre çok zayıf.",
    medium: "Şifre orta güçte.",
    strong: "Şifre güçlü.",
    mismatch: "Şifreler birbiriyle eşleşmiyor.",
  };

  function setStrength(state, label) {
    if (!strength || !strengthLabel) return;
    strength.dataset.state = state;
    strengthLabel.textContent = label;
  }

  function updateStrength() {
    const value = password ? password.value : "";
    const repeated = confirmation ? confirmation.value : "";

    if (confirmation) {
      confirmation.setCustomValidity(repeated && value !== repeated ? labels.mismatch : "");
    }

    if (!value) {
      setStrength("empty", labels.empty);
      return;
    }

    if (repeated && value !== repeated) {
      setStrength("weak", labels.mismatch);
      return;
    }

    let score = 0;
    if (window.wp && window.wp.passwordStrength) {
      score = window.wp.passwordStrength.meter(value, [], repeated || value);
    }

    if (value.length < 10 || score < 3) {
      setStrength("weak", labels.weak);
    } else if (score === 3) {
      setStrength("medium", labels.medium);
    } else {
      setStrength("strong", labels.strong);
    }
  }

  toggle?.addEventListener("click", function () {
    const reveal = password?.type === "password";
    [password, confirmation].forEach((input) => {
      if (input) input.type = reveal ? "text" : "password";
    });

    toggle.setAttribute("aria-pressed", reveal ? "true" : "false");
    toggle.setAttribute("aria-label", reveal ? "Şifreyi gizle" : "Şifreyi göster");
    const icon = toggle.querySelector("i");
    icon?.classList.toggle("fa-eye", !reveal);
    icon?.classList.toggle("fa-eye-slash", reveal);
  });

  password?.addEventListener("input", updateStrength);
  confirmation?.addEventListener("input", updateStrength);
  updateStrength();
})();
