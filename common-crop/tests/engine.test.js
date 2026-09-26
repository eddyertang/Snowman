// Run: node --test common-crop/tests/*.test.js
const test = require("node:test");
const assert = require("node:assert/strict");
const E = require("../site/engine.js");

const strawberries = {
  id: "strawberries", caseSize: 8, refPrice: 275,
  tiers: [
    { minUnits: 8, unitPrice: 240 },
    { minUnits: 160, unitPrice: 195 },
    { minUnits: 480, unitPrice: 160 },
  ],
};

test("water-fill shares evenly and respects each member's max", () => {
  assert.deepEqual(E.waterFill([5, 1, 5], 11), [5, 1, 5]);
  assert.deepEqual(E.waterFill([5, 1, 5], 7), [3, 1, 3]);
  assert.deepEqual(E.waterFill([5, 1, 5], 6), [3, 1, 2]); // remainder goes in pledge order
  assert.deepEqual(E.waterFill([4, 4, 4], 0), [0, 0, 0]);
});

test("picks the cheapest tier the pot can actually fill", () => {
  // 100 people, £10 each, max 5 punnets. At £1.60, 500 wanted -> 496 (62 cases) >= 480.
  const demands = Array.from({ length: 100 }, (_, i) => ({ memberId: "m" + i, budget: 1000, maxUnits: 5, seq: i }));
  const r = E.solveItem(strawberries, demands);
  assert.equal(r.unitPrice, 160);
  assert.equal(r.units, 496);
  assert.equal(r.units % 8, 0);
  const total = r.allocations.reduce((s, a) => s + a.units, 0);
  assert.equal(total, 496);
});

test("falls back to a dearer tier when the pot is too small", () => {
  const demands = Array.from({ length: 30 }, (_, i) => ({ memberId: "m" + i, budget: 720, maxUnits: 3, seq: i }));
  const r = E.solveItem(strawberries, demands);
  assert.equal(r.unitPrice, 240); // 90 wanted at every tier -> only tier 1 (min 8) reachable
  assert.equal(r.units, 88);
});

test("money beyond what a member can use carries over, and nothing is lost", () => {
  const demands = Array.from({ length: 100 }, (_, i) => ({ memberId: "m" + i, budget: 1000, maxUnits: 5, seq: i }));
  const r = E.solveItem(strawberries, demands);
  for (const a of r.allocations) assert.equal(a.cost + a.carry, 1000);
  const carried = r.allocations.reduce((s, a) => s + a.carry, 0);
  assert.equal(carried + r.totalCost, r.totalBudget);
});

test("nothing is bought and everything carries when no tier is reachable", () => {
  const r = E.solveItem(strawberries, [{ memberId: "a", budget: 300, maxUnits: 1 }]);
  assert.equal(r.bought, false);
  assert.equal(r.allocations[0].carry, 300);
});

test("ledger: deposit, earmark, commit, collect", () => {
  let ev = [];
  ev = E.tryAppend(ev, { type: "deposit", member: "you", amount: 2000 });
  ev = E.tryAppend(ev, { type: "move", member: "you", from: "unassigned", to: "strawberries", amount: 1000 });
  ev = E.tryAppend(ev, { type: "commit", member: "you", cycle: 4, item: "strawberries", units: 3, amount: 480 });
  let acc = E.accountsFrom(ev).you;
  assert.equal(acc.items.strawberries, 520); // carry-over stays earmarked
  assert.equal(acc.unassigned, 1000);
  assert.equal(acc.committed[4].status, "awaiting");
  ev = E.tryAppend(ev, { type: "collect", member: "you", cycle: 4, by: "you" });
  acc = E.accountsFrom(ev).you;
  assert.equal(acc.spent, 480);
  assert.equal(E.available(acc), 1520);
});

test("ledger refuses overspending and withdrawing earmarked credit", () => {
  const ev = [
    { type: "deposit", member: "you", amount: 1000 },
    { type: "move", member: "you", from: "unassigned", to: "eggs", amount: 800 },
  ];
  assert.throws(() => E.tryAppend(ev, { type: "withdraw", member: "you", amount: 500 }), /unassigned/);
  assert.throws(() => E.tryAppend(ev, { type: "move", member: "you", from: "eggs", to: "beef", amount: 900 }));
  assert.throws(() => E.tryAppend(ev, { type: "collect", member: "you", cycle: 1 }));
});

test("QR scan releases own parcel plus accepted delegations only", () => {
  const cycle = 4;
  const ev = [
    { type: "deposit", member: "you", amount: 1000 },
    { type: "deposit", member: "sam", amount: 1000 },
    { type: "deposit", member: "priya", amount: 1000 },
    { type: "commit", member: "you", cycle, item: "eggs", units: 1, amount: 700, from: "unassigned" },
    { type: "commit", member: "sam", cycle, item: "eggs", units: 1, amount: 700, from: "unassigned" },
    { type: "commit", member: "priya", cycle, item: "eggs", units: 1, amount: 700, from: "unassigned" },
  ];
  const delegations = [
    { from: "sam", to: "you", cycle, status: "accepted" },
    { from: "priya", to: "you", cycle, status: "pending" },
  ];
  const r = E.scan(ev, E.pickupToken("you", cycle), delegations, cycle);
  assert.equal(r.ok, true);
  assert.deepEqual(r.release.map((p) => p.memberId).sort(), ["sam", "you"]);

  const sams = E.scan(ev, E.pickupToken("sam", cycle), delegations, cycle);
  assert.equal(sams.ok, false); // Sam handed his collection to you

  const after = ev.concat(r.events);
  assert.equal(E.scan(after, E.pickupToken("you", cycle), delegations, cycle).reason, "Already collected.");
  assert.equal(E.accountsFrom(after).sam.committed[cycle].by, "you");
});

test("tampered or wrong-cycle tokens are rejected", () => {
  const good = E.pickupToken("you", 4);
  assert.ok(E.parseToken(good));
  assert.equal(E.parseToken(good.replace("you", "sam")), null);
  assert.equal(E.scan([], E.pickupToken("you", 3), [], 4).ok, false);
});

test("settleCycle commits allocations and delivery fees", () => {
  const ev0 = [{ type: "deposit", member: "you", amount: 3000 },
    { type: "move", member: "you", from: "unassigned", to: "strawberries", amount: 2000 }];
  const { events, results } = E.settleCycle(5, [strawberries],
    { strawberries: [{ memberId: "you", budget: 2000, maxUnits: 8 }] },
    [{ memberId: "you", fee: 350 }]);
  assert.equal(results.strawberries.units, 8);
  const acc = E.accountsFrom(ev0.concat(events)).you;
  assert.equal(acc.committed[5].total, 8 * 240 + 350);
  assert.equal(acc.items.strawberries, 2000 - 8 * 240);
  assert.equal(acc.unassigned, 1000 - 350);
});
