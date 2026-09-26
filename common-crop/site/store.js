/*
 * Data layer. The rest of the site only talks to `Store`, never to
 * CC_DATA or localStorage directly.
 *
 * Today: reads sample data and keeps the visitor's pledges in localStorage.
 * Later: replace each method body with a Supabase query / API call. The
 * methods are already async so callers won't need to change.
 *
 *   listBuys()        -> select * from crowd_buys_with_totals where cycle_id = current
 *   pledge(id, qty)   -> insert into pledges (...); Stripe SetupIntent to save card
 *   joinWaitlist(f)   -> insert into waitlist (...)
 *   farmInterest(f)   -> insert into farm_enquiries (...)
 */
(function () {
  const KEY = "commoncrop.basket.v1";
  const data = window.CC_DATA;

  function readBasket() {
    try {
      return JSON.parse(localStorage.getItem(KEY)) || {};
    } catch (e) {
      return {};
    }
  }

  function writeBasket(basket) {
    try {
      localStorage.setItem(KEY, JSON.stringify(basket));
    } catch (e) {
      /* storage blocked: basket lives for this page view only */
    }
  }

  let basket = readBasket();

  function farmById(id) {
    return data.farms.find((f) => f.id === id);
  }

  window.Store = {
    async getCycle() {
      return data.cycle;
    },
    async getStats() {
      return data.stats;
    },
    async getSplit() {
      return data.split;
    },
    async listFarms() {
      return data.farms;
    },
    async listHubs() {
      return data.hubs;
    },
    async listBuys() {
      return data.buys.map((b) => ({
        ...b,
        farm: farmById(b.farm),
        mine: basket[b.id] || 0,
        pledged: b.pledged + (basket[b.id] || 0),
      }));
    },
    async getBasket() {
      const buys = await this.listBuys();
      return buys
        .filter((b) => b.mine > 0)
        .map((b) => ({ id: b.id, name: b.name, qty: b.mine, price: b.price }));
    },
    async pledge(buyId, delta) {
      const next = Math.max(0, (basket[buyId] || 0) + delta);
      if (next === 0) delete basket[buyId];
      else basket[buyId] = next;
      writeBasket(basket);
    },
    async joinWaitlist(form) {
      // TODO: POST to backend. Demo only: nothing leaves the browser.
      return { ok: true, form };
    },
    async farmInterest(form) {
      return { ok: true, form };
    },
  };
})();
