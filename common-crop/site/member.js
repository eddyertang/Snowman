/* Member app: renders from Store, sends every change through Store. */
(function () {
  const S = window.Store;
  const E = window.CCEngine;
  const D = S.data;
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  const gbp = (p) => (p < 0 ? "−" : "") + "£" + (Math.abs(p) / 100).toFixed(2);
  const gbpShort = (p) => (p % 100 === 0 ? "£" + p / 100 : gbp(p));
  const toPence = (s) => {
    const n = parseFloat(String(s).replace(/[£,\s]/g, ""));
    return Number.isFinite(n) && n > 0 ? Math.round(n * 100) : null;
  };
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const person = (id) => (D.people[id] ? D.people[id].name : id);
  const possessive = (id) => (id === S.you ? "Your" : person(id) + "'s");
  const itemName = (id) => (id === E.UNASSIGNED ? "Unassigned" : id === "delivery" ? "Delivery" : S.itemById(id).name);

  function show(el, msg) {
    el.textContent = msg || "";
    el.hidden = !msg;
  }

  // ---------------------------------------------------------------- tabs
  const TABS = ["pot", "choose", "credit", "pickup", "group", "rules", "hub"];
  function currentTab() {
    const t = location.hash.replace("#", "");
    return TABS.includes(t) ? t : "pot";
  }
  function showTab() {
    const t = currentTab();
    $$("[data-panel]").forEach((p) => (p.hidden = p.dataset.panel !== t));
    $$("[data-tab]").forEach((a) => a.setAttribute("aria-current", a.dataset.tab === t ? "page" : "false"));
  }

  // ---------------------------------------------------------------- header
  function renderHeader() {
    const acc = S.account();
    const pot = S.pot();
    $("#hdr-credit").textContent = gbp(E.available(acc));
    $("#hdr-pot").textContent = pot.name;
    $$(".js-cycle").forEach((el) => (el.textContent = S.state.cycle));
  }

  // ---------------------------------------------------------------- pot
  function renderPot() {
    const st = S.state;
    const pot = S.pot();
    const hub = S.hub(pot.hub);
    const totals = S.potTotals();
    const network = Object.values(totals).reduce((a, b) => a + b, 0);
    const projs = S.projections();
    const yourSaving = projs.reduce((s, p) => s + p.saving, 0);

    $("#pot-title").textContent = pot.name;
    $("#pot-phase").textContent = st.phase === "open" ? "orders open" : "bought · awaiting pickup";
    $("#pot-sub").textContent = `${pot.members} of ${pot.capacity} households · collect at ${hub.name} ${hub.venue.replace(" (proposed)", "")}, ${pot.slot}`;

    $("#cycle-actions").innerHTML = st.phase === "open"
      ? `<button type="button" class="btn btn-primary" data-action="close">Close orders &amp; buy (demo)</button>`
      : `<button type="button" class="btn btn-ghost" data-action="next">End collection &amp; start cycle ${st.cycle + 1} (demo)</button>`;

    $("#pot-tiles").innerHTML = [
      ["In your pot", gbp(totals[pot.id]), "earmarked this cycle"],
      ["Network pot", gbp(network), `${D.pots.length} pots buying together`],
      ["You save", gbp(yourSaving), "vs supermarket, this cycle"],
      ["Your credit", gbp(E.available(S.account())), "available to use"],
    ].map(([k, v, s]) => `<div class="tile"><span>${k}</span><b>${v}</b><small>${s}</small></div>`).join("");

    $("#pot-items tbody").innerHTML = projs.map((p) => {
      const r = p.result;
      const done = st.results && st.results[p.item.id];
      const mine = done ? done.mine : p.mine;
      const status = r.bought
        ? `<b>${gbp(r.unitPrice)}</b> <span class="muted">/ ${esc(p.item.unit)}</span>`
        : `<span class="chip chip-close">Not enough yet</span>`;
      return `<tr>
        <td><b>${esc(p.item.name)}</b><br><span class="muted small">${p.people} people · ${esc(p.item.farm)}</span></td>
        <td>${status}${p.hint && p.hint.unitsShort > 0 ? `<br><span class="small hint">${p.hint.unitsShort} more ${esc(p.item.plural)} → ${gbp(p.hint.tier.unitPrice)}</span>` : ""}</td>
        <td class="num">${r.units ? `${r.units}<br><span class="muted small">${r.cases} × ${esc(p.item.caseLabel)}</span>` : "–"}</td>
        <td class="num">${mine && mine.units ? `${mine.units} · ${gbp(mine.cost)}` : "–"}</td>
        <td class="num">${mine && mine.carry ? gbp(mine.carry) : "–"}</td>
        <td class="num good">${p.saving > 0 ? gbp(p.saving) : "–"}</td>
      </tr>`;
    }).join("");

    $("#pot-list").innerHTML = D.pots.map((p) => {
      const pct = Math.round((p.members / p.capacity) * 100);
      const full = p.members >= p.capacity;
      return `<li class="${p.id === pot.id ? "is-you" : ""}">
        <div><b>${esc(p.name)}</b>${p.id === pot.id ? ' <span class="chip chip-met">Your pot</span>' : ""}${full ? ' <span class="chip chip-open">Full</span>' : ""}
        <span class="muted small">${esc(S.hub(p.hub).name)} · ${esc(p.slot)}</span></div>
        <div class="meter" aria-hidden="true"><span style="width:${pct}%"></span></div>
        <span class="small mono">${p.members}/${p.capacity} · ${gbp(totals[p.id])}</span>
      </li>`;
    }).join("");
  }

  // ---------------------------------------------------------------- choose
  function renderChoose() {
    const acc = S.account();
    const open = S.state.phase === "open";
    $("#choose-unassigned").textContent = gbp(acc.unassigned);
    $("#ref-note").textContent = D.refNote + " Prices include Common Crop's 15% margin.";

    $("#items-grid").innerHTML = S.projections().map((p) => {
      const it = p.item;
      const r = p.result;
      const ear = acc.items[it.id] || 0;
      const max = (S.state.maxUnits.you || {})[it.id] || 1;
      const tiers = it.tiers.slice().sort((a, b) => a.minUnits - b.minUnits);
      const ladder = tiers.map((t, i) => `<li class="${i === r.tierIndex ? "on" : i < r.tierIndex ? "past" : ""}">
          <b>${gbp(t.unitPrice)}</b><span>${t.minUnits}+</span></li>`).join("");
      const outcome = ear === 0
        ? `<p class="muted small">Not opted in. Add money to join this buy.</p>`
        : p.mine.units
          ? `<p class="outcome">You get <b>${p.mine.units} × ${esc(it.unit)}</b> for <b>${gbp(p.mine.cost)}</b>${p.mine.carry ? ` · <b>${gbp(p.mine.carry)}</b> carries over` : ""}${p.saving > 0 ? ` · saves <b class="good">${gbp(p.saving)}</b>` : ""}</p>`
          : `<p class="outcome">Not enough on this item for one ${esc(it.unit)} at ${r.unitPrice ? gbp(r.unitPrice) : "the entry price"}. Your ${gbp(ear)} carries over.</p>`;
      return `<article class="item" data-item="${it.id}">
        <header>
          <span class="buy-cat">${esc(it.category)}</span>
          <span class="miles">${esc(it.farm)} · ${it.miles} mi</span>
        </header>
        <h3>${esc(it.name)}</h3>
        <p class="muted small">Per ${esc(it.unit)} · bought by the ${esc(it.caseLabel)} · supermarket ${gbp(it.refPrice)}</p>
        <ol class="ladder" aria-label="Bulk price tiers (units bought across the network)">${ladder}</ol>
        <p class="small">${p.people} people · ${r.units} ${esc(it.plural)} bought at the current pot${p.hint && p.hint.unitsShort > 0 ? ` · <span class="hint">${p.hint.unitsShort} more unlock ${gbp(p.hint.tier.unitPrice)}</span>` : ""}</p>
        <div class="controls">
          <div class="ctl">
            <span>Money on this item</span>
            <div class="stepper">
              <button type="button" data-ear="${it.id}" data-delta="-100" ${!open || ear === 0 ? "disabled" : ""} aria-label="Remove £1">−</button>
              <output>${gbpShort(ear)}</output>
              <button type="button" data-ear="${it.id}" data-delta="100" ${!open ? "disabled" : ""} aria-label="Add £1">+</button>
            </div>
          </div>
          <div class="ctl">
            <span>Most I'd use</span>
            <div class="stepper">
              <button type="button" data-max="${it.id}" data-delta="-1" ${!open || max <= 1 ? "disabled" : ""} aria-label="Fewer">−</button>
              <output>${max}</output>
              <button type="button" data-max="${it.id}" data-delta="1" ${!open ? "disabled" : ""} aria-label="More">+</button>
            </div>
          </div>
        </div>
        ${outcome}
      </article>`;
    }).join("");
  }

  // ---------------------------------------------------------------- credit
  function renderCredit() {
    const acc = S.account();
    const committedNow = Object.values(acc.committed).filter((c) => c.status === "awaiting").reduce((s, c) => s + c.total, 0);
    $("#credit-tiles").innerHTML = [
      ["Available", gbp(E.available(acc)), "unassigned + on items"],
      ["Awaiting pickup", gbp(committedNow), "food bought for you"],
      ["Spent", gbp(acc.spent), "collected"],
      ["Withdrawn", gbp(acc.withdrawn), "back to your card"],
    ].map(([k, v, s]) => `<div class="tile"><span>${k}</span><b>${v}</b><small>${s}</small></div>`).join("");

    const cyc = acc.committed[S.state.cycle];
    const keys = [E.UNASSIGNED, ...D.items.map((i) => i.id)];
    $("#credit-items tbody").innerHTML = keys.map((k) => {
      const avail = k === E.UNASSIGNED ? acc.unassigned : acc.items[k] || 0;
      const line = cyc && cyc.status === "awaiting" && cyc.items[k];
      if (!avail && !line && k !== E.UNASSIGNED) return "";
      return `<tr>
        <td>${k === E.UNASSIGNED ? "<b>Unassigned</b>" : esc(itemName(k))}</td>
        <td class="num">${gbp(avail)}</td>
        <td class="num">${line ? `${line.units} · ${gbp(line.amount)}` : "–"}</td>
        <td>${avail && k !== E.UNASSIGNED ? `<button type="button" class="link-btn" data-unassign="${k}">Move to unassigned</button>` : ""}</td>
      </tr>`;
    }).join("");

    const opts = keys.map((k) => `<option value="${k}">${esc(itemName(k))}</option>`).join("");
    const from = $("#move-from"), to = $("#move-to");
    const fv = from.value, tv = to.value;
    from.innerHTML = opts; to.innerHTML = opts;
    from.value = fv || "strawberries"; to.value = tv || E.UNASSIGNED;

    const describe = (e) => {
      switch (e.type) {
        case "deposit": return [`Topped up`, e.amount];
        case "withdraw": return [`Withdrew to card`, -e.amount];
        case "move": return [`Moved ${itemName(e.from)} → ${itemName(e.to)}`, 0, e.amount];
        case "commit": return [`Cycle ${e.cycle}: bought ${e.units} × ${itemName(e.item)}`, 0, e.amount];
        case "collect": return [`Cycle ${e.cycle}: collected${e.by !== e.member ? ` by ${person(e.by)}` : ""}`, 0];
        case "forfeit": return [`Cycle ${e.cycle}: not collected, donated`, 0];
      }
      return [e.type, 0];
    };
    $("#ledger").innerHTML = S.state.events.filter((e) => e.member === S.you).slice().reverse().map((e) => {
      const [text, delta, amt] = describe(e);
      return `<li><span class="mono small muted">${esc((e.at || "").replace("T", " "))}</span><span>${esc(text)}</span>
        <b class="mono ${delta > 0 ? "good" : ""}">${delta ? gbp(delta) : amt ? gbp(amt) : ""}</b></li>`;
    }).join("");
  }

  // ---------------------------------------------------------------- pickup
  function drawMap() {
    const svg = $("#map");
    const pts = D.hubs.map((h) => [h.lat, h.lng]).concat(Object.values(D.postcodes));
    const lats = pts.map((p) => p[0]), lngs = pts.map((p) => p[1]);
    const minLat = Math.min(...lats) - 0.01, maxLat = Math.max(...lats) + 0.01;
    const minLng = Math.min(...lngs) - 0.015, maxLng = Math.max(...lngs) + 0.015;
    const kx = 600 / ((maxLng - minLng) * 0.6), ky = 420 / (maxLat - minLat);
    const k = Math.min(kx, ky);
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
    out += `<circle cx="${cx}" cy="${cy}" r="3" class="map-centre"/><text x="${cx - 8}" y="${cy + 4}" class="map-label muted" text-anchor="end">City centre</text>`;
    {
      const [ux, uy] = xy(you[0], you[1]);
      const hub = S.hub(pot.hub);
      const [hx, hy] = xy(hub.lat, hub.lng);
      out += `<line x1="${ux}" y1="${uy}" x2="${hx}" y2="${hy}" class="map-route"/>`;
      out += `<circle cx="${ux}" cy="${uy}" r="6" class="map-you"/><text x="${ux - 10}" y="${uy + 18}" class="map-label" text-anchor="end">You</text>`;
    }
    for (const h of D.hubs) {
      const [x, y] = xy(h.lat, h.lng);
      const mine = h.id === pot.hub;
      out += `<g class="map-hub ${mine ? "mine" : ""}"><path d="M${x} ${y} l-9 -16 a10 10 0 1 1 18 0 z"/><circle cx="${x}" cy="${y - 19}" r="3.5" class="map-dot"/>
        <text x="${x + 13}" y="${y - 14}" class="map-label">${esc(h.name)}</text></g>`;
    }
    svg.innerHTML = out;
  }

  let lastToken = null;
  function renderQR(token) {
    const el = $("#qr");
    if (token === lastToken) return;
    lastToken = token;
    el.innerHTML = "";
    if (window.QRCode) {
      const dark = getComputedStyle(document.body).getPropertyValue("--qr-dark").trim() || "#18251c";
      new window.QRCode(el, { text: token, width: 200, height: 200, colorDark: dark, colorLight: "#ffffff", correctLevel: window.QRCode.CorrectLevel.M });
    } else {
      el.innerHTML = `<p class="small muted">QR library didn't load. Show the code below instead.</p>`;
    }
  }

  function parcelHtml(memberId, parcel) {
    if (!parcel) return "";
    const lines = Object.entries(parcel.items).map(([k, l]) => `<li>${l.units} × ${esc(itemName(k))}<b>${gbp(l.amount)}</b></li>`).join("");
    const status = parcel.status === "awaiting" ? `<span class="chip chip-close">Ready to collect</span>`
      : parcel.status === "collected" ? `<span class="chip chip-met">Collected${parcel.by !== memberId ? " by " + esc(parcel.by === S.you ? "you" : person(parcel.by)) : ""}</span>`
      : `<span class="chip chip-open">Not collected</span>`;
    return `<div class="parcel"><p><b>${esc(possessive(memberId))} parcel</b> ${status}</p><ul>${lines}</ul></div>`;
  }

  function renderPickup() {
    const st = S.state;
    const pot = S.pot();
    const hub = S.hub(pot.hub);
    const token = S.token();
    const holder = S.holderOf(S.you);
    renderQR(token);
    $("#token").textContent = token;

    const { incoming, outgoing } = S.delegationsFor();
    const covers = incoming.filter((d) => d.status === "accepted").map((d) => person(d.from));
    $("#qr-covers").innerHTML = holder !== S.you
      ? `<span class="warn">Your parcel is being collected by ${esc(person(holder))}. This code won't release it.</span>`
      : covers.length ? `This code also releases: <b>${covers.map(esc).join(", ")}</b>` : "This code releases your parcel only.";

    const parcels = [[S.you, S.parcel()]].concat(incoming.filter((d) => d.status === "accepted").map((d) => [d.from, S.parcel(d.from)]));
    $("#parcel").innerHTML = st.phase === "open"
      ? `<p class="muted small">Your parcel appears here when orders close on ${new Date(D.cycle.closesAt).toLocaleDateString("en-GB", { weekday: "long" })} at 8pm.</p>`
      : parcels.map(([m, p]) => parcelHtml(m, p)).join("") || `<p class="muted small">Nothing bought for you this cycle.</p>`;

    $("#pickup-where").innerHTML = `<b>${esc(hub.name)}</b>, ${esc(hub.venue)}<br>${esc(pot.slot)} · Saturday ${new Date(D.cycle.collectOn).toLocaleDateString("en-GB", { day: "numeric", month: "short" })}`;
    drawMap();
    $("#hub-rows").innerHTML = D.hubs.map((h) => {
      const slots = D.pots.filter((p) => p.hub === h.id).map((p) => `${esc(p.name.replace(h.name + " ", ""))}: ${esc(p.slot)}`).join(" · ");
      return `<li class="${h.id === pot.hub ? "mine" : ""}"><b>${esc(h.name)}</b><span class="small muted">${esc(h.venue)}</span><span class="small">${slots}</span>
        <a class="small" href="https://www.openstreetmap.org/?mlat=${h.lat}&mlon=${h.lng}#map=16/${h.lat}/${h.lng}" target="_blank" rel="noopener">Open map</a></li>`;
    }).join("");

    $("#deleg-in").innerHTML = incoming.map((d) => `<div class="request">
        <p><b>${esc(person(d.from))}</b> asked you to collect their parcel.</p>
        ${d.status === "pending" ? `<div class="btn-row"><button type="button" class="btn btn-primary btn-small" data-deleg-accept="${d.from}">Accept</button>
          <button type="button" class="btn btn-ghost btn-small" data-deleg-decline="${d.from}">Decline</button></div>`
        : `<span class="chip ${d.status === "accepted" ? "chip-met" : "chip-open"}">${d.status === "accepted" ? "You're collecting it" : "Declined"}</span>`}
      </div>`).join("");

    const others = Object.keys(D.people).filter((m) => m !== S.you);
    const out = outgoing[0];
    $("#deleg-to").innerHTML = `<option value="">No one, I'll collect</option>` + others.map((m) => `<option value="${m}" ${out && out.to === m ? "selected" : ""}>${esc(person(m))}</option>`).join("");
    $("#deleg-out").innerHTML = out
      ? `<p class="small">${out.status === "pending" ? `Waiting for <b>${esc(person(out.to))}</b> to accept. <button type="button" class="link-btn" data-deleg-sim="${out.to}">${esc(person(out.to))} accepts (demo)</button>`
        : out.status === "accepted" ? `<span class="chip chip-met">${esc(person(out.to))} is collecting your parcel</span>` : `${esc(person(out.to))} declined.`}</p>` : "";

    const inArea = D.deliveryDistricts.includes(D.you.postcode.replace(/\s.*$/, ""));
    $("#delivery").checked = st.delivery;
    $("#delivery").disabled = !inArea || st.phase !== "open";
    $("#delivery-fee").textContent = gbp(D.deliveryFee);
    $("#delivery-note").textContent = inArea
      ? `Available in ${D.deliveryDistricts.join(", ")}. Saturday 1–5pm.${st.phase !== "open" ? " Change this next cycle." : ""}`
      : "Not available in your area yet.";
  }

  // ---------------------------------------------------------------- group
  function renderGroup() {
    const st = S.state;
    $("#group-title").textContent = S.pot().name + " group";
    $("#chat-optin").checked = st.chatOptIn;
    $("#chat-box").classList.toggle("off", !st.chatOptIn);
    $("#chat-list").innerHTML = st.chatOptIn
      ? st.chat.map((m) => `<li class="${m.who === S.you ? "me" : ""}">
          <span class="avatar" aria-hidden="true">${esc(D.people[m.who].initials)}</span>
          <div><p class="small"><b>${esc(person(m.who))}</b> <span class="muted">${esc(m.at)}</span></p><p>${esc(m.text)}</p></div>
        </li>`).join("")
      : `<li class="muted">Turn on "Show me in the group" to see and join the conversation.</li>`;
    $("#chat-input").disabled = !st.chatOptIn;
    const list = $("#chat-list");
    list.scrollTop = list.scrollHeight;
  }

  // ---------------------------------------------------------------- hub
  let pendingScan = null;
  function renderHub() {
    $("#hub-closed").hidden = S.state.phase !== "open";
  }
  function doScan(token) {
    const out = $("#scan-result");
    const r = S.scan(token);
    pendingScan = r.ok ? r : null;
    if (!r.ok) {
      const reason = r.delegatedTo ? `This parcel is being collected by ${r.delegatedTo === S.you ? "you (the demo user)" : person(r.delegatedTo)}. Scan their code instead.` : r.reason;
      out.innerHTML = `<p class="error">${esc(reason)}</p>`;
      return;
    }
    out.innerHTML = `<div class="scan-ok">
      <p><b>${esc(r.holder === S.you ? "You (the demo user) are" : person(r.holder) + " is")}</b> collecting ${r.release.length} parcel${r.release.length > 1 ? "s" : ""}:</p>
      ${r.release.map((p) => parcelHtml(p.memberId, p.parcel)).join("")}
      <button type="button" class="btn btn-primary" data-confirm-scan>Confirm handover</button>
    </div>`;
  }

  // ---------------------------------------------------------------- events
  function renderAll() {
    renderHeader(); renderPot(); renderChoose(); renderCredit(); renderPickup(); renderGroup(); renderHub();
  }

  function guard(errEl, fn) {
    try { fn(); show(errEl, ""); } catch (e) { show(errEl, e.message); }
    renderAll();
  }

  document.addEventListener("click", (e) => {
    const t = e.target.closest("button, [data-action]");
    if (!t) return;
    const d = t.dataset;
    if (d.action === "close") { S.closeCycle(); renderAll(); }
    else if (d.action === "next") { S.nextCycle(); $("#scan-result").innerHTML = ""; renderAll(); }
    else if (d.ear) guard($("#choose-error"), () => {
      const cur = S.account().items[d.ear] || 0;
      const next = Math.max(0, cur + Number(d.delta));
      if (next > cur && S.account().unassigned < next - cur) throw new Error("Not enough unassigned credit. Top up on the Credit tab or move money from another item.");
      S.setEarmark(d.ear, next);
    });
    else if (d.max) { S.setMax(d.max, ((S.state.maxUnits.you || {})[d.max] || 1) + Number(d.delta)); renderAll(); }
    else if (d.topup) guard($("#credit-error"), () => S.topUp(Number(d.topup)));
    else if (d.unassign) guard($("#credit-error"), () => S.move(d.unassign, E.UNASSIGNED, S.account().items[d.unassign]));
    else if (d.delegAccept) { S.respondDelegate(d.delegAccept, S.you, true); renderAll(); }
    else if (d.delegDecline) { S.respondDelegate(d.delegDecline, S.you, false); renderAll(); }
    else if (d.delegSim) { S.respondDelegate(S.you, d.delegSim, true); renderAll(); }
    else if (d.scan) { $("#scan-input").value = S.token(d.scan); doScan(S.token(d.scan)); }
    else if (t.hasAttribute("data-confirm-scan") && pendingScan) {
      S.confirmHandover(pendingScan);
      const names = pendingScan.release.map((p) => possessive(p.memberId).replace(/^Your$/, "your") + " parcel").join(" and ");
      pendingScan = null;
      $("#scan-result").innerHTML = `<p class="done">Handed over ${esc(names)}. Purchase complete and credit settled.</p>`;
      renderAll();
    }
    else if (t.id === "reset") { S.reset(); lastToken = null; $("#scan-result").innerHTML = ""; renderAll(); }
  });

  $("#move-form").addEventListener("submit", (e) => {
    e.preventDefault();
    guard($("#credit-error"), () => {
      const amt = toPence($("#move-amount").value);
      if (!amt) throw new Error("Enter an amount, like 5 or 5.50.");
      S.move($("#move-from").value, $("#move-to").value, amt);
      $("#move-amount").value = "";
    });
  });
  $("#withdraw-form").addEventListener("submit", (e) => {
    e.preventDefault();
    guard($("#credit-error"), () => {
      const amt = toPence($("#withdraw-amount").value);
      if (!amt) throw new Error("Enter an amount to withdraw.");
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
  window.addEventListener("hashchange", showTab);

  showTab();
  renderAll();
})();
