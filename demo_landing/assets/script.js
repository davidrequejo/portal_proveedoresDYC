(function () {
  "use strict";

  var ANTI_INSPECT = true;

  /* ================= Carousel ================= */
  var carousel = document.getElementById("carousel");
  if (carousel) {
    var slides = Array.prototype.slice.call(carousel.querySelectorAll(".slide"));
    var dotsWrap = document.getElementById("dots");
    var current = 0;
    var timer = null;
    var DELAY = 6000;

    slides.forEach(function (_, i) {
      var dot = document.createElement("button");
      dot.type = "button";
      dot.setAttribute("aria-label", "Ir a la diapositiva " + (i + 1));
      dot.addEventListener("click", function () {
        goTo(i);
        restart();
      });
      dotsWrap.appendChild(dot);
    });
    var dots = Array.prototype.slice.call(dotsWrap.children);

    function goTo(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        s.classList.toggle("is-active", i === current);
      });
      dots.forEach(function (d, i) {
        d.classList.toggle("is-active", i === current);
      });
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function restart() {
      clearInterval(timer);
      timer = setInterval(next, DELAY);
    }

    document.getElementById("nextBtn").addEventListener("click", function () { next(); restart(); });
    document.getElementById("prevBtn").addEventListener("click", function () { prev(); restart(); });

    carousel.addEventListener("mouseenter", function () { clearInterval(timer); });
    carousel.addEventListener("mouseleave", restart);

    var startX = null;
    carousel.addEventListener("touchstart", function (e) { startX = e.touches[0].clientX; }, { passive: true });
    carousel.addEventListener("touchend", function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 50) { dx < 0 ? next() : prev(); restart(); }
      startX = null;
    }, { passive: true });

    document.addEventListener("keydown", function (e) {
      if (e.key === "ArrowRight") { next(); restart(); }
      if (e.key === "ArrowLeft") { prev(); restart(); }
    });

    goTo(0);
    restart();
  }

  /* ================= Navbar ================= */
  var navbar = document.getElementById("navbar");
  var navToggle = document.getElementById("navToggle");
  var navLinks = document.getElementById("navLinks");

  function onScroll() {
    if (navbar) navbar.classList.toggle("scrolled", window.scrollY > 60);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  if (navToggle) {
    navToggle.addEventListener("click", function () {
      navLinks.classList.toggle("open");
    });
    navLinks.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () { navLinks.classList.remove("open"); });
    });
  }

  /* ================= Counters ================= */
  var counters = document.querySelectorAll("[data-count]");
  var counted = false;

  function runCounters() {
    if (counted) return;
    var first = counters[0];
    if (!first) return;
    var top = first.getBoundingClientRect().top;
    if (top > window.innerHeight - 60) return;
    counted = true;
    counters.forEach(function (el) {
      var target = parseInt(el.getAttribute("data-count"), 10);
      var start = performance.now();
      var duration = 1400;
      function step(now) {
        var p = Math.min((now - start) / duration, 1);
        el.textContent = Math.floor(p * target).toLocaleString("es-PE") + (p === 1 ? "+" : "");
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    });
  }
  window.addEventListener("scroll", runCounters, { passive: true });
  runCounters();

  /* ================= Form ================= */
  var form = document.getElementById("contactForm");
  if (form) {
    var success = document.getElementById("formSuccess");

    function setError(input, message) {
      var wrap = input.closest(".field");
      var error = wrap.querySelector(".error");
      if (message) {
        wrap.classList.add("is-invalid");
        error.textContent = message;
      } else {
        wrap.classList.remove("is-invalid");
        error.textContent = "";
      }
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var ok = true;
      var nombre = form.nombre;
      var email = form.email;
      var telefono = form.telefono;
      var distrito = form.distrito;

      if (nombre.value.trim().length < 3) { setError(nombre, "Ingresa tu nombre completo."); ok = false; }
      else setError(nombre, "");

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { setError(email, "Ingresa un correo válido."); ok = false; }
      else setError(email, "");

      if (!/^[0-9+\s-]{6,15}$/.test(telefono.value.trim())) { setError(telefono, "Ingresa un teléfono válido."); ok = false; }
      else setError(telefono, "");

      if (!distrito.value) { setError(distrito, "Selecciona un distrito."); ok = false; }
      else setError(distrito, "");

      if (!ok) return;

      success.hidden = false;
      form.reset();
      setTimeout(function () { success.hidden = true; }, 6000);
    });
  }

  /* ================= Footer year ================= */
  var year = document.getElementById("year");
  if (year) year.textContent = new Date().getFullYear();

  /* ================= Anti inspect ================= */
  if (ANTI_INSPECT) {
    var overlay = document.getElementById("anti-inspect-overlay");

    document.addEventListener("contextmenu", function (e) { e.preventDefault(); });

    document.addEventListener("keydown", function (e) {
      var k = (e.key || "").toUpperCase();
      if (
        k === "F12" ||
        (e.ctrlKey && e.shiftKey && (k === "I" || k === "J" || k === "C" || k === "K")) ||
        (e.ctrlKey && (k === "U" || k === "S"))
      ) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }, true);

    document.addEventListener("dragstart", function (e) { e.preventDefault(); });
    document.addEventListener("selectstart", function (e) {
      if (!/INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) e.preventDefault();
    });

    function showOverlay() {
      if (overlay) overlay.classList.add("is-open");
    }
    function hideOverlay() {
      if (overlay) overlay.classList.remove("is-open");
    }

    var threshold = 160;
    setInterval(function () {
      var t0 = performance.now();
      // El depurador solo se detiene si las DevTools están abiertas.
      // eslint-disable-next-line no-debugger
      debugger;
      if (performance.now() - t0 > threshold) {
        showOverlay();
        setTimeout(hideOverlay, 2500);
      }
    }, 1200);
  }
})();