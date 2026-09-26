/* Landing page. Read-only view of the network pot, via Store. */
(function () {
  const S = window.Store;
  const D = S.data;
  const $ = (sel, root = document) => root.querySelector(sel);
  const gbp = (p) => "£" + (p / 100).toFixed(2);
  const gbpRound = (p) => "£" + Math.round(p / 100).toLocaleString("en-GB");
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const DAY = 86400000;
  let filter = "all";

  // Roll the sample cycle forward in 14-day steps so the demo never looks expired.
  function currentCycle() {
    let closes = new Date(D.cycle.closesAt).getTime();
    let collect = new Date(D.cycle.collectOn + "T10:00:00").getTime();
    let number = D.cycle.number;
    while (closes < Date.now()) { closes += 14 * DAY; collect += 14 * DAY; number++; }
    return { number, closes: new Date(closes), collect: new Date(collect) };
  }
  const fmtDay = (d) => d.toLocaleDateString("en-GB", { weekday: "short", day: "numeric", month: "short" });

  function renderCycle() {
    const c = currentCycle();
    const ms = c.closes - Date.now();
    $("#cycle-number").textContent = String(c.number).padStart(2, "0");
    $("#cycle-close").textContent = fmtDay(c.closes) + ", 8pm";
    $("#cycle-collect").textContent = fmtDay(c.collect) + ", by pot slot";
    $("#cd-d").textContent = Math.floor(ms / DAY);
    $("#cd-h").textContent = Math.floor((ms % DAY) / 3600000);
    $("#cd-m").textContent = Math.floor((ms % 3600000) / 60000);
    const totals = S.potTotals();
    $("#stat-households").textContent = D.pots.reduce((s, p) => s + p.members, 0);
    $("#stat-pots").textContent = D.pots.length;
    $("#stat-pot").textContent = gbpRound(Object.values(totals).reduce((a, b) => a + b, 0));
  }

  function renderItems() {
    const projs = S.projections().filter((p) => filter === "all" || p.item.category === filter);
    $("#buys-grid").innerHTML = projs.map((p) => {
      const it = p.item, r = p.result;
      const tiers = it.tiers.slice().sort((a, b) => a.minUnits - b.minUnits);
      const perUnitSave = r.bought ? it.refPrice - r.unitPrice : 0;
      const pct = p.hint ? Math.min(100, Math.round((r.units / p.hint.tier.minUnits) * 100)) : 100;
      return `<article class="buy" data-status="${p.hint ? "open" : "met"}">
        <header class="buy-top">
          <span class="buy-cat">${esc(it.category)}</span>
          <span class="chip ${p.hint ? "chip-close" : "chip-met"}">${p.hint ? `Tier ${r.tierIndex + 1} of ${tiers.length}` : "Best price reached"}</span>
        </header>
        <h3>${esc(it.name)}</h3>
        <p class="buy-unit">Per ${esc(it.unit)} · bought by the ${esc(it.caseLabel)}</p>
        <p class="buy-farm">${esc(it.farm)} <span class="miles">${it.miles} mi</span></p>
        <div class="buy-price">
          <strong>${r.bought ? gbp(r.unitPrice) : "–"}</strong><span>now</span>
          ${perUnitSave > 0 ? `<span class="perkg">save ${gbp(perUnitSave)} vs ${gbp(it.refPrice)}</span>` : ""}
        </div>
        <div class="meter" aria-hidden="true"><span style="width:${pct}%"></span></div>
        <p class="meter-label">${p.hint
          ? `<b>${p.hint.unitsShort}</b> more ${esc(it.plural)} unlock <b>${gbp(p.hint.tier.unitPrice)}</b>`
          : `<b>${r.units}</b> ${esc(it.plural)} at the lowest price`}</p>
        <p class="buy-note">${p.people} people · ${gbpRound(r.totalBudget)} on this item · network saving ${gbpRound(Math.max(0, p.networkSaving))}</p>
        <div class="buy-actions"><a class="btn btn-primary" href="app.html#choose">Add money in the app</a></div>
      </article>`;
    }).join("");
  }

  function renderSplit() {
    $("#split-bar").innerHTML = D.split.map((s) => `<span class="seg seg-${s.key}" style="flex:${s.amount}"></span>`).join("");
    $("#split-legend").innerHTML = D.split.map((s) => `<li><i class="seg-${s.key}"></i><span>${esc(s.label)}</span><b>£${s.amount.toFixed(2)}</b></li>`).join("");
  }

  function renderFarms() {
    $("#farm-list").innerHTML = D.items.map((i) => `<li><b>${esc(i.farm)}</b><span>${esc(i.name)}</span><span class="miles">${i.miles} mi</span></li>`).join("");
  }

  function renderHubs() {
    $("#hub-list").innerHTML = D.hubs.map((h) => {
      const pots = D.pots.filter((p) => p.hub === h.id);
      return `<li class="hub">
        <span class="chip chip-hub-${pots.some((p) => p.members >= p.capacity) ? "pilot" : "planned"}">${pots.length} pot${pots.length > 1 ? "s" : ""}</span>
        <h3>${esc(h.name)}</h3>
        <p>${esc(h.venue)}</p>
        ${pots.map((p) => `<p class="hub-when">${esc(p.name.replace(h.name + " ", ""))}: ${esc(p.slot)} · ${p.members}/${p.capacity}</p>`).join("")}
      </li>`;
    }).join("");
  }

  function previewPot() {
    const pc = $("#join-postcode").value;
    const out = $("#join-pot");
    if (pc.trim().length < 3) { out.textContent = "Enter an NG postcode to see your pot."; return null; }
    const m = S.potForPostcode(pc);
    if (!m) { out.textContent = "We're only in Nottingham NG1–NG12 so far. Join anyway and we'll tell you when a pot opens near you."; return null; }
    out.innerHTML = m.pot
      ? `You'd join <b>${esc(m.pot.name)}</b>: ${esc(m.hub.name)}, about ${m.km.toFixed(1)} km away, ${esc(m.pot.slot)}.`
      : `Pots at ${esc(m.hub.name)} are full. You'll start a new one there.`;
    return m;
  }

  document.addEventListener("click", (e) => {
    const f = e.target.closest("[data-filter]");
    if (!f) return;
    filter = f.dataset.filter;
    document.querySelectorAll("[data-filter]").forEach((b) => b.setAttribute("aria-pressed", String(b === f)));
    renderItems();
  });
  $("#join-postcode").addEventListener("input", previewPot);
  $("#join-form").addEventListener("submit", (e) => {
    e.preventDefault();
    const form = Object.fromEntries(new FormData(e.target));
    const m = previewPot();
    const done = $("#join-done");
    done.hidden = false;
    done.textContent = `Thanks${form.name ? ", " + form.name.split(" ")[0] : ""}. ${m && m.pot ? `You're down for ${m.pot.name}. ` : ""}This is a demo, so nothing was sent.`;
  });
  $("#farm-form").addEventListener("submit", (e) => { e.preventDefault(); $("#farm-done").hidden = false; });

  renderCycle(); renderItems(); renderSplit(); renderFarms(); renderHubs();
  setInterval(renderCycle, 60000);
})();
