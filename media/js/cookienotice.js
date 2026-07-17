/* plg_system_cookienotice */
(function () {
  "use strict";

  function getCookie(name) {
    var encodedName = encodeURIComponent(name) + "=";
    var cookies = document.cookie ? document.cookie.split(";") : [];

    for (var i = 0; i < cookies.length; i += 1) {
      if (cookies[i].trim().indexOf(encodedName) === 0) {
        return true;
      }
    }

    return false;
  }

  function setCookie(name, value, maxAgeSeconds) {
    var cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value)
      + "; max-age=" + String(maxAgeSeconds)
      + "; path=/; samesite=lax";

    if (window.location.protocol === "https:") {
      cookie += "; secure";
    }

    document.cookie = cookie;
  }

  function removeBanner(banner) {
    if (banner && banner.parentNode) {
      banner.parentNode.removeChild(banner);
    }
  }

  function initBanner(banner) {
    var cookieName = banner.getAttribute("data-cookie-name") || "cn_accepted";
    var maxAge = parseInt(banner.getAttribute("data-max-age") || "15552000", 10);
    var delay = parseInt(banner.getAttribute("data-delay") || "0", 10);

    if (getCookie(cookieName)) {
      removeBanner(banner);
      return;
    }

    if (!Number.isFinite(maxAge) || maxAge < 1) {
      maxAge = 15552000;
    }

    if (!Number.isFinite(delay) || delay < 0) {
      delay = 0;
    }

    var show = function () {
      if (!getCookie(cookieName)) {
        banner.classList.add("show");
      } else {
        removeBanner(banner);
      }
    };

    var hide = function () {
      banner.classList.remove("show");

      window.setTimeout(function () {
        removeBanner(banner);
      }, 220);
    };

    var acceptBtn = banner.querySelector(".jt-cookie-accept");
    var closeBtn = banner.querySelector(".jt-cookie-close");

    if (acceptBtn) {
      acceptBtn.addEventListener("click", function () {
        setCookie(cookieName, "1", maxAge);
        hide();
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        hide();
      });
    }

    if (delay > 0) {
      window.setTimeout(show, delay);
    } else {
      show();
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var banners = document.querySelectorAll(".jt-cookie-notice");

    for (var i = 0; i < banners.length; i += 1) {
      initBanner(banners[i]);
    }
  });
})();
