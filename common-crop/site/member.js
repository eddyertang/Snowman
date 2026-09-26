/* Member app: renders from Store, sends every change through Store. */
(function () {
  const S = window.Store;
  const E = window.CCEngine;
  const D = S.data;
  const { esc, gbp, gbpNice, gbpRound, savePct, compareHtml } = window.CCUI;
  const art = (id) => window.CCArt.get(id);
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  const toPence = (s) => {
    const n = parseFloat(String(s).replace(/[£,\s]/g, ""));
    return Number.isFinite(n) && n > 0 ? Math.round(n * 100) : null;
  };
  const person = (id) => (D.people[id] ? D.people[id].name : id);
  const possessive = (id) => (id === S.you ? "Your" : person(id) + "'s");
  const itemName = (id) => (id === E.UNASSIGNED ? "Wallet (free to spend)" : id === "delivery" ? "Delivery" : S.itemById(id).name);
  const qtyLabel = (item, n) => (item.unit === "kg" ? `${n} kg` : `${n} ${n === 1 ? item.unit.replace(/^\d+\s?(g|kg)\s/, "") : item.plural}`);

  function show(el, msg) { el.textContent = msg || ""; el.hidden = !msg; }

  // ---------------------------------------------------------------- tabs
  const TABS = ["home", "shop", "wallet", "pickup", "group", "rules", "hub"];
  function showTab() {
    const t = TABS.includes(location.hash.slice(1)) ? location.hash.slice(1) : "home";
    $$("[data-panel]").forEach((p) => (p.hidden = p.dataset.panel !== t));
    $$("[data-tab]").forEach((a) => a.setAttribute("aria-current", a.dataset.tab === t ? "page" : "false"));
  }

  function walletTotal() { return E.available(S.account()); }
  function awaiting() {
    const c = S.account().committed[S.state.cycle];
    return c && c.status === "awaiting" ? c : null;
  }

  // ---------------------------------------------------------------- home
  function basketLines() {
    // After close: what was bought. Before: what you've asked for, at today's price.
    const st = S.state;
    if (st.phase === "closed") {
      const c = S.account().committed[st.cycle];
      if (!c) return [];
      return Object.entries(c.items).filter(([k]) => k !== "delivery").map(([k, l]) => {
        const item = S.itemById(k);
        return { item, qty: l.units, cost: l.amount, saving: E.saving(item, l.units, l.amount) };
      });
    }
    return S.projections().filter((p) => S.wanted(p.item.id) > 0).map((p) => ({
      item: p.item, qty: p.mine.units, wanted: S.wanted(p.item.id), cost: p.mine.cost, saving: p.saving,
    }));
  }

  function renderHome() {
    const st = S.state;
    const pot = S.pot();
    const hub = S.hub(pot.hub);
    const lines = basketLines();
    const saving = lines.reduce((s, l) => s + Math.max(0, l.saving), 0);
    const spend = lines.reduce((s, l) => s + l.cost, 0);
    const closes = new Date(D.cycle.closesAt).toLocaleDateString("en-GB", { weekday: "long" });

    $("#home-when").textContent = st.phase === "open"
      ? `Orders close ${closes} at 8pm. Pick up Saturday ${pot.slot.replace("Sat ", "")} at ${hub.name}.`
      : `Your food's been bought. Pick up Saturday ${pot.slot.replace("Sat ", "")} at ${hub.name}.`;

    $("#save-card").innerHTML = saving > 0
      ? `<div class="save-art">${art("basket")}</div>
         <div><p class="save-big">You're saving <b>${gbp(saving)}</b> this week</p>
         <p class="small">on ${gbp(spend)} of food, compared with the same quality at the supermarket.</p></div>`
      : `<div class="save-art">${art("basket")}</div>
         <div><p class="save-big">Pick a few things to start saving</p><p class="small"><a href="#shop">Browse this week's food</a></p></div>`;

    $("#basket").innerHTML = lines.length ? lines.map((l) => `
      <li class="cat-${l.item.category}">
        <span class="mini-art">${art(l.item.id)}</span>
        <div><b>${esc(l.item.name)}</b>
          <span class="small muted">${l.qty ? qtyLabel(l.item, l.qty) : "Not enough yet"}${l.wanted && l.qty && l.qty < l.wanted ? ` of ${l.wanted} wanted` : ""}</span></div>
        <span class="amt">${l.qty ? gbp(l.cost) : ""}</span>
      </li>`).join("")
      : `<li class="empty">Nothing yet. <a href="#shop">See what's in season</a></li>`;

    const totals = S.potTotals();
    const network = Object.values(totals).reduce((a, b) => a + b, 0);
    const best = S.projections().filter((p) => p.result.bought && !p.hint).length;
    const fill = Math.round((pot.members / pot.capacity) * 100);
    $("#pot-card").innerHTML = `
      <div class="ring" style="--fill:${fill}"><span>${pot.members}</span></div>
      <div>
        <h2>${esc(pot.name)}</h2>
        <p class="small">${pot.members} neighbours · ${gbpRound(totals[pot.id])} in your pot</p>
        <p class="small muted">With all ${D.pots.length} pots together (${gbpRound(network)}), ${best} of ${D.items.length} items are at their best price.</p>
      </div>`;

    const last = st.chat[st.chat.length - 1];
    $("#chat-peek").innerHTML = last
      ? `<span class="avatar">${esc(D.people[last.who].initials)}</span><div><p class="small"><b>${esc(person(last.who))}</b> in your pot</p><p class="peek">${esc(last.text)}</p></div>`
      : "";

    $("#demo-actions").innerHTML = st.phase === "open"
      ? `<button type="button" class="btn btn-soft" data-action="close">Close orders and buy</button>`
      : `<button type="button" class="btn btn-soft" data-action="next">Saturday's over: start next week</button>`;
  }

  // ---------------------------------------------------------------- shop
  let filter = "all";
  function renderShop() {
    const acc = S.account();
    const open = S.state.phase === "open";
    $("#free-money").textContent = gbp(acc.unassigned);
    $("#shop-closed").hidden = open;
    $("#ref-note").textContent = D.refNote;

    $("#shop-grid").innerHTML = S.projections().filter((p) => filter === "all" || p.item.category === filter).map((p) => {
      const it = p.item, r = p.result;
      const want = S.wanted(it.id);
      const onItem = acc.items[it.id] || 0;
      const pct = savePct(it, r.unitPrice);
      const tiers = it.tiers.slice().sort((a, b) => a.minUnits - b.minUnits);
      let helper;
      if (want === 0) helper = onItem ? `${gbp(onItem)} left over from last week is on this. Choose how many to use it.` : "Tap + to add";
      else if (p.mine.units >= want) helper = `${gbp(onItem)} set aside${p.mine.carry > 0 ? `, and ${gbp(p.mine.carry)} will come back to you if the price stays` : ""}.`;
      else if (p.mine.units > 0) helper = `At today's price you'd get ${p.mine.units}. Add a little more to your wallet to get ${want}.`;
      else helper = `${gbp(onItem)} set aside. Not quite enough for one yet.`;

      return `<article class="product cat-${it.category}${want ? " in-basket" : ""}">
        <div class="product-top">
          <span class="product-art">${art(it.id)}</span>
          <div>
            <h3>${esc(it.name)}</h3>
            <p class="farm-line">${esc(it.farmer)} at ${esc(it.farm)} · ${it.miles} mi</p>
          </div>
        </div>
        <p class="price"><b>${r.bought ? gbp(r.unitPrice) : gbp(tiers[0].unitPrice)}</b> <span>per ${esc(it.unit)}</span>
          ${pct > 0 ? `<span class="save">${pct}% less</span>` : ""}</p>
        ${p.hint && p.hint.unitsShort > 0 ? `<p class="nudge"><span class="bar"><span style="width:${Math.min(96, Math.round((r.units / p.hint.tier.minUnits) * 100))}%"></span></span>${p.hint.unitsShort.toLocaleString("en-GB")} more ${esc(it.plural)} and it's ${gbp(p.hint.tier.unitPrice)}</p>`
          : `<p class="nudge nudge-done"><span class="tick" aria-hidden="true"></span> Best price unlocked</p>`}
        <div class="qty">
          <span class="qty-label">How many?</span>
          <div class="stepper big">
            <button type="button" data-want="${it.id}" data-delta="-1" ${!open || want === 0 ? "disabled" : ""} aria-label="One less">−</button>
            <output aria-live="polite">${want}</output>
            <button type="button" data-want="${it.id}" data-delta="1" ${!open ? "disabled" : ""} aria-label="One more">+</button>
          </div>
        </div>
        <p class="helper small">${helper}</p>
        <details class="how"><summary>Price steps and how we compared</summary>
          <ol class="ladder">${tiers.map((t, i) => `<li class="${i === r.tierIndex ? "on" : ""}"><b>${gbp(t.unitPrice)}</b><span>${t.minUnits.toLocaleString("en-GB")}+ ${esc(it.plural)}</span></li>`).join("")}</ol>
          <p class="small muted">${p.people} neighbours are in. Bought by the ${esc(it.caseLabel)}.</p>
          ${compareHtml(it, r.unitPrice)}
          <p class="small muted">${esc(it.story)}</p>
        </details>
      </article>`;
    }).join("");
  }

  // ---------------------------------------------------------------- wallet
  function renderWallet() {
    const acc = S.account();
    const set = Object.values(acc.items).reduce((s, n) => s + n, 0);
    const wait = awaiting();
    $("#wallet-chip").textContent = gbp(walletTotal());
    $("#wallet-card").innerHTML = `
      <p class="small">Your wallet</p>
      <p class="wallet-big">${gbp(walletTotal())}</p>
      <div class="wallet-split">
        <span><i class="dot dot-free"></i>${gbp(acc.unassigned)} free to spend</span>
        <span><i class="dot dot-set"></i>${gbp(set)} set aside for food</span>
        ${wait ? `<span><i class="dot dot-wait"></i>${gbp(wait.total)} of food waiting for you</span>` : ""}
      </div>`;

    const rows = Object.entries(acc.items);
    $("#earmarks").innerHTML = rows.length ? rows.map(([k, v]) => {
      const item = S.itemById(k);
      return `<li class="cat-${item.category}"><span class="mini-art">${art(k)}</span>
        <div><b>${esc(item.name)}</b><span class="small muted">${S.wanted(k) ? `for ${qtyLabel(item, S.wanted(k))}` : "left over, not used yet"}</span></div>
        <span class="amt">${gbp(v)}</span>
        <button type="button" class="link-btn small" data-unassign="${k}">Back to wallet</button></li>`;
    }).join("") : `<li class="empty">Nothing set aside. <a href="#shop">Choose some food</a></li>`;

    const keys = [E.UNASSIGNED, ...D.items.map((i) => i.id)];
    const opts = keys.map((k) => `<option value="${k}">${esc(itemName(k))}</option>`).join("");
    const from = $("#move-from"), to = $("#move-to");
    const fv = from.value, tv = to.value;
    from.innerHTML = opts; to.innerHTML = opts;
    from.value = fv || "strawberries"; to.value = tv || E.UNASSIGNED;

    const describe = (e) => {
      switch (e.type) {
        case "deposit": return [`Added money`, e.amount];
        case "withdraw": return [`Sent back to card`, -e.amount];
        case "move": return [`${itemName(e.from)} → ${itemName(e.to)}`, null, e.amount];
        case "commit": return [`Bought ${e.units} × ${itemName(e.item)} (week ${e.cycle})`, null, e.amount];
        case "collect": return [`Collected${e.by !== e.member ? ` by ${person(e.by)}` : ""} (week ${e.cycle})`, null];
        case "forfeit": return [`Not collected, donated (week ${e.cycle})`, null];
      }
      return [e.type, null];
    };
    $("#history").innerHTML = S.state.events.filter((e) => e.member === S.you).slice().reverse().map((e) => {
      const [text, delta, amt] = describe(e);
      return `<li><span class="small muted">${esc((e.at || "").replace("T", " "))}</span><span>${esc(text)}</span>
        <b class="${delta > 0 ? "good" : ""}">${delta ? (delta > 0 ? "+" : "") + gbp(delta) : amt ? gbp(amt) : ""}</b></li>`;
    }).join("");
  }

  // ---------------------------------------------------------------- pickup
  function drawMap() {
    const svg = $("#map");
    const pts = D.hubs.map((h) => [h.lat, h.lng]).concat(Object.values(D.postcodes));
    const lats = pts.map((p) => p[0]), lngs = pts.map((p) => p[1]);
    const minLat = Math.min(...lats) - 0.01, maxLat = Math.max(...lats) + 0.01;
    const minLng = Math.min(...lngs) - 0.015, maxLng = Math.max(...lngs) + 0.015;
    const k = Math.min(600 / ((maxLng - minLng) * 0.6), 420 / (maxLat - minLat));
    const ox = (600 - (maxLng - minLng) * 0.6 * k) / 2, oy = (420 - (maxLat - minLat) * k) / 2;
    const xy = (lat, lng) => [ox + (lng - minLng) * 0.6 * k, oy + (maxLat - lat) * k];
    const pot = S.pot();
    const you = [D.you.lat, D.you.lng];
    const marks = D.hubs.map((h) => xy(h.lat, h.lng)).concat([xy(you[0], you[1]), xy(52.9536, -1.148)]);
    const clear = (x, y) => marks.every(([mx, my]) => Math.hypot(mx - x, my - y) > 40);

    let out = "";
    for (const [code, ll] of Object.entries(D.postcodes)) {
      const [x, y] = xy(ll[0], ll[1]);
      if (clear(x, y)) out += `<text x="${x}" y="${y}" class="map-district">${code}</text>`;
    }
    const [cx, cy] = xy(52.9536, -1.148);
    out += `<circle cx="${cx}" cy="${cy}" r="3" class="map-centre"/><text x="${cx - 8}" y="${cy + 4}" class="map-label map-muted" text-anchor="end">City centre</text>`;
    const hub = S.hub(pot.hub);
    const [ux, uy] = xy(you[0], you[1]);
    const [hx, hy] = xy(hub.lat, hub.lng);
    out += `<line x1="${ux}" y1="${uy}" x2="${hx}" y2="${hy}" class="map-route"/>`;
    out += `<circle cx="${ux}" cy="${uy}" r="7" class="map-you"/><text x="${ux - 11}" y="${uy + 18}" class="map-label" text-anchor="end">You</text>`;
    for (const h of D.hubs) {
      const [x, y] = xy(h.lat, h.lng);
      out += `<g class="map-hub ${h.id === pot.hub ? "mine" : ""}"><path d="M${x} ${y} l-10 -17 a11 11 0 1 1 20 0 z"/><circle cx="${x}" cy="${y - 20}" r="4" class="map-dot"/>
        <text x="${x + 14}" y="${y - 15}" class="map-label">${esc(h.name)}</text></g>`;
    }
    svg.innerHTML = out;
  }

  let lastToken = null;
  function renderQR(token) {
    if (token === lastToken) return;
    lastToken = token;
    const el = $("#qr");
    el.innerHTML = "";
    if (window.QRCode) {
      new window.QRCode(el, { text: token, width: 200, height: 200, colorDark: "#1c2b22", colorLight: "#ffffff", correctLevel: window.QRCode.CorrectLevel.M });
    } else {
      el.innerHTML = `<p class="small muted">Couldn't draw the QR code. Show the code below instead.</p>`;
    }
  }

  function parcelHtml(memberId, parcel) {
    if (!parcel) return "";
    const lines = Object.entries(parcel.items).map(([k, l]) => {
      const item = k === "delivery" ? null : S.itemById(k);
      return `<li>${item ? `<span class="mini-art">${art(k)}</span>${esc(qtyLabel(item, l.units))} ${esc(item.name.toLowerCase())}` : "Delivery"}</li>`;
    }).join("");
    const status = parcel.status === "awaiting" ? `<span class="tag tag-ready">Ready</span>`
      : parcel.status === "collected" ? `<span class="tag tag-done">Collected${parcel.by !== memberId ? " by " + esc(parcel.by === S.you ? "you" : person(parcel.by)) : ""}</span>`
      : `<span class="tag">Not collected</span>`;
    return `<div class="parcel"><p><b>${esc(possessive(memberId))} bag</b> ${status}</p><ul>${lines}</ul></div>`;
  }

  function renderPickup() {
    const st = S.state;
    const pot = S.pot();
    const hub = S.hub(pot.hub);
    renderQR(S.token());
    $("#token").textContent = S.token();

    const { incoming, outgoing } = S.delegationsFor();
    const holder = S.holderOf(S.you);
    const covers = incoming.filter((d) => d.status === "accepted").map((d) => person(d.from));
    $("#qr-covers").innerHTML = holder !== S.you
      ? `<span class="warn">${esc(person(holder))} is collecting your bag, so this code won't release it.</span>`
      : covers.length ? `This also picks up <b>${covers.map(esc).join(" and ")}'s</b> bag.` : "";

    const parcels = [[S.you, S.parcel()]].concat(incoming.filter((d) => d.status === "accepted").map((d) => [d.from, S.parcel(d.from)]));
    $("#parcel").innerHTML = st.phase === "open"
      ? `<p class="small muted">Your bag shows up here once orders close.</p>`
      : parcels.map(([m, p]) => parcelHtml(m, p)).join("") || `<p class="small muted">Nothing bought for you this week.</p>`;

    $("#pickup-where").innerHTML = `<b>Saturday ${pot.slot.replace("Sat ", "")}</b><br>${esc(hub.name)} ${esc(hub.venue.replace(" (proposed)", "").toLowerCase())} <span class="muted small">(venue to be confirmed)</span>`;
    drawMap();
    $("#hub-rows").innerHTML = D.hubs.map((h) => {
      const slots = D.pots.filter((p) => p.hub === h.id).map((p) => `${esc(p.name.replace(h.name + " ", ""))}: ${esc(p.slot)}`).join(" · ");
      return `<li class="${h.id === pot.hub ? "mine" : ""}"><b>${esc(h.name)}</b><span class="small muted">${slots}</span>
        <a class="small" href="https://www.openstreetmap.org/?mlat=${h.lat}&mlon=${h.lng}#map=16/${h.lat}/${h.lng}" target="_blank" rel="noopener">Map</a></li>`;
    }).join("");

    $("#deleg-in").innerHTML = incoming.map((d) => `<div class="request">
        <span class="avatar">${esc(D.people[d.from].initials)}</span>
        <div><p><b>${esc(person(d.from))}</b> asked if you can pick up their bag.</p>
        ${d.status === "pending" ? `<div class="btn-row"><button type="button" class="btn btn-small" data-deleg-accept="${d.from}">Yes, I'll get it</button>
          <button type="button" class="btn btn-small btn-soft" data-deleg-decline="${d.from}">Not this week</button></div>`
        : `<span class="tag ${d.status === "accepted" ? "tag-done" : ""}">${d.status === "accepted" ? "You're picking it up" : "You said no"}</span>`}</div>
      </div>`).join("");

    const others = Object.keys(D.people).filter((m) => m !== S.you);
    const out = outgoing[0];
    $("#deleg-to").innerHTML = `<option value="">No thanks, I'll collect</option>` + others.map((m) => `<option value="${m}" ${out && out.to === m ? "selected" : ""}>${esc(person(m))}</option>`).join("");
    $("#deleg-out").innerHTML = out
      ? `<p class="small">${out.status === "pending" ? `Waiting for ${esc(person(out.to))} to say yes. <button type="button" class="link-btn" data-deleg-sim="${out.to}">(Demo: ${esc(person(out.to))} says yes)</button>`
        : out.status === "accepted" ? `<span class="tag tag-done">${esc(person(out.to))} is picking up your bag</span>` : `${esc(person(out.to))} can't this week.`}</p>` : "";

    const inArea = D.deliveryDistricts.includes(D.you.postcode.replace(/\s.*$/, ""));
    $("#delivery").checked = st.delivery;
    $("#delivery").disabled = !inArea || st.phase !== "open";
    $("#delivery-fee").textContent = gbp(D.deliveryFee);
    $("#delivery-note").textContent = inArea
      ? `Saturday afternoon, in ${D.deliveryDistricts.join(", ")}.${st.phase !== "open" ? " You can change this next week." : ""}`
      : "Not in your area yet.";
  }

  // ---------------------------------------------------------------- group
  function renderGroup() {
    const st = S.state;
    $("#group-title").textContent = S.pot().name;
    $("#chat-optin").checked = st.chatOptIn;
    $("#chat-list").innerHTML = st.chatOptIn
      ? st.chat.map((m) => `<li class="${m.who === S.you ? "me" : ""}">
          <span class="avatar" aria-hidden="true">${esc(D.people[m.who].initials)}</span>
          <div class="bubble"><p class="small"><b>${esc(person(m.who))}</b> <span class="muted">${esc(m.at)}</span></p><p>${esc(m.text)}</p></div>
        </li>`).join("")
      : `<li class="empty">Turn on "Show me in the group" to join in.</li>`;
    $("#chat-input").disabled = !st.chatOptIn;
    const list = $("#chat-list");
    list.scrollTop = list.scrollHeight;
  }

  // ---------------------------------------------------------------- hub
  let pendingScan = null;
  function doScan(token) {
    const out = $("#scan-result");
    const r = S.scan(token);
    pendingScan = r.ok ? r : null;
    if (!r.ok) {
      const reason = r.delegatedTo ? `${r.delegatedTo === S.you ? "You (the demo member) are" : esc(person(r.delegatedTo)) + " is"} collecting this bag. Scan their code instead.` : esc(r.reason);
      out.innerHTML = `<p class="error">${reason}</p>`;
      return;
    }
    out.innerHTML = `<div class="card scan-ok">
      <p><b>${r.holder === S.you ? "The demo member" : esc(person(r.holder))}</b> is picking up ${r.release.length === 1 ? "1 bag" : r.release.length + " bags"}:</p>
      ${r.release.map((p) => parcelHtml(p.memberId, p.parcel)).join("")}
      <button type="button" class="btn" data-confirm-scan>Hand over</button>
    </div>`;
  }

  // ---------------------------------------------------------------- wiring
  function renderAll() {
    $("#pot-chip").textContent = S.pot().name;
    $("#hub-closed").hidden = S.state.phase !== "open";
    renderWallet(); renderHome(); renderShop(); renderPickup(); renderGroup();
  }

  function guard(errEl, fn) {
    try { fn(); show(errEl, ""); } catch (e) { show(errEl, e.message); }
    renderAll();
  }

  document.addEventListener("click", (e) => {
    const t = e.target.closest("button");
    if (!t) return;
    const d = t.dataset;
    if (d.action === "close") { S.closeCycle(); renderAll(); }
    else if (d.action === "next") { S.nextCycle(); $("#scan-result").innerHTML = ""; renderAll(); }
    else if (d.want) guard($("#shop-error"), () => S.setWanted(d.want, Math.max(0, S.wanted(d.want) + Number(d.delta))));
    else if (d.filter) {
      filter = d.filter;
      $$("[data-filter]").forEach((b) => b.setAttribute("aria-pressed", String(b === t)));
      renderShop();
    }
    else if (d.topup) guard($("#wallet-error"), () => S.topUp(Number(d.topup)));
    else if (d.unassign) guard($("#wallet-error"), () => S.move(d.unassign, E.UNASSIGNED, S.account().items[d.unassign]));
    else if (d.delegAccept) { S.respondDelegate(d.delegAccept, S.you, true); renderAll(); }
    else if (d.delegDecline) { S.respondDelegate(d.delegDecline, S.you, false); renderAll(); }
    else if (d.delegSim) { S.respondDelegate(S.you, d.delegSim, true); renderAll(); }
    else if (d.scan) { $("#scan-input").value = S.token(d.scan); doScan(S.token(d.scan)); }
    else if (t.hasAttribute("data-confirm-scan") && pendingScan) {
      S.confirmHandover(pendingScan);
      const n = pendingScan.release.length;
      pendingScan = null;
      $("#scan-result").innerHTML = `<p class="done">Handed over ${n === 1 ? "1 bag" : n + " bags"}. All done, enjoy!</p>`;
      renderAll();
    }
    else if (t.id === "reset") { S.reset(); lastToken = null; $("#scan-result").innerHTML = ""; renderAll(); }
  });

  $("#move-form").addEventListener("submit", (e) => {
    e.preventDefault();
    guard($("#wallet-error"), () => {
      const amt = toPence($("#move-amount").value);
      if (!amt) throw new Error("Enter an amount, like 5 or 5.50.");
      S.move($("#move-from").value, $("#move-to").value, amt);
      $("#move-amount").value = "";
    });
  });
  $("#withdraw-form").addEventListener("submit", (e) => {
    e.preventDefault();
    guard($("#wallet-error"), () => {
      const amt = toPence($("#withdraw-amount").value);
      if (!amt) throw new Error("Enter an amount to send back.");
      S.withdraw(amt);
      $("#withdraw-amount").value = "";
    });
  });
  $("#deleg-to").addEventListener("change", (e) => { S.requestDelegate(e.target.value); renderAll(); });
  $("#delivery").addEventListener("change", (e) => { S.setDelivery(e.target.checked); renderAll(); });
  $("#chat-optin").addEventListener("change", (e) => { S.setChatOptIn(e.target.checked); renderGroup(); });
  $("#chat-form").addEventListener("submit", (e) => {
    e.preventDefault();
    const v = $("#chat-input").value.trim();
    if (!v) return;
    S.postChat(v);
    $("#chat-input").value = "";
    renderGroup();
  });
  $("#scan-form").addEventListener("submit", (e) => { e.preventDefault(); doScan($("#scan-input").value); });
  window.addEventListener("hashchange", () => { showTab(); window.scrollTo(0, 0); });

  showTab();
  renderAll();
})();
