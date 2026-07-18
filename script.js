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
})();
