(function () {
  var viewport = window.visualViewport;
  if (!viewport) return;
  var base = viewport.height;
  function check() {
    var keyboard = viewport.height < base * 0.75;
    document.body.classList.toggle("is-keyboard", keyboard);
  }
  viewport.addEventListener("resize", check);
})();
