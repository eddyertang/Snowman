(function () {
  var form = document.getElementById("filters");
  var grid = document.getElementById("product-grid");
  if (!form || !grid) return;
  var shell = form.querySelector(".filters-shell");
  if (shell && window.matchMedia("(max-width: 767px)").matches && !form.querySelector("input:checked")) {
    shell.removeAttribute("open");
  }
  form.addEventListener("submit", function (event) {
    event.preventDefault();
    var data = new FormData(form);
    var params = new URLSearchParams();
    data.forEach(function (value, key) {
      if (value) params.append(key, value);
    });
    params.set("simali_partial", "1");
    var url = form.action.split("?")[0] + "?" + params.toString();
    fetch(url, { headers: { "X-Requested-With": "fetch" } })
      .then(function (response) { return response.text(); })
      .then(function (html) {
        grid.innerHTML = html;
        params.delete("simali_partial");
        var next = form.action.split("?")[0] + (params.toString() ? "?" + params.toString() : "");
        history.pushState(null, "", next);
        var status = document.getElementById("filter-status");
        if (status) status.textContent = "Filters updated.";
      });
  });
})();
