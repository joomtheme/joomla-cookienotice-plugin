/* plg_system_cookienotice */
(function () {
  "use strict";

  var CATEGORIES = ["preferences", "analytics", "marketing"];

  function parseConfig(root) {
    var node = root.querySelector(".jt-cookie-config");

    if (!node) {
      return null;
    }

    try {
      return JSON.parse(node.textContent || "{}");
    } catch (error) {
      return null;
    }
  }

  function getCookieValue(name) {
    var encodedName = encodeURIComponent(name) + "=";
    var cookies = document.cookie ? document.cookie.split(";") : [];

    for (var i = 0; i < cookies.length; i += 1) {
      var cookie = cookies[i].trim();

      if (cookie.indexOf(encodedName) === 0) {
        try {
          return decodeURIComponent(cookie.substring(encodedName.length));
        } catch (error) {
          return "";
        }
      }
    }

    return "";
  }

  function createChoices(value) {
    return {
      necessary: true,
      preferences: value === true,
      analytics: value === true,
      marketing: value === true
    };
  }

  function createState(config, choices) {
    return {
      version: 1,
      revision: String(config.revision || "1"),
      timestamp: new Date().toISOString(),
      choices: {
        necessary: true,
        preferences: choices.preferences === true,
        analytics: choices.analytics === true,
        marketing: choices.marketing === true
      }
    };
  }

  function readState(config) {
    var raw = getCookieValue(config.cookieName);

    if (!raw) {
      return null;
    }

    try {
      var state = JSON.parse(raw);
      var choices = state && state.choices;

      if (
        !state
        || state.version !== 1
        || String(state.revision) !== String(config.revision)
        || !choices
        || choices.necessary !== true
        || typeof choices.preferences !== "boolean"
        || typeof choices.analytics !== "boolean"
        || typeof choices.marketing !== "boolean"
      ) {
        return null;
      }

      return state;
    } catch (error) {
      return null;
    }
  }

  function writeState(config, state) {
    var maxAge = Number.parseInt(String(config.maxAge || "15552000"), 10);

    if (!Number.isFinite(maxAge) || maxAge < 1) {
      maxAge = 15552000;
    }

    var cookie = encodeURIComponent(config.cookieName) + "=" + encodeURIComponent(JSON.stringify(state))
      + "; max-age=" + String(maxAge)
      + "; path=/; samesite=lax";

    if (window.location.protocol === "https:") {
      cookie += "; secure";
    }

    document.cookie = cookie;
  }

  function removeConsentCookie(config) {
    document.cookie = encodeURIComponent(config.cookieName) + "=; max-age=0; path=/; samesite=lax";
  }

  function wildcardMatch(value, pattern) {
    var expression = pattern.replace(/[.+?^${}()|[\]\\]/g, "\\$&").replace(/\*/g, ".*");

    return new RegExp("^" + expression + "$").test(value);
  }

  function visibleCookieNames() {
    if (!document.cookie) {
      return [];
    }

    return document.cookie.split(";").map(function (item) {
      var name = item.split("=")[0].trim();

      try {
        return decodeURIComponent(name);
      } catch (error) {
        return name;
      }
    });
  }

  function cookiePaths() {
    var paths = ["/"];
    var parts = window.location.pathname.split("/").filter(Boolean);

    for (var i = 1; i <= parts.length; i += 1) {
      paths.push("/" + parts.slice(0, i).join("/") + "/");
    }

    return paths;
  }

  function cookieDomains() {
    var hostname = window.location.hostname;
    var parts = hostname.split(".");
    var domains = [""];

    if (!hostname || hostname === "localhost" || /^[0-9.]+$/.test(hostname)) {
      return domains;
    }

    domains.push(hostname);

    for (var i = 1; i < parts.length - 1; i += 1) {
      domains.push("." + parts.slice(i).join("."));
    }

    return domains;
  }

  function expireCookie(name) {
    var paths = cookiePaths();
    var domains = cookieDomains();

    for (var i = 0; i < paths.length; i += 1) {
      for (var j = 0; j < domains.length; j += 1) {
        var cookie = encodeURIComponent(name) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; max-age=0; path=" + paths[i];

        if (domains[j]) {
          cookie += "; domain=" + domains[j];
        }

        document.cookie = cookie + "; samesite=lax";
      }
    }
  }

  function cleanupRevokedCookies(config, previousState, nextState) {
    if (!previousState) {
      return false;
    }

    var names = visibleCookieNames();
    var revoked = false;

    CATEGORIES.forEach(function (category) {
      if (previousState.choices[category] !== true || nextState.choices[category] === true) {
        return;
      }

      revoked = true;
      var patterns = (config.cleanupCookies && config.cleanupCookies[category]) || [];

      names.forEach(function (name) {
        if (patterns.some(function (pattern) { return wildcardMatch(name, pattern); })) {
          expireCookie(name);
        }
      });
    });

    return revoked;
  }

  function copyScript(source) {
    var script = document.createElement("script");
    var sourceType = source.getAttribute("type");

    for (var i = 0; i < source.attributes.length; i += 1) {
      var attribute = source.attributes[i];

      if (
        attribute.name !== "type"
        && attribute.name !== "data-cookie-category"
        && attribute.name !== "data-cookie-src"
        && attribute.name !== "data-cookie-activated"
      ) {
        script.setAttribute(attribute.name, attribute.value);
      }
    }

    var sourceUrl = source.getAttribute("data-cookie-src");

    if (sourceType && sourceType.toLowerCase() !== "text/plain") {
      script.type = sourceType;
    }

    if (sourceUrl) {
      script.src = sourceUrl;
    }

    if (script.src && !source.hasAttribute("async")) {
      script.async = false;
    }

    script.text = source.textContent || "";

    return script;
  }

  function activateAnnotatedElements(category) {
    var selector = "[data-cookie-category=\"" + category + "\"]:not([data-cookie-activated])";
    var elements = document.querySelectorAll(selector);

    for (var i = 0; i < elements.length; i += 1) {
      var element = elements[i];

      if (element.tagName === "SCRIPT") {
        element.setAttribute("data-cookie-activated", "true");
        element.parentNode.replaceChild(copyScript(element), element);
        continue;
      }

      var sourceUrl = element.getAttribute("data-cookie-src");
      var sourceSet = element.getAttribute("data-cookie-srcset");

      if (sourceUrl) {
        element.setAttribute("src", sourceUrl);
      }

      if (sourceSet) {
        element.setAttribute("srcset", sourceSet);
      }

      element.setAttribute("data-cookie-activated", "true");
    }
  }

  function activateSnippet(root, config, category) {
    var snippet = config.snippets && config.snippets[category];

    if (!snippet || root.getAttribute("data-jt-snippet-" + category) === "active") {
      return;
    }

    root.setAttribute("data-jt-snippet-" + category, "active");

    var template = document.createElement("template");
    template.innerHTML = snippet;

    var scripts = template.content.querySelectorAll("script");

    for (var i = 0; i < scripts.length; i += 1) {
      scripts[i].parentNode.replaceChild(copyScript(scripts[i]), scripts[i]);
    }

    var container = document.createElement("div");
    container.className = "jt-cookie-injected";
    container.setAttribute("data-jt-cookie-injected", category);
    container.appendChild(template.content);
    document.body.appendChild(container);
  }

  function activateAllowedContent(root, config, state) {
    if (!state) {
      return;
    }

    CATEGORIES.forEach(function (category) {
      if (state.choices[category] === true) {
        activateAnnotatedElements(category);
        activateSnippet(root, config, category);
      }
    });
  }

  function dispatchConsentEvent(name, state) {
    document.dispatchEvent(new CustomEvent(name, { detail: state }));
  }

  function init(root) {
    var config = parseConfig(root);

    if (!config || !config.cookieName) {
      return;
    }

    var banner = root.querySelector(".jt-cookie-notice");
    var launcher = root.querySelector(".jt-cookie-launcher");
    var backdrop = root.querySelector("[data-jt-cookie-preferences]");
    var dialog = root.querySelector(".jt-cookie-preferences");
    var currentState = readState(config);
    var returnFocus = null;

    function showBanner() {
      if (banner) {
        banner.classList.add("show");
      }
    }

    function hideBanner() {
      if (banner) {
        banner.classList.remove("show");
      }
    }

    function updateLauncher() {
      if (launcher) {
        launcher.hidden = !(currentState && config.showLauncher !== false);
      }
    }

    function setPreferenceInputs(choices) {
      var inputs = root.querySelectorAll("[data-jt-cookie-category]");

      for (var i = 0; i < inputs.length; i += 1) {
        var category = inputs[i].getAttribute("data-jt-cookie-category");
        inputs[i].checked = choices[category] === true;
      }
    }

    function openPreferences() {
      if (!backdrop || !dialog) {
        return;
      }

      returnFocus = document.activeElement;
      setPreferenceInputs(currentState ? currentState.choices : createChoices(false));
      hideBanner();
      backdrop.hidden = false;
      document.documentElement.classList.add("jt-cookie-preferences-open");
      dialog.focus();
    }

    function closePreferences() {
      if (!backdrop) {
        return;
      }

      backdrop.hidden = true;
      document.documentElement.classList.remove("jt-cookie-preferences-open");

      if (!currentState) {
        showBanner();
      }

      if (currentState && launcher && !launcher.hidden) {
        launcher.focus();
      } else if (
        returnFocus
        && typeof returnFocus.focus === "function"
        && (!currentState || !banner || !banner.contains(returnFocus))
      ) {
        returnFocus.focus();
      }
    }

    function saveChoices(choices) {
      var previousState = currentState;
      var nextState = createState(config, choices);
      var needsReload = cleanupRevokedCookies(config, previousState, nextState);

      writeState(config, nextState);
      currentState = nextState;
      hideBanner();
      updateLauncher();
      closePreferences();
      activateAllowedContent(root, config, currentState);
      dispatchConsentEvent("jt:cookie-consent:change", currentState);

      if (needsReload) {
        window.location.reload();
      }
    }

    function selectedChoices() {
      var choices = createChoices(false);
      var inputs = root.querySelectorAll("[data-jt-cookie-category]");

      for (var i = 0; i < inputs.length; i += 1) {
        choices[inputs[i].getAttribute("data-jt-cookie-category")] = inputs[i].checked === true;
      }

      return choices;
    }

    root.addEventListener("click", function (event) {
      var button = event.target.closest("[data-jt-cookie-action]");

      if (!button || !root.contains(button)) {
        return;
      }

      var action = button.getAttribute("data-jt-cookie-action");

      if (action === "accept") {
        saveChoices(createChoices(true));
      } else if (action === "reject") {
        saveChoices(createChoices(false));
      } else if (action === "save") {
        saveChoices(selectedChoices());
      } else if (action === "preferences" || action === "open") {
        openPreferences();
      } else if (action === "close") {
        closePreferences();
      }
    });

    if (backdrop) {
      backdrop.addEventListener("click", function (event) {
        if (event.target === backdrop) {
          closePreferences();
        }
      });

      backdrop.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          event.preventDefault();
          closePreferences();
          return;
        }

        if (event.key !== "Tab" || !dialog) {
          return;
        }

        var focusable = dialog.querySelectorAll("a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex=\"-1\"])");

        if (!focusable.length) {
          return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      });
    }

    window.JTCookieConsent = {
      getState: function () {
        return currentState ? JSON.parse(JSON.stringify(currentState)) : null;
      },
      hasConsent: function (category) {
        return category === "necessary" || Boolean(currentState && currentState.choices[category] === true);
      },
      openPreferences: openPreferences,
      reset: function () {
        removeConsentCookie(config);
        window.location.reload();
      }
    };

    if (currentState) {
      activateAllowedContent(root, config, currentState);
      updateLauncher();
    } else {
      var delay = Number.parseInt(String(config.delay || "0"), 10);

      if (!Number.isFinite(delay) || delay < 0) {
        delay = 0;
      }

      if (delay > 0) {
        window.setTimeout(showBanner, delay);
      } else {
        showBanner();
      }
    }

    dispatchConsentEvent("jt:cookie-consent:ready", currentState);
  }

  function initAll() {
    var roots = document.querySelectorAll("[data-jt-cookie-consent-root]");

    for (var i = 0; i < roots.length; i += 1) {
      init(roots[i]);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }
})();
