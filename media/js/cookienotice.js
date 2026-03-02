/* plg_system_cookienotice */
(function () {
  function hasBootstrap() {
    // Bootstrap 5 sets global bootstrap object when bundle is loaded.
    return typeof window.bootstrap !== "undefined";
  }

  function setCookie(name, value, maxAgeSeconds) {
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value)
      + "; max-age=" + String(maxAgeSeconds)
      + "; path=/; samesite=lax";
  }

  function initBanner(banner) {
    var cookieName = banner.getAttribute("data-cookie-name") || "cn_accepted";
    var maxAge = parseInt(banner.getAttribute("data-max-age") || "15552000", 10); // 180d
    var delay = parseInt(banner.getAttribute("data-delay") || "0", 10);

    var show = function () {
      banner.classList.remove("d-none");
      // If Bootstrap is present and alert has fade, toggle show.
      if (banner.classList.contains("fade")) {
        // Force reflow for transition
        void banner.offsetWidth;
        banner.classList.add("show");
      }
    };

    var hide = function () {
      if (banner.classList.contains("fade")) {
        banner.classList.remove("show");
        setTimeout(function () {
          banner.remove();
        }, 200);
      } else {
        banner.remove();
      }
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
      setTimeout(show, delay);
    } else {
      show();
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var banner = document.querySelector(".jt-cookie-notice");
    if (!banner) return;

    if (!hasBootstrap()) {
      banner.classList.add("jt-no-bs");
    }

    initBanner(banner);
  });
})();
