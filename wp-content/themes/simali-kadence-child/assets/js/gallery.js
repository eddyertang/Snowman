(function () {
  var main = document.getElementById("gallery-main");
  document.querySelectorAll("[data-gallery]").forEach(function (button) {
    button.addEventListener("click", function () {
      if (main) main.src = button.getAttribute("data-gallery");
    });
  });
  var dialog = document.getElementById("zoom-dialog");
  var open = document.getElementById("zoom-open");
  var close = document.getElementById("zoom-close");
  var image = document.getElementById("zoom-image");
  if (open && dialog) {
    open.addEventListener("click", function () {
      if (image && main) image.src = main.src;
      dialog.showModal();
    });
  }
  if (close && dialog) close.addEventListener("click", function () { dialog.close(); });
})();
