import assert from "node:assert/strict";
import test from "node:test";
import { createRequire } from "node:module";

const require = createRequire(import.meta.url);
const { quote } = require("../assets/js/calculator-math.js");

test("30 m² at 10% wastage is 33.0 m², 3 pallets, £210+VAT", () => {
  const result = quote({ area: 30, wastage: 0.1, price_m2: 50 });
  assert.equal(result.ok, true);
  assert.equal(result.order_m2, 33);
  assert.equal(result.pallets, 3);
  assert.equal(result.delivery_ex_vat, 210);
  assert.equal(result.delivery_inc_vat, 252);
  assert.equal(result.total_inc_vat, 1902);
  assert.equal(result.summary, "Order 33.0 m² (incl. 10% wastage) · 3 pallets · Delivery £210+VAT");
});

test("zero area orders nothing", () => {
  const result = quote({ area: 0, wastage: 0.1 });
  assert.equal(result.ok, true);
  assert.equal(result.order_m2, 0);
  assert.equal(result.pallets, 0);
  assert.equal(result.delivery_ex_vat, 0);
  assert.equal(result.total_inc_vat, null);
});

test("non-numeric area is rejected", () => {
  const result = quote({ area: "twelve", wastage: 0.1 });
  assert.equal(result.ok, false);
  assert.match(result.error, /number/i);
});

test("exact pallet multiple stays on one pallet", () => {
  const result = quote({ area: 15, wastage: 0 });
  assert.equal(result.order_m2, 15);
  assert.equal(result.pallets, 1);
  assert.equal(result.delivery_ex_vat, 70);
});

test("30 m² with no wastage is the published two-pallet example", () => {
  const result = quote({ area: 30, wastage: 0 });
  assert.equal(result.order_m2, 30);
  assert.equal(result.pallets, 2);
  assert.equal(result.delivery_ex_vat, 140);
});

test("rooms are summed before wastage", () => {
  const result = quote({
    rooms: [
      { length: 5, width: 4 },
      { length: 2, width: 2.5 },
    ],
    wastage: 0.1,
  });
  assert.equal(result.area, 25);
  assert.equal(result.order_m2, 27.5);
  assert.equal(result.pallets, 2);
});

test("packs round the order up to whole packs", () => {
  const result = quote({ area: 10.1, wastage: 0, pack_m2: 2.5 });
  assert.equal(result.packs, 5);
  assert.equal(result.order_m2, 12.5);
  assert.equal(result.pallets, 1);
});

test("weight derives m² per pallet from 1000 kg", () => {
  const result = quote({ area: 30, wastage: 0, weight_per_m2: 50 });
  assert.equal(result.m2_per_pallet, 20);
  assert.equal(result.pallets, 2);
});

test("negative measurements are rejected", () => {
  const result = quote({ area: -1, wastage: 0.1 });
  assert.equal(result.ok, false);
});
