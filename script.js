// Минимум JS: мобильное меню + год в футере. Без анимаций и зависимостей.
(function () {
  "use strict";

  // Год в футере
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Мобильное меню
  var toggle = document.getElementById("navToggle");
  var menu = document.getElementById("mobileMenu");

  function closeMenu() {
    if (!menu || !toggle) return;
    menu.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
  }

  if (toggle && menu) {
    toggle.addEventListener("click", function () {
      var isOpen = toggle.getAttribute("aria-expanded") === "true";
      if (isOpen) {
        closeMenu();
      } else {
        menu.hidden = false;
        toggle.setAttribute("aria-expanded", "true");
      }
    });

    // Закрывать меню после клика по пункту
    menu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closeMenu);
    });

    // Закрывать при переходе на десктоп
    window.addEventListener("resize", function () {
      if (window.innerWidth > 900) closeMenu();
    });
  }

  // Классическое скрытие шапки при прокрутке:
  // вниз — уезжает вверх, вверх — возвращается; у самого верха всегда видима.
  var header = document.querySelector(".site-header");
  if (header) {
    var lastY = window.pageYOffset;
    var ticking = false;
    var THRESHOLD = 8; // игнорируем микродёргания скролла

    function onScroll() {
      var y = window.pageYOffset;

      // У самого верха или при открытом мобильном меню — показываем.
      if (y <= header.offsetHeight || (toggle && toggle.getAttribute("aria-expanded") === "true")) {
        header.classList.remove("site-header--hidden");
        lastY = y;
        ticking = false;
        return;
      }

      if (y - lastY > THRESHOLD) {
        header.classList.add("site-header--hidden");    // скролл вниз
        lastY = y;
      } else if (lastY - y > THRESHOLD) {
        header.classList.remove("site-header--hidden");  // скролл вверх
        lastY = y;
      }
      ticking = false;
    }

    window.addEventListener("scroll", function () {
      if (!ticking) {
        window.requestAnimationFrame(onScroll);
        ticking = true;
      }
    }, { passive: true });
  }
})();
