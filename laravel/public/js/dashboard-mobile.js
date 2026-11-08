(function () {
  function setupAmihMobileMenu() {
    if (window.innerWidth > 768) return;

    const sidebar = document.querySelector(".sidebar");
    const topbar = document.querySelector(".topbar");
    const layout = document.querySelector(".admin-layout, .kasir-layout");

    if (!sidebar || !topbar || !layout) return;
    if (document.getElementById("amihMobileToggle")) return;

    const backdrop = document.createElement("div");
    backdrop.className = "amih-mobile-backdrop";
    backdrop.id = "amihMobileBackdrop";
    layout.appendChild(backdrop);

    const button = document.createElement("button");
    button.type = "button";
    button.className = "amih-mobile-toggle";
    button.id = "amihMobileToggle";
    button.setAttribute("aria-label", "Buka menu navigasi");
    button.innerHTML = '<i class="bi bi-list"></i>';
    topbar.insertBefore(button, topbar.firstChild);

    function closeMenu() {
      sidebar.classList.remove("mobile-open");
      backdrop.classList.remove("show");
      button.setAttribute("aria-label", "Buka menu navigasi");
      button.innerHTML = '<i class="bi bi-list"></i>';
    }

    function toggleMenu() {
      const isOpen = sidebar.classList.toggle("mobile-open");
      backdrop.classList.toggle("show", isOpen);
      button.setAttribute("aria-label", isOpen ? "Tutup menu navigasi" : "Buka menu navigasi");
      button.innerHTML = isOpen
        ? '<i class="bi bi-x-lg"></i>'
        : '<i class="bi bi-list"></i>';
    }

    button.addEventListener("click", toggleMenu);
    backdrop.addEventListener("click", closeMenu);

    sidebar.querySelectorAll(".menu-link").forEach(function (link) {
      link.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 768) closeMenu();
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupAmihMobileMenu);
  } else {
    setupAmihMobileMenu();
  }
})();
