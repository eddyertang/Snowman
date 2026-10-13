/**
 * m², pack and pallet maths. Shared by the product page and the unit tests.
 * Quantity is square metres. Delivery is £70 + VAT per pallet.
 */
(function (root, factory) {
  var api = factory();
  if (typeof module === "object" && module.exports) {
    module.exports = api;
  }
  root.SimaliCalc = api;
})(typeof globalThis !== "undefined" ? globalThis : this, function () {
  var DELIVERY_EX_VAT = 70;
  var VAT_RATE = 1.2;
  var DEFAULT_M2_PER_PALLET = 15;

  function fail(message) {
    return { ok: false, error: message };
  }

  function isBlank(value) {
    return value === null || value === undefined || String(value).trim() === "";
  }

  function finite(value, label) {
    if (isBlank(value)) {
      return { ok: false, empty: true };
    }
    var number = typeof value === "number" ? value : Number(String(value).replace(",", "."));
    if (!Number.isFinite(number)) {
      return fail("Enter a number for " + label + ".");
    }
    return { ok: true, value: number };
  }

  function ceilToTenth(value) {
    return Math.ceil(value * 10 - 1e-8) / 10;
  }

  function ceilUnits(value) {
    return Math.ceil(value - 1e-8);
  }

  function roundMoney(value) {
    return Math.round(value * 100) / 100;
  }

  function quote(input) {
    input = input || {};
    var wastageResult = finite(input.wastage === undefined ? 0.1 : input.wastage, "wastage");
    if (!wastageResult.ok) {
      return wastageResult;
    }
    var wastage = wastageResult.value;
    if (wastage < 0 || wastage > 0.5) {
      return fail("Choose a wastage of 5%, 10% or 15%.");
    }

    var areaResult = resolveArea(input);
    if (!areaResult.ok) {
      return areaResult;
    }
    var area = areaResult.area;
    if (area < 0) {
      return fail("Area cannot be negative.");
    }

    var orderM2 = area === 0 ? 0 : ceilToTenth(area * (1 + wastage));
    var packs = null;
    var packResult = finite(input.pack_m2, "pack size");
    if (!packResult.ok && !packResult.empty) {
      return packResult;
    }
    if (packResult.ok) {
      if (packResult.value <= 0) {
        return fail("Pack size must be greater than zero.");
      }
      if (orderM2 > 0) {
        packs = ceilUnits(orderM2 / packResult.value);
        orderM2 = roundMoney(packs * packResult.value);
      } else {
        packs = 0;
      }
    }

    var palletResult = m2PerPallet(input);
    if (!palletResult.ok) {
      return palletResult;
    }
    var perPallet = palletResult.value;
    var pallets = orderM2 === 0 ? 0 : ceilUnits(orderM2 / perPallet);
    var deliveryEx = pallets * DELIVERY_EX_VAT;
    var deliveryInc = roundMoney(deliveryEx * VAT_RATE);

    var priceResult = finite(input.price_m2, "price");
    if (!priceResult.ok && !priceResult.empty) {
      return priceResult;
    }
    var price = priceResult.ok ? priceResult.value : null;
    if (price !== null && price < 0) {
      return fail("Price cannot be negative.");
    }
    var total = price === null || price <= 0 ? null : roundMoney(orderM2 * price + deliveryInc);

    return {
      ok: true,
      area: roundMoney(area),
      wastage: wastage,
      order_m2: roundMoney(orderM2),
      packs: packs,
      m2_per_pallet: roundMoney(perPallet),
      pallets: pallets,
      delivery_ex_vat: deliveryEx,
      delivery_inc_vat: deliveryInc,
      total_inc_vat: total,
      summary: summary({
        order_m2: orderM2,
        wastage: wastage,
        pallets: pallets,
        delivery_ex_vat: deliveryEx,
      }),
    };
  }

  function resolveArea(input) {
    var rooms = Array.isArray(input.rooms) ? input.rooms : [];
    var usedRooms = false;
    var area = 0;
    for (var i = 0; i < rooms.length; i++) {
      var room = rooms[i] || {};
      var lengthBlank = isBlank(room.length);
      var widthBlank = isBlank(room.width);
      if (lengthBlank && widthBlank) {
        continue;
      }
      usedRooms = true;
      var length = finite(room.length, "length");
      var width = finite(room.width, "width");
      if (!length.ok) {
        return length;
      }
      if (!width.ok) {
        return width;
      }
      if (length.value < 0 || width.value < 0) {
        return fail("Room measurements cannot be negative.");
      }
      area += length.value * width.value;
    }
    if (usedRooms) {
      return { ok: true, area: area };
    }
    if (isBlank(input.area)) {
      return { ok: true, area: 0 };
    }
    var direct = finite(input.area, "area");
    if (!direct.ok) {
      return direct;
    }
    return { ok: true, area: direct.value };
  }

  function m2PerPallet(input) {
    var weight = finite(input.weight_per_m2, "weight");
    if (!weight.ok && !weight.empty) {
      return weight;
    }
    if (weight.ok) {
      if (weight.value <= 0) {
        return fail("Weight per m² must be greater than zero.");
      }
      return { ok: true, value: 1000 / weight.value };
    }
    var meta = finite(input.m2_per_pallet, "m² per pallet");
    if (!meta.ok && !meta.empty) {
      return meta;
    }
    if (meta.ok) {
      if (meta.value <= 0) {
        return fail("m² per pallet must be greater than zero.");
      }
      return { ok: true, value: meta.value };
    }
    return { ok: true, value: DEFAULT_M2_PER_PALLET };
  }

  function summary(result) {
    if (!result.order_m2) {
      return "Enter an area to calculate the order.";
    }
    var percent = Math.round(result.wastage * 100);
    return (
      "Order " +
      result.order_m2.toFixed(1) +
      " m² (incl. " +
      percent +
      "% wastage) · " +
      result.pallets +
      (result.pallets === 1 ? " pallet" : " pallets") +
      " · Delivery £" +
      result.delivery_ex_vat +
      "+VAT"
    );
  }

  return {
    DELIVERY_EX_VAT: DELIVERY_EX_VAT,
    quote: quote,
  };
});
