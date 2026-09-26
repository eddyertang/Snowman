/* Rendering and interaction. Reads everything through window.Store. */
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const gbp = (n) => "£" + (Number.isInteger(n) ? n : n.toFixed(2));
  const DAY = 86400000;

  const CATEGORY_LABEL = { veg: "Veg", fruit: "Fruit", meat: "Meat", eggs: "Eggs" };
  let filter = "all";

  function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  }

  // Roll the sample cycle forward in 14-day steps until it closes in the future.
  function currentCycle(cycle) {
    let closes = new Date(cycle.closesAt).getTime();
    let collect = new Date(cycle.collectOn + "T10:00:00").getTime();
    let number = cycle.number;
    const now = Date.now();
    while (closes < now) {
      closes += 14 * DAY;
      collect += 14 * DAY;
      number += 1;
    }
    return { number, closes: new Date(closes), collect: new Date(collect) };
  }

  function fmtDay(d) {
    return d.toLocaleDateString("en-GB", { weekday: "short", day: "numeric", month: "short" });
  }

  function countdown(to) {
    const ms = Math.max(0, to - Date.now());
    const d = Math.floor(ms / DAY);
    const h = Math.floor((ms % DAY) / 3600000);
    const m = Math.floor((ms % 3600000) / 60000);
    return { d, h, m };
  }

  async function renderCycle() {
    const c = currentCycle(await Store.getCycle());
    const stats = await Store.getStats();
    const t = countdown(c.closes);
    $("#cycle-number").textContent = String(c.number).padStart(2, "0");
    $("#cycle-close").textContent = fmtDay(c.closes) + ", 8pm";
    $("#cycle-collect").textContent = fmtDay(c.collect) + ", 10am–1pm";
    $("#cd-d").textContent = t.d;
    $("#cd-h").textContent = t.h;
    $("#cd-m").textContent = t.m;
    $("#stat-households").textContent = stats.households;
    $("#stat-farms").textContent = stats.farms;
    $("#stat-miles").textContent = stats.avgMiles;
  }

  function buyStatus(b) {
    if (b.pledged >= b.target) return { key: "met", label: "Target met · ordering" };
    const left = b.target - b.pledged;
    if (left <= Math.ceil(b.target * 0.25)) return { key: "close", label: left + " to go" };
    return { key: "open", label: "Open" };
  }

  async function renderBuys() {
    const buys = await Store.listBuys();
    const shown = buys.filter((b) => filter === "all" || b.category === filter);
    $("#buys-grid").innerHTML = shown
      .map((b) => {
        const s = buyStatus(b);
        const pct = Math.min(100, Math.round((b.pledged / b.target) * 100));
        const full = b.pledged >= b.target;
        return `
        <article class="buy" data-status="${s.key}">
          <header class="buy-top">
            <span class="buy-cat">${CATEGORY_LABEL[b.category]}</span>
            <span class="chip chip-${s.key}">${esc(s.label)}</span>
          </header>
          <h3>${esc(b.name)}</h3>
          <p class="buy-unit">${esc(b.unit)}</p>
          <p class="buy-farm">${esc(b.farm.name)} · ${esc(b.farm.village)} <span class="miles">${b.farm.miles} mi</span></p>
          <div class="buy-price">
            <strong>${gbp(b.price)}</strong><span>per share</span>
            ${b.perKg ? `<span class="perkg">${gbp(b.perKg)}/kg</span>` : ""}
          </div>
          <div class="meter" role="progressbar" aria-valuemin="0" aria-valuemax="${b.target}" aria-valuenow="${b.pledged}" aria-label="Shares pledged">
            <span style="width:${pct}%"></span>
          </div>
          <p class="meter-label"><b>${b.pledged}</b> of ${b.target} shares pledged</p>
          <p class="buy-note">${esc(b.note)}</p>
          <div class="buy-actions">
            ${b.mine > 0 ? `
              <div class="stepper" aria-label="Your shares">
                <button type="button" data-pledge="${b.id}" data-delta="-1" aria-label="Remove a share">−</button>
                <output>${b.mine}</output>
                <button type="button" data-pledge="${b.id}" data-delta="1" ${full ? "disabled" : ""} aria-label="Add a share">+</button>
              </div>` : `
              <button type="button" class="btn btn-primary" data-pledge="${b.id}" data-delta="1" ${full ? "disabled" : ""}>
                ${full ? "Full: next round soon" : "Pledge a share"}
              </button>`}
          </div>
        </article>`;
      })
      .join("");
  }

  async function renderBasket() {
    const items = await Store.getBasket();
    const count = items.reduce((n, i) => n + i.qty, 0);
    const total = items.reduce((n, i) => n + i.qty * i.price, 0);
    $("#basket-count").textContent = count;
    $("#basket-count").hidden = count === 0;
    $("#basket-list").innerHTML = items.length
      ? items.map((i) => `<li><span>${i.qty} × ${esc(i.name)}</span><b>${gbp(i.qty * i.price)}</b></li>`).join("")
      : `<li class="empty">No pledges yet. Pick a crowd-buy to get started.</li>`;
    $("#basket-total").textContent = gbp(total);
  }

  async function renderSplit() {
    const split = await Store.getSplit();
    $("#split-bar").innerHTML = split
      .map((s) => `<span class="seg seg-${s.key}" style="flex:${s.amount}" title="${esc(s.label)}: ${gbp(s.amount)}"></span>`)
      .join("");
    $("#split-legend").innerHTML = split
      .map((s) => `<li><i class="seg-${s.key}"></i><span>${esc(s.label)}</span><b>${gbp(s.amount)}</b></li>`)
      .join("");
  }

  async function renderFarms() {
    const farms = await Store.listFarms();
    $("#farm-list").innerHTML = farms
      .map((f) => `<li><b>${esc(f.name)}</b><span>${esc(f.kind)}</span><span class="miles">${esc(f.village)} · ${f.miles} mi</span></li>`)
      .join("");
  }

  async function renderHubs() {
    const hubs = await Store.listHubs();
    const label = { pilot: "Pilot hub", planned: "Next", vote: "Vote for it" };
    $("#hub-list").innerHTML = hubs
      .map((h) => `
        <li class="hub">
          <span class="chip chip-hub-${h.status}">${label[h.status]}</span>
          <h3>${esc(h.name)}</h3>
          <p>${esc(h.venue)}</p>
          <p class="hub-when">${esc(h.when)}</p>
        </li>`)
      .join("");
    $("#join-hub").innerHTML =
      `<option value="">Choose your nearest</option>` +
      hubs.map((h) => `<option value="${h.id}">${esc(h.name)}</option>`).join("") +
      `<option value="other">Somewhere else in Notts</option>`;
  }

  function bindEvents() {
    document.addEventListener("click", async (e) => {
      const p = e.target.closest("[data-pledge]");
      if (p) {
        await Store.pledge(p.dataset.pledge, Number(p.dataset.delta));
        await Promise.all([renderBuys(), renderBasket()]);
        return;
      }
      const f = e.target.closest("[data-filter]");
      if (f) {
        filter = f.dataset.filter;
        document.querySelectorAll("[data-filter]").forEach((b) => b.setAttribute("aria-pressed", b === f));
        renderBuys();
        return;
      }
      if (e.target.closest("#basket-open")) $("#basket").hidden = false;
      if (e.target.closest("#basket-close")) $("#basket").hidden = true;
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") $("#basket").hidden = true;
    });

    $("#join-form").addEventListener("submit", async (e) => {
      e.preventDefault();
      const form = Object.fromEntries(new FormData(e.target));
      await Store.joinWaitlist(form);
      $("#join-done").hidden = false;
      $("#join-done").textContent = `Thanks${form.name ? ", " + form.name.split(" ")[0] : ""}. This is a demo, so nothing was sent. On the live site you'd get a welcome email and first-cycle reminder.`;
    });

    $("#farm-form").addEventListener("submit", async (e) => {
      e.preventDefault();
      await Store.farmInterest(Object.fromEntries(new FormData(e.target)));
      $("#farm-done").hidden = false;
    });
  }

  async function start() {
    bindEvents();
    await Promise.all([renderCycle(), renderBuys(), renderBasket(), renderSplit(), renderFarms(), renderHubs()]);
    setInterval(renderCycle, 60000);
  }

  start();
})();
