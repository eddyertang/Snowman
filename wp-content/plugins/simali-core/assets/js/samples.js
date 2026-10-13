(function () {
  var data = window.simaliData || {};
  var status = document.getElementById("sample-status");
  var count = document.getElementById("sample-count");

  function announce(message) {
    if (status) status.textContent = message;
  }

  function remember(ids) {
    try { localStorage.setItem("simali-samples", JSON.stringify(ids)); } catch (error) {}
    if (count) count.textContent = String(ids.length);
  }

  function send(method, id) {
    return fetch(data.rest + "samples", {
      method: method,
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": data.nonce || "",
      },
      body: JSON.stringify({ product_id: id }),
    }).then(function (response) { return response.json(); });
  }

  document.addEventListener("click", function (event) {
    var add = event.target.closest("[data-add-sample]");
    var remove = event.target.closest("[data-remove-sample]");
    if (add) {
      send("POST", add.getAttribute("data-add-sample")).then(function (payload) {
        if (payload.message) announce(payload.message);
        if (payload.ids) remember(payload.ids);
        if (payload.code === "simali_sample_max") announce(payload.message);
      });
    }
    if (remove) {
      send("DELETE", remove.getAttribute("data-remove-sample")).then(function (payload) {
        if (payload.message) announce(payload.message);
        if (payload.ids) remember(payload.ids);
        var item = remove.closest("li");
        if (item) item.remove();
      });
    }
  });

  var postcode = document.getElementById("postcode-form");
  if (postcode) {
    postcode.addEventListener("submit", function (event) {
      event.preventDefault();
      var input = postcode.querySelector("input");
      fetch(data.rest + "postcode?postcode=" + encodeURIComponent(input.value))
        .then(function (response) { return response.json(); })
        .then(function (payload) {
          var slot = document.getElementById("postcode-result");
          if (slot) slot.textContent = payload.message || payload.error || "";
        });
    });
  }
})();
