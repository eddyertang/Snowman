(function () {
  var form = document.getElementById("simali-calc");
  if (!form || !window.SimaliCalc) return;
  var result = document.getElementById("calc-result");
  var rooms = form.querySelector("[data-rooms]");

  function readRooms() {
    return Array.prototype.map.call(form.querySelectorAll(".room-row"), function (row) {
      return {
        length: row.querySelector("[data-length]").value,
        width: row.querySelector("[data-width]").value,
      };
    });
  }

  function update() {
    var wastage = form.querySelector("input[name='wastage']:checked");
    var quote = window.SimaliCalc.quote({
      area: form.querySelector("[data-area]").value,
      rooms: readRooms(),
      wastage: wastage ? wastage.value : 0.1,
      price_m2: form.getAttribute("data-price"),
      pack_m2: form.getAttribute("data-pack"),
      weight_per_m2: form.getAttribute("data-weight"),
      m2_per_pallet: form.getAttribute("data-pallet"),
    });
    if (!result) return;
    if (!quote.ok) {
      result.textContent = quote.error;
      return;
    }
    var text = quote.summary;
    if (quote.total_inc_vat) {
      text += " · Materials and delivery £" + quote.total_inc_vat.toFixed(2) + " inc VAT";
    }
    if (quote.delivery_inc_vat) {
      text += " (of which delivery £" + quote.delivery_inc_vat.toFixed(2) + " inc VAT)";
    }
    result.textContent = text;
    var qty = document.querySelector("input.qty, input[name='quantity']");
    if (qty && quote.order_m2) qty.value = quote.order_m2.toFixed(1);
  }

  form.addEventListener("input", update);
  form.addEventListener("change", update);
  var add = form.querySelector("[data-add-room]");
  if (add && rooms) {
    add.addEventListener("click", function () {
      var row = rooms.querySelector(".room-row");
      var copy = row.cloneNode(true);
      copy.querySelectorAll("input").forEach(function (input) { input.value = ""; });
      rooms.appendChild(copy);
    });
  }
  form.addEventListener("click", function (event) {
    if (event.target.closest("[data-remove-room]") && rooms.querySelectorAll(".room-row").length > 1) {
      event.target.closest(".room-row").remove();
      update();
    }
  });
})();
