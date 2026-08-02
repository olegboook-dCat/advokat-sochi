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

  // Кнопка «наверх»: появляется после прокрутки, плавно возвращает наверх
  var toTop = document.getElementById("toTop");
  if (toTop) {
    var SHOW_AT = 400; // px прокрутки, после которых показываем кнопку

    function toggleToTop() {
      if (window.pageYOffset > SHOW_AT) {
        toTop.classList.add("is-visible");
      } else {
        toTop.classList.remove("is-visible");
      }
    }

    window.addEventListener("scroll", toggleToTop, { passive: true });
    toggleToTop();

    toTop.addEventListener("click", function () {
      var reduce = window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" });
    });
  }

  // Форма отзыва → Web3Forms (отправка без перезагрузки страницы)
  var reviewForm = document.getElementById("reviewForm");
  if (reviewForm) {
    var statusEl = document.getElementById("rfStatus");
    var submitBtn = document.getElementById("rfSubmit");

    reviewForm.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!reviewForm.checkValidity()) {
        reviewForm.reportValidity();
        return;
      }

      statusEl.className = "review-form__status";
      statusEl.textContent = "Отправляем…";
      submitBtn.disabled = true;

      fetch(reviewForm.getAttribute("action"), {
        method: "POST",
        headers: { "Accept": "application/json" },
        body: new FormData(reviewForm)
      })
        .then(function (r) {
          return r.json().then(function (j) { return { ok: r.ok, data: j }; });
        })
        .then(function (res) {
          var msg = res.data && res.data.message;
          if (res.ok && res.data && res.data.success) {
            reviewForm.reset();
            statusEl.className = "review-form__status is-ok";
            statusEl.textContent = msg ||
              "Спасибо! Ваш отзыв отправлен на модерацию и появится на сайте после проверки.";
          } else {
            statusEl.className = "review-form__status is-err";
            statusEl.textContent = msg ||
              "Не удалось отправить отзыв. Попробуйте ещё раз или свяжитесь с нами по телефону.";
          }
        })
        .catch(function () {
          statusEl.className = "review-form__status is-err";
          statusEl.textContent =
            "Ошибка сети. Проверьте подключение к интернету и попробуйте снова.";
        })
        .finally(function () {
          submitBtn.disabled = false;
        });
    });
  }
})();
