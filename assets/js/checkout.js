(() => {
  const cfg = window.polarisCheckoutConfig || {};
  const form = document.querySelector("form.checkout.woocommerce-checkout");
  if (!form) return;

  const messages = Object.assign(
    {
      invalidPhone: "Lütfen geçerli bir Türkiye telefon numarası girin.",
      invalidTC: "T.C. Kimlik No 11 haneli sayısal değer olmalıdır.",
      invalidPostcode: "Posta kodu 5 haneli olmalıdır.",
      requiredField: "Bu alan zorunludur.",
    },
    cfg.messages || {}
  );

  const cityStateMap = cfg.cityStateMap || {};
  const districtMap = cfg.districtMap || {};

  const dispatchChange = (el) => {
    if (!el) return;
    el.dispatchEvent(new Event("change", { bubbles: true }));
  };

  const normalizeToken = (value) =>
    ((text) => (typeof text.normalize === "function" ? text.normalize("NFD") : text))(String(value || ""))
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase()
      .replace(/[^a-z0-9]/g, "");

  const toDigits = (value) => String(value || "").replace(/\D+/g, "");

  const toTRPhoneDigits = (value) => {
    let digits = toDigits(value);
    if (digits.length === 12 && digits.startsWith("90")) {
      digits = digits.slice(2);
    }
    if (digits.length === 11 && digits.startsWith("0")) {
      digits = digits.slice(1);
    }
    if (!/^[2-5]\d{9}$/.test(digits)) return "";
    return digits;
  };

  const formatTRPhone = (digits) =>
    `0 (${digits.slice(0, 3)}) ${digits.slice(3, 6)} ${digits.slice(6, 8)} ${digits.slice(8, 10)}`;

  const formatPhonePartial = (rawValue) => {
    let digits = toDigits(rawValue);
    if (digits.startsWith("90")) digits = digits.slice(2);
    if (digits.startsWith("0")) digits = digits.slice(1);
    digits = digits.slice(0, 10);
    if (!digits) return "";

    let out = "0";
    if (digits.length >= 1) out += ` (${digits.slice(0, Math.min(3, digits.length))}`;
    if (digits.length >= 4) out += `) ${digits.slice(3, Math.min(6, digits.length))}`;
    if (digits.length >= 7) out += ` ${digits.slice(6, Math.min(8, digits.length))}`;
    if (digits.length >= 9) out += ` ${digits.slice(8, Math.min(10, digits.length))}`;
    return out;
  };

  const findStateCodeByCity = (cityValue) => {
    const exact = cityStateMap[cityValue];
    if (exact) return exact;

    const target = normalizeToken(cityValue);
    if (!target) return "";

    for (const [cityName, stateCode] of Object.entries(cityStateMap)) {
      if (normalizeToken(cityName) === target) return stateCode;
    }

    return "";
  };

  const getField = (name) => form.querySelector(`#${name}`);
  const getFieldRow = (name) => form.querySelector(`#${name}_field`);

  const createOrGetDatalist = (id) => {
    if (!id) return null;
    let datalist = document.getElementById(id);
    if (datalist) return datalist;
    datalist = document.createElement("datalist");
    datalist.id = id;
    form.appendChild(datalist);
    return datalist;
  };

  const setCitySelectByName = (selectEl, cityName) => {
    if (!selectEl || !cityName) return false;
    const target = normalizeToken(cityName);
    if (!target) return false;

    for (const option of Array.from(selectEl.options)) {
      const optionTokenValue = normalizeToken(option.value);
      const optionTokenText = normalizeToken(option.textContent || "");
      if (optionTokenValue === target || optionTokenText === target) {
        if (selectEl.value !== option.value) {
          selectEl.value = option.value;
          dispatchChange(selectEl);
        }
        return true;
      }
    }

    return false;
  };

  const syncStateFromCity = (prefix) => {
    const cityInput = getField(`${prefix}_city`);
    const stateInput = getField(`${prefix}_state`);
    if (!cityInput || !stateInput) return;

    const stateCode = findStateCodeByCity(cityInput.value);
    if (stateInput.value !== stateCode) {
      stateInput.value = stateCode;
      dispatchChange(stateInput);
    }
  };

  const updateDistrictSuggestions = (prefix) => {
    const cityInput = getField(`${prefix}_city`);
    const districtInput = getField(`${prefix}_district`);
    if (!cityInput || !districtInput) return;

    const listId = districtInput.getAttribute("list");
    if (!listId) return;

    const datalist = createOrGetDatalist(listId);
    if (!datalist) return;

    const selectedCity = cityInput.value;
    const selectedCityToken = normalizeToken(selectedCity);

    let districts = [];
    for (const [mapCity, cityDistricts] of Object.entries(districtMap)) {
      if (normalizeToken(mapCity) === selectedCityToken && Array.isArray(cityDistricts)) {
        districts = cityDistricts;
        break;
      }
    }

    datalist.innerHTML = "";
    districts.forEach((district) => {
      const option = document.createElement("option");
      option.value = district;
      datalist.appendChild(option);
    });
  };

  const attachPhoneValidation = (fieldName, isRequiredFn) => {
    const input = getField(fieldName);
    if (!input) return;

    input.addEventListener("input", () => {
      input.value = formatPhonePartial(input.value);
      input.setCustomValidity("");
    });

    input.addEventListener("blur", () => {
      const required = isRequiredFn();
      const value = input.value.trim();
      if (!value) {
        input.setCustomValidity(required ? messages.requiredField : "");
        return;
      }

      const digits = toTRPhoneDigits(value);
      if (!digits) {
        input.setCustomValidity(messages.invalidPhone);
        return;
      }

      input.value = formatTRPhone(digits);
      input.setCustomValidity("");
    });
  };

  const attachPostcodeValidation = (fieldName, isRequiredFn) => {
    const input = getField(fieldName);
    if (!input) return;

    input.addEventListener("input", () => {
      input.value = toDigits(input.value).slice(0, 5);
      input.setCustomValidity("");
    });

    input.addEventListener("blur", () => {
      const required = isRequiredFn();
      const value = input.value.trim();
      if (!value) {
        input.setCustomValidity(required ? messages.requiredField : "");
        return;
      }

      input.setCustomValidity(/^\d{5}$/.test(value) ? "" : messages.invalidPostcode);
    });
  };

  const tcInput = getField("billing_tc_kimlik_no");
  if (tcInput) {
    tcInput.addEventListener("input", () => {
      tcInput.value = toDigits(tcInput.value).slice(0, 11);
      tcInput.setCustomValidity("");
    });

    tcInput.addEventListener("blur", () => {
      const value = tcInput.value.trim();
      if (!value) {
        tcInput.setCustomValidity("");
        return;
      }
      tcInput.setCustomValidity(/^\d{11}$/.test(value) ? "" : messages.invalidTC);
    });
  }

  const shippingToggle = getField("ship-to-different-address-checkbox");
  const shippingRequired = () => Boolean(shippingToggle && shippingToggle.checked);

  attachPhoneValidation("billing_phone", () => true);
  attachPhoneValidation("shipping_phone", shippingRequired);
  attachPostcodeValidation("billing_postcode", () => true);
  attachPostcodeValidation("shipping_postcode", shippingRequired);

  ["billing", "shipping"].forEach((prefix) => {
    const cityInput = getField(`${prefix}_city`);
    if (!cityInput) return;

    cityInput.addEventListener("change", () => {
      syncStateFromCity(prefix);
      updateDistrictSuggestions(prefix);
    });

    syncStateFromCity(prefix);
    updateDistrictSuggestions(prefix);
  });

  const corporateCheckbox = getField("billing_corporate_invoice");
  const corporateRows = [
    getFieldRow("billing_company"),
    getFieldRow("billing_tax_office"),
    getFieldRow("billing_tax_number"),
  ].filter(Boolean);

  const corporateInputs = [
    getField("billing_company"),
    getField("billing_tax_office"),
    getField("billing_tax_number"),
  ].filter(Boolean);

  const syncCorporateVisibility = () => {
    if (!corporateCheckbox) return;
    const enabled = corporateCheckbox.checked;

    corporateRows.forEach((row) => {
      row.hidden = !enabled;
      row.classList.toggle("is-hidden", !enabled);
    });

    corporateInputs.forEach((input) => {
      input.required = enabled;
      if (!enabled) input.setCustomValidity("");
    });
  };

  if (corporateCheckbox) {
    corporateCheckbox.addEventListener("change", syncCorporateVisibility);
    syncCorporateVisibility();
  }

  if (shippingToggle) {
    shippingToggle.addEventListener("change", () => {
      const shippingPhone = getField("shipping_phone");
      const shippingPostcode = getField("shipping_postcode");

      if (shippingPhone) shippingPhone.setCustomValidity("");
      if (shippingPostcode) shippingPostcode.setCustomValidity("");
    });
  }

  const initPlaces = (prefix) => {
    if (!window.google || !window.google.maps || !window.google.maps.places) return;
    const addressInput = getField(`${prefix}_address_1`);
    if (!addressInput) return;

    const cityInput = getField(`${prefix}_city`);
    const districtInput = getField(`${prefix}_district`);
    const postcodeInput = getField(`${prefix}_postcode`);

    const autocomplete = new window.google.maps.places.Autocomplete(addressInput, {
      types: ["address"],
      componentRestrictions: { country: "tr" },
      fields: ["address_components", "formatted_address"],
    });

    autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();
      const components = Array.isArray(place.address_components) ? place.address_components : [];

      const findComponent = (type) => {
        const comp = components.find((item) => Array.isArray(item.types) && item.types.includes(type));
        return comp ? comp.long_name : "";
      };

      const cityName =
        findComponent("administrative_area_level_1") ||
        findComponent("locality") ||
        findComponent("administrative_area_level_2");

      const districtName =
        findComponent("administrative_area_level_2") ||
        findComponent("sublocality_level_1") ||
        findComponent("sublocality");

      const postcode = findComponent("postal_code");

      if (place.formatted_address) {
        addressInput.value = place.formatted_address;
        dispatchChange(addressInput);
      }

      if (cityInput && cityName) {
        setCitySelectByName(cityInput, cityName);
        syncStateFromCity(prefix);
        updateDistrictSuggestions(prefix);
      }

      if (districtInput && districtName) {
        districtInput.value = districtName;
        dispatchChange(districtInput);
      }

      if (postcodeInput && postcode) {
        postcodeInput.value = toDigits(postcode).slice(0, 5);
        dispatchChange(postcodeInput);
      }
    });
  };

  if (cfg.googlePlacesEnabled) {
    initPlaces("billing");
    initPlaces("shipping");
  }

  form.addEventListener("submit", (event) => {
    const invalid = [];

    const billingPhone = getField("billing_phone");
    if (billingPhone) {
      const digits = toTRPhoneDigits(billingPhone.value);
      if (!digits) {
        billingPhone.setCustomValidity(messages.invalidPhone);
        invalid.push(billingPhone);
      } else {
        billingPhone.value = formatTRPhone(digits);
        billingPhone.setCustomValidity("");
      }
    }

    const shippingPhone = getField("shipping_phone");
    if (shippingPhone) {
      if (shippingRequired()) {
        const digits = toTRPhoneDigits(shippingPhone.value);
        if (!digits) {
          shippingPhone.setCustomValidity(messages.invalidPhone);
          invalid.push(shippingPhone);
        } else {
          shippingPhone.value = formatTRPhone(digits);
          shippingPhone.setCustomValidity("");
        }
      } else {
        shippingPhone.setCustomValidity("");
      }
    }

    if (tcInput) {
      if (!tcInput.value.trim()) {
        tcInput.value = "11111111111";
      }
      if (!/^\d{11}$/.test(tcInput.value)) {
        tcInput.setCustomValidity(messages.invalidTC);
        invalid.push(tcInput);
      } else {
        tcInput.setCustomValidity("");
      }
    }

    const billingPostcode = getField("billing_postcode");
    if (billingPostcode) {
      if (!/^\d{5}$/.test(billingPostcode.value.trim())) {
        billingPostcode.setCustomValidity(messages.invalidPostcode);
        invalid.push(billingPostcode);
      } else {
        billingPostcode.setCustomValidity("");
      }
    }

    const shippingPostcode = getField("shipping_postcode");
    if (shippingPostcode) {
      if (shippingRequired() && !/^\d{5}$/.test(shippingPostcode.value.trim())) {
        shippingPostcode.setCustomValidity(messages.invalidPostcode);
        invalid.push(shippingPostcode);
      } else {
        shippingPostcode.setCustomValidity("");
      }
    }

    if (corporateCheckbox && corporateCheckbox.checked) {
      corporateInputs.forEach((input) => {
        if (!input.value.trim()) {
          input.setCustomValidity(messages.requiredField);
          invalid.push(input);
        } else {
          input.setCustomValidity("");
        }
      });
    }

    if (invalid.length) {
      event.preventDefault();
      invalid[0].reportValidity();
    }
  });

  if (window.jQuery) {
    window.jQuery(document.body).on("updated_checkout", () => {
      ["billing", "shipping"].forEach((prefix) => {
        syncStateFromCity(prefix);
        updateDistrictSuggestions(prefix);
      });
      syncCorporateVisibility();
    });
  }
})();
