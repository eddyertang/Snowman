/* Small shared helpers for both pages. */
(function () {
  const E = window.CCEngine;
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const gbp = (p) => (p < 0 ? "−" : "") + "£" + (Math.abs(p) / 100).toFixed(2);
  const gbpNice = (p) => (p % 100 === 0 ? "£" + (p / 100).toLocaleString("en-GB") : gbp(p));
  const gbpRound = (p) => "£" + Math.round(p / 100).toLocaleString("en-GB");
  const kgFmt = (n) => (n < 1 ? Math.round(n * 1000) + " g" : (Math.round(n * 100) / 100) + " kg");

  /** Percent cheaper than the like-for-like supermarket price, at a unit price. */
  function savePct(item, unitPrice) {
    const ref = E.referenceUnitPrice(item);
    return ref && unitPrice ? Math.round((1 - unitPrice / ref) * 100) : 0;
  }

  /** "How we compared" content: the like-for-like sum, in plain words. */
  function compareHtml(item, unitPrice) {
    const b = E.referenceBreakdown(item);
    if (!b) return "";
    const per = b.basis === "kg" ? "kg" : "egg";
    const qty = b.basis === "kg" ? kgFmt(item.perUnit) : item.perUnit + " eggs";
    const rows = b.parts.map((p) => {
      const perStr = b.basis === "kg" ? `${gbp(Math.round(p.perBase))}/kg` : `${Math.round(p.perBase * 10) / 10}p/egg`;
      return `<li><span>${b.parts.length > 1 ? Math.round(p.share * 100) + "% " : ""}${esc(p.label)}</span>
        <span class="muted">best pack: ${esc(p.pack.name)} ${gbp(p.pack.price)}</span><b>${perStr}</b></li>`;
    }).join("");
    const perTotal = b.basis === "kg" ? `${gbp(Math.round(b.perBase))}/kg` : `${Math.round(b.perBase * 10) / 10}p an egg`;
    return `<p>Compared with <b>${esc(item.compare.likeFor)}</b>, using the supermarket's best-value pack.</p>
      <ul class="sum">${rows}</ul>
      <p>${qty} at ${perTotal} = <b>${gbp(b.unitPrice)}</b> in the supermarket.${unitPrice ? ` Here: <b>${gbp(unitPrice)}</b>.` : ""}</p>`;
  }

  window.CCUI = { esc, gbp, gbpNice, gbpRound, savePct, compareHtml };
})();
