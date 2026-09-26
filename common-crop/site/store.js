/*
 * Store: the only place pages get data or change it.
 *
 * Demo: state lives in this browser (localStorage, if allowed).
 * Live: each method becomes an API call; the server runs the same engine.js
 * and owns the ledger. See README "Growth path".
 */
(function () {
  const E = window.CCEngine;
  const D = window.CC_DATA;
  const KEY = "commoncrop.demo.v3";
  const YOU = D.you.id;

  function fresh() {
    return {
      cycle: D.cycle.number,
      phase: "open",               // open -> closed (bought, awaiting pickup) -> open (next cycle)
      events: D.startingEvents.slice(),
      maxUnits: JSON.parse(JSON.stringify(D.startingMax)),
      delivery: false,
      delegations: [
        { from: "sam", to: YOU, cycle: D.cycle.number, status: "pending" },
      ],
      chat: D.chat.slice(),
      chatOptIn: true,
      results: null,
      notice: null,
    };
  }

  let state;
  try { state = JSON.parse(localStorage.getItem(KEY)) || fresh(); } catch (e) { state = fresh(); }

  function save() {
    try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) { /* page-view only */ }
  }

  function stamp(e) {
    return Object.assign({ at: new Date().toISOString().slice(0, 16) }, e);
  }

  function append(e) {
    state.events = E.tryAppend(state.events, stamp(e)); // throws with a readable message
    save();
  }

  const itemById = (id) => D.items.find((i) => i.id === id);

  /** UK outward code ("NG2" from "ng2 4ab", "NG24AB" or "NG2"). The inward part is always 3 chars. */
  function outwardCode(pc) {
    const t = String(pc).toUpperCase().trim().replace(/\s+/g, " ");
    if (t.includes(" ")) return t.split(" ")[0];
    return t.length > 4 && /\d[A-Z]{2}$/.test(t) ? t.slice(0, -3) : t;
  }
  const accounts = () => E.accountsFrom(state.events);
  const realMembers = () => Object.keys(D.people);

  /** Everyone's demand for an item: synthetic network + real members. */
  function demandsFor(itemId) {
    const acc = accounts();
    const real = realMembers()
      .filter((m) => acc[m] && acc[m].items[itemId] > 0)
      .map((m, i) => ({
        memberId: m,
        pot: D.you.pot,
        budget: acc[m].items[itemId],
        maxUnits: (state.maxUnits[m] || {})[itemId] || 0, // leftovers alone don't buy anything
        seq: i,
      }));
    return D.networkDemand[itemId].concat(real);
  }

  function projection(item) {
    const demands = demandsFor(item.id);
    const r = E.solveItem(item, demands);
    const mine = r.allocations.find((a) => a.memberId === YOU) || { units: 0, cost: 0, carry: 0 };
    const potBudget = demands.filter((d) => d.pot === D.you.pot).reduce((s, d) => s + d.budget, 0);
    return {
      item, result: r, mine, potBudget,
      people: demands.length,
      hint: E.nextTierHint(item, demands, r),
      saving: E.saving(item, mine.units, mine.cost),
      networkSaving: E.saving(item, r.units, r.totalCost),
    };
  }

  window.Store = {
    data: D,
    you: YOU,
    get state() { return state; },
    itemById,
    accounts,
    account(m = YOU) { return accounts()[m] || E.emptyAccount(); },
    projections() { return D.items.map(projection); },
    projection(itemId) { return projection(itemById(itemId)); },

    pot(id = D.you.pot) { return D.pots.find((p) => p.id === id); },
    hub(id) { return D.hubs.find((h) => h.id === id); },

    potTotals() {
      const totals = Object.fromEntries(D.pots.map((p) => [p.id, 0]));
      for (const item of D.items) for (const d of demandsFor(item.id)) totals[d.pot] += d.budget;
      return totals;
    },

    // ---- credit
    topUp(amount) { append({ type: "deposit", member: YOU, amount }); },
    withdraw(amount) { append({ type: "withdraw", member: YOU, amount }); },
    move(from, to, amount) { append({ type: "move", member: YOU, from, to, amount }); },

    /** Set how much of your credit is earmarked to an item (tops up from, or returns to, unassigned). */
    setEarmark(itemId, target) {
      const current = this.account().items[itemId] || 0;
      if (target > current) this.move(E.UNASSIGNED, itemId, target - current);
      else if (target < current) this.move(itemId, E.UNASSIGNED, current - target);
    },
    setMax(itemId, n) {
      state.maxUnits[YOU] = state.maxUnits[YOU] || {};
      state.maxUnits[YOU][itemId] = Math.max(1, n);
      save();
    },
    setDelivery(on) { state.delivery = on; save(); },

    /**
     * Friendly path: "I want N of these". Sets the max and puts exactly enough
     * money on the item at today's price; any extra on the item (e.g. last
     * week's leftover) is used first, and any surplus goes back to the wallet.
     */
    setWanted(itemId, qty) {
      const item = itemById(itemId);
      const entry = Math.max(...item.tiers.map((t) => t.unitPrice));
      const price = projection(item).result.unitPrice || entry;
      const target = qty * price;
      const current = this.account().items[itemId] || 0;
      if (target > current && this.account().unassigned < target - current) {
        const e = new Error(`Add ${"£" + ((target - current - this.account().unassigned) / 100).toFixed(2)} to your wallet to get ${qty}.`);
        e.shortfall = target - current - this.account().unassigned;
        throw e;
      }
      state.maxUnits[YOU] = state.maxUnits[YOU] || {};
      state.maxUnits[YOU][itemId] = qty;
      save();
      this.setEarmark(itemId, target);
    },
    wanted(itemId) {
      return (state.maxUnits[YOU] || {})[itemId] || 0;
    },

    // ---- cycle
    closeCycle() {
      if (state.phase !== "open") return;
      const demandsByItem = Object.fromEntries(D.items.map((i) => [i.id, demandsFor(i.id)]));
      const acc = accounts();
      const delivery = state.delivery && acc[YOU] && acc[YOU].unassigned >= D.deliveryFee
        ? [{ memberId: YOU, fee: D.deliveryFee }] : [];
      const { results, events } = E.settleCycle(state.cycle, D.items, demandsByItem, delivery);
      const real = new Set(realMembers());
      let ev = state.events;
      for (const e of events) if (real.has(e.member)) ev = E.tryAppend(ev, stamp(e));
      state.events = ev;
      state.results = Object.fromEntries(Object.entries(results).map(([k, r]) => [k, {
        bought: r.bought, unitPrice: r.unitPrice, units: r.units, cases: r.cases, people: r.allocations.length,
        mine: r.allocations.find((a) => a.memberId === YOU) || null,
      }]));
      state.phase = "closed";
      state.notice = delivery.length ? "delivery" : null;
      save();
    },
    nextCycle() {
      if (state.phase !== "closed") return;
      // Anything still uncollected at the end of the window is forfeited (see Rules).
      const acc = accounts();
      for (const m of realMembers()) {
        const c = acc[m] && acc[m].committed[state.cycle];
        if (c && c.status === "awaiting") append({ type: "forfeit", member: m, cycle: state.cycle });
      }
      state.cycle += 1;
      state.phase = "open";
      // Fresh basket each week; leftover money stays on its item until reused.
      state.maxUnits[YOU] = {};
      state.results = null;
      state.delegations = state.delegations.filter((d) => d.cycle >= state.cycle);
      save();
    },

    // ---- pickup
    token(m = YOU) { return E.pickupToken(m, state.cycle); },
    holderOf(m) { return E.holderOf(m, state.cycle, state.delegations); },
    parcel(m = YOU) { return this.account(m).committed[state.cycle] || null; },
    delegationsFor(m = YOU) {
      return {
        incoming: state.delegations.filter((d) => d.to === m && d.cycle === state.cycle),
        outgoing: state.delegations.filter((d) => d.from === m && d.cycle === state.cycle),
      };
    },
    requestDelegate(to) {
      state.delegations = state.delegations.filter((d) => !(d.from === YOU && d.cycle === state.cycle));
      if (to) state.delegations.push({ from: YOU, to, cycle: state.cycle, status: "pending" });
      save();
    },
    respondDelegate(from, to, accept) {
      const d = state.delegations.find((x) => x.from === from && x.to === to && x.cycle === state.cycle);
      if (d) d.status = accept ? "accepted" : "declined";
      save();
    },
    scan(token) { return E.scan(state.events, token, state.delegations, state.cycle); },
    confirmHandover(scanResult) {
      for (const e of scanResult.events) append(e);
    },

    // ---- group
    postChat(text) {
      state.chat.push({ who: YOU, at: new Date().toLocaleTimeString("en-GB", { hour: "2-digit", minute: "2-digit" }), text });
      save();
    },
    setChatOptIn(on) { state.chatOptIn = on; save(); },

    // ---- signup: nearest pot with space
    potForPostcode(pc) {
      const ll = D.postcodes[outwardCode(pc)];
      if (!ll) return null;
      const dist = (h) => Math.hypot((h.lat - ll[0]) * 111, (h.lng - ll[1]) * 68);
      const ranked = D.hubs.slice().sort((a, b) => dist(a) - dist(b));
      for (const h of ranked) {
        const pot = D.pots.find((p) => p.hub === h.id && p.members < p.capacity);
        if (pot) return { pot, hub: h, km: dist(h) };
      }
      return { pot: null, hub: ranked[0], km: dist(ranked[0]) };
    },

    /** Founding-member sign-up. Live when config.js has Supabase details. */
    async joinWaitlist(form) {
      const cfg = window.CC_CONFIG || {};
      if (!cfg.supabaseUrl || !cfg.supabaseKey) return { ok: true, demo: true };
      const headers = { apikey: cfg.supabaseKey, "Content-Type": "application/json", Prefer: "return=minimal" };
      if (cfg.supabaseKey.startsWith("eyJ")) headers.Authorization = "Bearer " + cfg.supabaseKey; // legacy anon key
      let res;
      try {
        res = await fetch(cfg.supabaseUrl.replace(/\/$/, "") + "/rest/v1/waitlist", {
          method: "POST", headers, body: JSON.stringify(form),
        });
      } catch (e) {
        return { ok: false, error: "We couldn't reach the server. Check your connection and try again." };
      }
      if (res.status === 201) return { ok: true };
      if (res.status === 409) return { ok: true, already: true };
      return { ok: false, error: "Something went wrong saving your details. Please try again in a minute." };
    },

    reset() { state = fresh(); save(); },
  };
})();
