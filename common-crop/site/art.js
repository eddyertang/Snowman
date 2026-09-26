/*
 * Hand-drawn-style produce illustrations as inline SVG strings.
 * Fixed produce colours (they are pictures, not UI chrome) on a tinted disc
 * that follows the theme via CSS (.art-bg).
 */
(function () {
  const S = (inner, label) =>
    `<svg class="art" viewBox="0 0 64 64" role="img" aria-label="${label}"><circle class="art-bg" cx="32" cy="32" r="31"/>${inner}</svg>`;

  const art = {
    strawberries: S(`
      <path d="M32 54c-9-6-17-15-17-24 0-7 5-11 11-11 3 0 5 1 6 2 1-1 3-2 6-2 6 0 11 4 11 11 0 9-8 18-17 24z" fill="#e5484d"/>
      <g fill="#ffd978"><circle cx="25" cy="30" r="1.3"/><circle cx="32" cy="27" r="1.3"/><circle cx="39" cy="30" r="1.3"/><circle cx="28" cy="37" r="1.3"/><circle cx="36" cy="37" r="1.3"/><circle cx="32" cy="44" r="1.3"/></g>
      <path d="M32 21c-4-5-9-5-11-3 3 1 6 3 7 5-4 0-7 2-7 4 4-1 8-2 11-2 3 0 7 1 11 2 0-2-3-4-7-4 1-2 4-4 7-5-2-2-7-2-11 3z" fill="#3a9b5c"/>
      <path d="M32 21v-7" stroke="#2f7d4f" stroke-width="2.5" stroke-linecap="round"/>`, "Strawberry"),
    bramley: S(`
      <path d="M32 22c-3-3-9-4-13 0-5 5-4 15 0 22 3 5 7 9 13 7 6 2 10-2 13-7 4-7 5-17 0-22-4-4-10-3-13 0z" fill="#8cc152"/>
      <path d="M22 30c1-3 3-5 6-5" stroke="#c7e59a" stroke-width="3" stroke-linecap="round" fill="none"/>
      <path d="M32 22c0-4 1-7 3-9" stroke="#6b4a2b" stroke-width="2.5" stroke-linecap="round" fill="none"/>
      <path d="M35 15c4-3 9-2 11 0-3 3-8 4-11 0z" fill="#3a9b5c"/>`, "Apple"),
    potatoes: S(`
      <path d="M14 36c0-9 8-15 19-15 10 0 17 5 17 13 0 9-8 14-19 14-10 0-17-4-17-12z" fill="#c9965c"/>
      <path d="M18 34c2-6 8-9 15-9" stroke="#e3bb86" stroke-width="3" stroke-linecap="round" fill="none"/>
      <g fill="#8a6035"><circle cx="26" cy="38" r="1.4"/><circle cx="37" cy="33" r="1.4"/><circle cx="41" cy="41" r="1.2"/><circle cx="31" cy="43" r="1.1"/></g>`, "Potato"),
    vegbag: S(`
      <path d="M25 26l-3-12M31 25l1-14M37 26l5-11" stroke="#3a9b5c" stroke-width="3.5" stroke-linecap="round"/>
      <path d="M40 27l6-9" stroke="#f28c28" stroke-width="5" stroke-linecap="round"/>
      <path d="M17 26h30l-3 26H20z" fill="#d9b382"/>
      <path d="M17 26h30l-1 6H18z" fill="#c49a66"/>
      <path d="M26 38h12" stroke="#a87d4c" stroke-width="2" stroke-linecap="round"/>`, "Veg bag"),
    eggs: S(`
      <ellipse cx="26" cy="36" rx="9" ry="12" fill="#f5e6cc"/>
      <ellipse cx="39" cy="38" rx="9" ry="12" fill="#d6a26b"/>
      <path d="M22 30c1-3 3-5 5-5" stroke="#fff8ea" stroke-width="2.5" stroke-linecap="round" fill="none"/>
      <path d="M35 32c1-3 3-5 5-5" stroke="#e9c49a" stroke-width="2.5" stroke-linecap="round" fill="none"/>`, "Eggs"),
    chicken: S(`
      <ellipse cx="31" cy="38" rx="15" ry="12" fill="#fbf4e8"/>
      <circle cx="41" cy="26" r="7" fill="#fbf4e8"/>
      <path d="M38 19c1-3 3-3 4-1 1-2 3-2 3 1-1 2-5 2-7 0z" fill="#e5484d"/>
      <path d="M48 26l5 2-5 2z" fill="#f2a93b"/>
      <circle cx="43" cy="25" r="1.3" fill="#2a2a2a"/>
      <path d="M20 36c4 5 10 6 15 3" stroke="#e7dccb" stroke-width="3" stroke-linecap="round" fill="none"/>
      <path d="M28 50v5M34 50v5" stroke="#f2a93b" stroke-width="2.5" stroke-linecap="round"/>`, "Hen"),
    beef: S(`
      <path d="M13 22c4-1 8 1 10 4M51 22c-4-1-8 1-10 4" stroke="#efe3cf" stroke-width="4" stroke-linecap="round" fill="none"/>
      <path d="M20 26c0-4 5-7 12-7s12 3 12 7v12c0 6-5 12-12 12s-12-6-12-12z" fill="#a0522d"/>
      <path d="M26 24c3-2 9-2 12 0-2 4-10 4-12 0z" fill="#f4ede2"/>
      <ellipse cx="32" cy="42" rx="9" ry="6.5" fill="#f2b5a8"/>
      <circle cx="29" cy="42" r="1.4" fill="#8a3b2c"/><circle cx="35" cy="42" r="1.4" fill="#8a3b2c"/>
      <circle cx="26" cy="31" r="1.8" fill="#2a2a2a"/><circle cx="38" cy="31" r="1.8" fill="#2a2a2a"/>`, "Cow"),
    basket: S(`
      <path d="M16 30h32l-4 20H20z" fill="#d9a441"/>
      <path d="M22 30c0-10 20-10 20 0" stroke="#b07f25" stroke-width="3" fill="none"/>
      <path d="M20 36h24M21 42h22" stroke="#b07f25" stroke-width="2"/>
      <circle cx="27" cy="27" r="5" fill="#e5484d"/><circle cx="36" cy="26" r="5" fill="#8cc152"/>`, "Basket"),
  };

  window.CCArt = {
    get(id) { return art[id] || art.basket; },
  };
})();
