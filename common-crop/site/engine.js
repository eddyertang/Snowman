/*
 * Common Crop engine: the rules for money and food, with no UI or storage.
 *
 * All money is integer pence. Everything here is deterministic, so the same
 * inputs always give the same allocation, and the server can re-run it to audit.
 *
 * 1. Pricing   Each item has bulk tiers. The network buys at the cheapest tier
 *              that members' combined money can actually reach.
 * 2. Allocation Bought units are shared out fairly (water-filling, in pledge
 *              order). Nobody gets more than their own max; unspent money
 *              stays earmarked to that item as carry-over.
 * 3. Ledger    An append-only list of events. Every balance is computed from
 *              it, so "who paid what, who got what, who holds what credit" is
 *              always provable.
 * 4. Pickup    Each member has a QR token per cycle. Scanning it releases that
 *              member's parcel plus any parcels mutually delegated to them,
 *              and settles the committed credit as spent.
 */
(function (root) {
  const UNASSIGNED = "unassigned";

  // ---------------------------------------------------------------- pricing

  function sortedTiers(item) {
    return [...item.tiers].sort((a, b) => a.minUnits - b.minUnits);
  }

  /** Units a member wants at a given unit price: capped by budget and their max. */
  function unitsWanted(d, unitPrice) {
    if (unitPrice <= 0) return d.maxUnits;
    return Math.max(0, Math.min(d.maxUnits, Math.floor(d.budget / unitPrice)));
  }

  function wholeCases(units, caseSize) {
    return Math.floor(units / caseSize) * caseSize;
  }

  /**
   * Decide the tier, units bought and each member's share for one item.
   * demands: [{ memberId, budget (pence), maxUnits, seq }]
   */
  function solveItem(item, demands) {
    const tiers = sortedTiers(item);
    const active = demands
      .filter((d) => d.budget > 0 && d.maxUnits > 0)
      .sort((a, b) => (a.seq || 0) - (b.seq || 0));
    const totalBudget = active.reduce((s, d) => s + d.budget, 0);

    // Try the cheapest (largest) tier first; take the first one demand can fill.
    for (let t = tiers.length - 1; t >= 0; t--) {
      const tier = tiers[t];
      const wants = active.map((d) => unitsWanted(d, tier.unitPrice));
      const totalWanted = wants.reduce((s, n) => s + n, 0);
      const units = wholeCases(totalWanted, item.caseSize);
      if (units > 0 && units >= tier.minUnits) {
        const shares = waterFill(wants, units);
        const allocations = active.map((d, i) => {
          const cost = shares[i] * tier.unitPrice;
          return { memberId: d.memberId, units: shares[i], cost, carry: d.budget - cost };
        });
        return {
          itemId: item.id, bought: true, tier, tierIndex: t,
          unitPrice: tier.unitPrice, units, cases: units / item.caseSize,
          totalWanted, totalBudget,
          totalCost: units * tier.unitPrice,
          allocations,
        };
      }
    }
    return {
      itemId: item.id, bought: false, tier: null, tierIndex: -1,
      unitPrice: null, units: 0, cases: 0,
      totalWanted: 0, totalBudget, totalCost: 0,
      allocations: active.map((d) => ({ memberId: d.memberId, units: 0, cost: 0, carry: d.budget })),
    };
  }

  /**
   * Share `units` across members whose wants are `wants`, as evenly as
   * possible: everyone gets up to a common level L, then the remainder goes
   * one each, in pledge order, to members who wanted more than L.
   */
  function waterFill(wants, units) {
    const total = wants.reduce((s, n) => s + n, 0);
    if (units >= total) return wants.slice();
    const sorted = wants.slice().sort((a, b) => a - b);
    let level = 0, used = 0, remaining = wants.length, i = 0;
    while (i < sorted.length) {
      const step = sorted[i] - level;
      if (used + step * remaining > units) break;
      used += step * remaining;
      level = sorted[i];
      while (i < sorted.length && sorted[i] === level) { i++; remaining--; }
    }
    level += Math.floor((units - used) / remaining);
    const shares = wants.map((w) => Math.min(w, level));
    let left = units - shares.reduce((s, n) => s + n, 0);
    for (let k = 0; k < wants.length && left > 0; k++) {
      if (wants[k] > shares[k]) { shares[k]++; left--; }
    }
    return shares;
  }

  /** How close the network is to the next cheaper tier (for "unlock" hints). */
  function nextTierHint(item, demands, result) {
    const tiers = sortedTiers(item);
    const next = tiers[result.tierIndex + 1];
    if (!next) return null;
    const wanted = demands.reduce((s, d) => s + unitsWanted(d, next.unitPrice), 0);
    return { tier: next, unitsShort: Math.max(0, next.minUnits - wholeCases(wanted, item.caseSize)) };
  }

  // ------------------------------------------------------ price comparison
  //
  // Like-for-like supermarket price for one of our units.
  //
  //   item.perUnit   how many base units (kg, or eggs) are in one of our units
  //   item.compare   { basis: 'kg'|'each', components: [{ label, share, packs }] }
  //     packs        [{ name, size (base units), price (pence) }] for the SAME
  //                  quality (e.g. British grass-fed, free-range)
  //
  // For each component we take the supermarket's cheapest price per base unit
  // across its pack sizes (usually its biggest pack), then scale up:
  //   5 kg of beef, supermarket best £8/kg  ->  5 x £8 = £40 to compare with.
  // Mixed items (a beef share, a veg bag) are a weighted basket of components.

  function bestPack(packs) {
    return packs.reduce((best, p) => (p.price / p.size < best.price / best.size ? p : best));
  }

  function referenceBreakdown(item) {
    const c = item.compare;
    if (!c) return null;
    const parts = c.components.map((comp) => {
      const pack = bestPack(comp.packs);
      return { label: comp.label, share: comp.share, pack, perBase: pack.price / pack.size };
    });
    const perBase = parts.reduce((s, p) => s + p.share * p.perBase, 0);
    return { basis: c.basis, parts, perBase, unitPrice: Math.round(perBase * item.perUnit) };
  }

  function referenceUnitPrice(item) {
    const b = referenceBreakdown(item);
    return b ? b.unitPrice : item.refPrice || 0;
  }

  function saving(item, units, cost) {
    const ref = referenceUnitPrice(item);
    return ref ? units * ref - cost : 0;
  }

  // ----------------------------------------------------------------- ledger
  //
  // Event types (amounts in pence, always positive):
  //   deposit  { member, amount }                    card -> unassigned
  //   move     { member, from, to, amount }          unassigned <-> item earmarks
  //   commit   { member, cycle, item, units, amount, from? }
  //                                                  earmark -> committed (food bought)
  //   collect  { member, cycle, by }                 committed -> spent (QR scanned)
  //   forfeit  { member, cycle }                     committed -> forfeited (not collected)
  //   withdraw { member, amount }                    unassigned -> back to card

  function emptyAccount() {
    return { unassigned: 0, items: {}, committed: {}, spent: 0, forfeited: 0, withdrawn: 0, deposited: 0 };
  }

  function bucketGet(acc, key) {
    return key === UNASSIGNED ? acc.unassigned : acc.items[key] || 0;
  }

  function bucketAdd(acc, key, amount) {
    if (key === UNASSIGNED) acc.unassigned += amount;
    else {
      acc.items[key] = (acc.items[key] || 0) + amount;
      if (acc.items[key] === 0) delete acc.items[key];
    }
  }

  function need(ok, msg) {
    if (!ok) throw new Error(msg);
  }

  function applyEvent(accounts, e) {
    need(Number.isInteger(e.amount ?? 0) && (e.amount ?? 0) >= 0, "Amounts must be whole pence and not negative");
    const acc = accounts[e.member] || (accounts[e.member] = emptyAccount());
    switch (e.type) {
      case "deposit":
        acc.unassigned += e.amount;
        acc.deposited += e.amount;
        break;
      case "move":
        need(e.from !== e.to, "Choose a different destination");
        need(bucketGet(acc, e.from) >= e.amount, `Not enough credit in ${e.from}`);
        bucketAdd(acc, e.from, -e.amount);
        bucketAdd(acc, e.to, e.amount);
        break;
      case "commit": {
        const from = e.from || e.item;
        need(bucketGet(acc, from) >= e.amount, `Not enough credit in ${from} to commit`);
        bucketAdd(acc, from, -e.amount);
        const c = acc.committed[e.cycle] || (acc.committed[e.cycle] = { items: {}, total: 0, status: "awaiting" });
        need(c.status === "awaiting", "This cycle is already settled");
        const line = c.items[e.item] || (c.items[e.item] = { units: 0, amount: 0 });
        line.units += e.units || 0;
        line.amount += e.amount;
        c.total += e.amount;
        break;
      }
      case "collect":
      case "forfeit": {
        const c = acc.committed[e.cycle];
        need(c && c.status === "awaiting", "Nothing awaiting collection for this cycle");
        if (e.type === "collect") { acc.spent += c.total; c.status = "collected"; c.by = e.by; }
        else { acc.forfeited += c.total; c.status = "forfeited"; }
        break;
      }
      case "withdraw":
        need(acc.unassigned >= e.amount, "You can only withdraw unassigned credit. Move earmarked credit back first.");
        acc.unassigned -= e.amount;
        acc.withdrawn += e.amount;
        break;
      default:
        throw new Error("Unknown event " + e.type);
    }
    return accounts;
  }

  function accountsFrom(events) {
    return events.reduce(applyEvent, {});
  }

  /** Validate an event against history without mutating it. Throws if invalid. */
  function tryAppend(events, event) {
    accountsFrom(events.concat([event]));
    return events.concat([event]);
  }

  function available(acc) {
    return acc.unassigned + Object.values(acc.items).reduce((s, n) => s + n, 0);
  }

  /**
   * Close a cycle: solve every item and produce the commit events.
   * Carry-over needs no event, because it simply stays in the item earmark.
   */
  function settleCycle(cycle, items, demandsByItem, delivery) {
    const results = {};
    const events = [];
    for (const item of items) {
      const r = solveItem(item, demandsByItem[item.id] || []);
      results[item.id] = r;
      for (const a of r.allocations) {
        if (a.units > 0) events.push({ type: "commit", member: a.memberId, cycle, item: item.id, units: a.units, amount: a.cost });
      }
    }
    for (const d of delivery || []) {
      events.push({ type: "commit", member: d.memberId, cycle, item: "delivery", units: 1, amount: d.fee, from: UNASSIGNED });
    }
    return { results, events };
  }

  // ----------------------------------------------------------------- pickup

  // Demo checksum. The live system signs tokens server-side (HMAC with a
  // rotating secret) so a screenshot of someone's code can't be forged.
  function checksum(s) {
    let h = 2166136261;
    for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
    return (h >>> 0).toString(36).slice(0, 5).toUpperCase();
  }

  function pickupToken(memberId, cycle) {
    const body = `CC1-${cycle}-${memberId}`;
    return `${body}-${checksum(body)}`;
  }

  function parseToken(token) {
    const m = /^CC1-(\d+)-([A-Za-z0-9_]+)-([A-Z0-9]+)$/.exec(String(token).trim());
    if (!m) return null;
    const body = `CC1-${m[1]}-${m[2]}`;
    if (checksum(body) !== m[3]) return null;
    return { cycle: Number(m[1]), memberId: m[2] };
  }

  /**
   * Delegations: [{ from, to, cycle, status: 'pending'|'accepted'|'declined' }]
   * Only an accepted (mutually agreed) delegation moves a parcel to another holder.
   */
  function holderOf(memberId, cycle, delegations) {
    const d = delegations.find((x) => x.from === memberId && x.cycle === cycle && x.status === "accepted");
    return d ? d.to : memberId;
  }

  /** Whose parcels does this token release? */
  function parcelsForHolder(accounts, holderId, cycle, delegations) {
    const members = Object.keys(accounts).filter((id) => holderOf(id, cycle, delegations) === holderId);
    return members
      .map((id) => ({ memberId: id, parcel: accounts[id].committed[cycle] }))
      .filter((p) => p.parcel);
  }

  /**
   * Scan at the hub. Returns the collect events to append and what to hand over.
   * If the scanned member has delegated their parcel, nothing is released to them.
   */
  function scan(events, token, delegations, cycle) {
    const t = parseToken(token);
    if (!t) return { ok: false, reason: "This code isn't valid. Ask the member to refresh their QR." };
    if (t.cycle !== cycle) return { ok: false, reason: `This code is for cycle ${t.cycle}, not cycle ${cycle}.` };
    const accounts = accountsFrom(events);
    const delegatedTo = holderOf(t.memberId, cycle, delegations);
    if (delegatedTo !== t.memberId) {
      return { ok: false, reason: `This member's parcel is being collected by ${delegatedTo}.`, delegatedTo };
    }
    const parcels = parcelsForHolder(accounts, t.memberId, cycle, delegations);
    const release = parcels.filter((p) => p.parcel.status === "awaiting");
    if (!release.length) {
      const done = parcels.find((p) => p.parcel.status !== "awaiting");
      return { ok: false, reason: done ? "Already collected." : "Nothing to collect this cycle." };
    }
    const newEvents = release.map((p) => ({ type: "collect", member: p.memberId, cycle, by: t.memberId }));
    return { ok: true, holder: t.memberId, release, events: newEvents };
  }

  const Engine = {
    UNASSIGNED, solveItem, waterFill, unitsWanted, nextTierHint, saving,
    referenceBreakdown, referenceUnitPrice,
    accountsFrom, applyEvent, tryAppend, available, emptyAccount, settleCycle,
    pickupToken, parseToken, holderOf, parcelsForHolder, scan,
  };

  if (typeof module !== "undefined" && module.exports) module.exports = Engine;
  else root.CCEngine = Engine;
})(typeof window !== "undefined" ? window : globalThis);
