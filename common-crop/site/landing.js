/* Landing page: friendly, read-only view of this week, via Store. */
(function () {
  const S = window.Store;
  const D = S.data;
  const { esc, gbp, gbpRound, savePct, compareHtml } = window.CCUI;
  const art = (id) => window.CCArt.get(id);
  const $ = (sel) => document.querySelector(sel);

  const projs = S.projections();
  const byId = Object.fromEntries(projs.map((p) => [p.item.id, p]));

  // ---- hero
  $("#crate").innerHTML = ["strawberries", "bramley", "eggs", "vegbag", "potatoes", "beef"]
    .map((id) => `<span class="crate-item cat-${S.itemById(id).category}">${art(id)}</span>`).join("");

  const straw = byId.strawberries;
  $("#float-art").innerHTML = art("strawberries");
  $("#float-title").textContent = "Strawberries just got cheaper";
  $("#float-sub").textContent = `${straw.result.units.toLocaleString("en-GB")} ${straw.item.plural} at ${gbp(straw.result.unitPrice)} each`;

  const bought = projs.filter((p) => p.result.bought);
  const avg = Math.round(bought.reduce((s, p) => s + savePct(p.item, p.result.unitPrice), 0) / bought.length);
  $("#float-save").textContent = avg + "%";

  const households = D.pots.reduce((s, p) => s + p.members, 0);
  $("#proof-count").textContent = `${households} neighbours`;
  $("#proof-pots").textContent = `${D.pots.length} local pots`;

  // ---- picks
  $("#picks-grid").innerHTML = projs.map((p) => {
    const it = p.item, r = p.result;
    const pct = savePct(it, r.unitPrice);
    const nudge = p.hint && p.hint.unitsShort > 0
      ? `<p class="nudge"><span class="bar"><span style="width:${Math.min(96, Math.round((r.units / p.hint.tier.minUnits) * 100))}%"></span></span>
          ${p.hint.unitsShort.toLocaleString("en-GB")} more ${esc(it.plural)} and it drops to <b>${gbp(p.hint.tier.unitPrice)}</b></p>`
      : `<p class="nudge nudge-done"><span class="tick" aria-hidden="true"></span> Best price unlocked</p>`;
    return `<article class="pick cat-${it.category}">
      <div class="pick-art">${art(it.id)}</div>
      <div class="pick-body">
        <h3>${esc(it.name)}</h3>
        <p class="farm-line">${esc(it.farm)} · ${it.miles} miles</p>
        <p class="price"><b>${r.bought ? gbp(r.unitPrice) : "–"}</b> <span>per ${esc(it.unit)}</span></p>
        ${pct > 0 ? `<span class="save">${pct}% less than the supermarket</span>` : ""}
        ${nudge}
        <details class="how"><summary>How we compared</summary>${compareHtml(it, r.unitPrice)}</details>
      </div>
    </article>`;
  }).join("");
  $("#ref-note").textContent = D.refNote;

  const closes = new Date(D.cycle.closesAt);
  $("#close-line").textContent = `Orders close ${closes.toLocaleDateString("en-GB", { weekday: "long" })} at 8pm.`;

  // ---- how it works + join illustrations
  document.querySelectorAll("[data-art]").forEach((el) => { el.innerHTML = art(el.dataset.art); });

  // ---- farmers
  $("#farmer-cards").innerHTML = ["beef", "strawberries", "bramley", "eggs", "potatoes", "chicken"].map((id) => S.itemById(id)).map((it) => `
    <article class="farmer cat-${it.category}">
      <span class="farmer-art">${art(it.id)}</span>
      <div>
        <h3>${esc(it.farmer)}</h3>
        <p class="farm-line">${esc(it.farm)} · ${it.miles} miles</p>
        <p>${esc(it.story)}</p>
      </div>
    </article>`).join("");

  // ---- fair split
  $("#split-bar").innerHTML = D.split.map((s) => `<span class="seg seg-${s.key}" style="flex:${s.amount}"></span>`).join("");
  $("#split-legend").innerHTML = D.split.map((s) => `<li><i class="seg-${s.key}"></i><span>${esc(s.label)}</span><b>£${s.amount.toFixed(2)}</b></li>`).join("");

  // ---- pot finder + join
  function potMessage(pc) {
    const m = S.potForPostcode(pc);
    if (!m) return "We're starting in Nottingham (NG1 to NG12). We'll let you know when a pot opens near you.";
    if (!m.pot) return `The pots at ${m.hub.name} are full, so you'd help start a new one there.`;
    const spaces = m.pot.capacity - m.pot.members;
    return `You'd be in <b>${esc(m.pot.name)}</b>, ${m.km.toFixed(1)} km away. Pick-up is ${esc(m.pot.slot)}. ${spaces} spaces left.`;
  }
  $("#finder").addEventListener("submit", (e) => {
    e.preventDefault();
    $("#finder-result").innerHTML = potMessage($("#finder-postcode").value) + ` <a href="#join">Join now</a>`;
    $("#join-postcode").value = $("#finder-postcode").value;
  });
  $("#join-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const f = e.target;
    const err = $("#join-error");
    const email = f.email.value.trim(), postcode = f.postcode.value.trim().toUpperCase();
    const problem = !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) ? "Please enter a valid email address."
      : postcode.length < 2 ? "Please enter your postcode."
      : !f.consent_updates.checked ? "Please tick the box so we can email you when your pot opens." : "";
    if (problem) { err.textContent = problem; err.hidden = false; return; }
    err.hidden = true;
    const btn = $("#join-submit");
    btn.disabled = true; btn.textContent = "Saving…";
    const res = await S.joinWaitlist({
      email, postcode,
      interests: [...f.querySelectorAll('[name="interests"]:checked')].map((x) => x.value),
      weekly_spend: f.querySelector('[name="weekly_spend"]:checked')?.value || null,
      would_collect: f.querySelector('[name="would_collect"]:checked')?.value || null,
      consent_updates: true,
      source: "landing",
    });
    btn.disabled = false; btn.textContent = "Count me in";
    if (!res.ok) { err.textContent = res.error; err.hidden = false; return; }
    f.hidden = true;
    const done = $("#join-done");
    done.hidden = false;
    done.innerHTML = (res.already ? "You're already on the list. " : "You're in! ") + potMessage(postcode)
      + (res.demo ? `<br><span class="small">Demo mode: nothing was sent.</span>` : "");
  });
})();
