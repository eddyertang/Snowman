(function () {
  var searchButton = document.querySelector("[aria-controls='site-search']");
  var search = document.getElementById("site-search");
  var drawer = document.getElementById("site-drawer");
  var menuButton = document.querySelector(".menu-toggle");
  var main = document.getElementById("main");

  function setOpen(button, panel, open) {
    if (!button || !panel) return;
    button.setAttribute("aria-expanded", open ? "true" : "false");
    if (open) panel.removeAttribute("hidden");
    else panel.setAttribute("hidden", "");
  }

  if (searchButton && search) {
    searchButton.addEventListener("click", function () {
      var open = search.hasAttribute("hidden");
      setOpen(searchButton, search, open);
      if (open) {
        var input = search.querySelector("input");
        if (input) input.focus();
      }
    });
  }

  function closeDrawer() {
    setOpen(menuButton, drawer, false);
    if (main) main.removeAttribute("inert");
  }

  if (menuButton && drawer) {
    menuButton.addEventListener("click", function () {
      var open = drawer.hasAttribute("hidden");
      setOpen(menuButton, drawer, open);
      if (main) {
        if (open) main.setAttribute("inert", "");
        else main.removeAttribute("inert");
      }
      if (open) {
        var close = drawer.querySelector("[data-close-drawer]");
        if (close) close.focus();
      }
    });
    drawer.addEventListener("click", function (event) {
      if (event.target.closest("[data-close-drawer]")) closeDrawer();
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeDrawer();
      setOpen(searchButton, search, false);
      var dialog = document.getElementById("zoom-dialog");
      if (dialog && dialog.open) dialog.close();
    }
  });

  var mapButton = document.getElementById("map-load");
  if (mapButton) {
    mapButton.addEventListener("click", function () {
      var slot = document.getElementById("map-slot");
      if (!slot || slot.querySelector("iframe")) return;
      var frame = document.createElement("iframe");
      frame.title = "Map of the Shaftesbury showroom";
      frame.width = "100%";
      frame.height = "320";
      frame.loading = "lazy";
      frame.src = "https://www.openstreetmap.org/export/embed.html?bbox=-2.21%2C50.99%2C-2.16%2C51.02&layer=mapnik";
      slot.appendChild(frame);
      mapButton.hidden = true;
    });
  }

  var cookie = document.getElementById("cookie-banner");
  if (cookie && !localStorage.getItem("simali-cookie")) {
    cookie.hidden = false;
    document.body.classList.add("has-cookie");
  }
  var cookieOk = document.getElementById("cookie-ok");
  if (cookieOk) {
    cookieOk.addEventListener("click", function () {
      localStorage.setItem("simali-cookie", "1");
      cookie.hidden = true;
      document.body.classList.remove("has-cookie");
    });
  }
})();
