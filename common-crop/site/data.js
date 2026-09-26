/*
 * Sample data for the shell site. Every farm, price, person and hub is a
 * PLACEHOLDER. Shapes mirror ../backend/schema.sql. Money is integer pence.
 */
(function () {
  // Small seeded RNG so the synthetic network is the same on every load.
  function rng(seed) {
    return function () {
      seed |= 0; seed = (seed + 0x6d2b79f5) | 0;
      let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  const hubs = [
    { id: "sneinton", name: "Sneinton", venue: "Community hall (proposed)", lat: 52.9535, lng: -1.1312 },
    { id: "west-bridgford", name: "West Bridgford", venue: "Church hall (proposed)", lat: 52.9302, lng: -1.1268 },
    { id: "beeston", name: "Beeston", venue: "Café back room (proposed)", lat: 52.9262, lng: -1.2152 },
    { id: "arnold", name: "Arnold", venue: "Scout hut (proposed)", lat: 53.0050, lng: -1.1270 },
  ];

  // A pot is a local group: shared chat, one hub, one collection slot.
  // When a pot hits capacity a new one opens, sharing the hub on a new slot.
  const pots = [
    { id: "sneinton-1", name: "Sneinton Pot 1", hub: "sneinton", slot: "Sat 10–11:30am", members: 120, capacity: 120 },
    { id: "sneinton-2", name: "Sneinton Pot 2", hub: "sneinton", slot: "Sat 11:30am–1pm", members: 41, capacity: 120 },
    { id: "west-bridgford-1", name: "West Bridgford Pot 1", hub: "west-bridgford", slot: "Sat 10am–1pm", members: 118, capacity: 120 },
    { id: "beeston-1", name: "Beeston Pot 1", hub: "beeston", slot: "Sat 9am–12pm", members: 104, capacity: 120 },
    { id: "arnold-1", name: "Arnold Pot 1", hub: "arnold", slot: "Sat 10am–1pm", members: 96, capacity: 120 },
  ];

  // Approximate postcode-district centres (demo). Live: postcodes.io lookup.
  const postcodes = {
    NG1: [52.9536, -1.1480], NG2: [52.9380, -1.1320], NG3: [52.9660, -1.1290], NG4: [52.9740, -1.0820],
    NG5: [52.9960, -1.1510], NG7: [52.9460, -1.1800], NG8: [52.9580, -1.2130], NG9: [52.9250, -1.2200],
    NG11: [52.9050, -1.1550], NG12: [52.9100, -1.0600],
  };

  // tiers: the more the network buys, the lower the unit price.
  // refPrice: supermarket comparison per unit (placeholder, see refNote).
  const items = [
    { id: "strawberries", name: "British strawberries", category: "fruit", farm: "Gedling Fruit Farm", miles: 6,
      unit: "400 g punnet", plural: "punnets", caseSize: 8, caseLabel: "flat of 8", refPrice: 275,
      tiers: [{ minUnits: 8, unitPrice: 240 }, { minUnits: 160, unitPrice: 195 }, { minUnits: 480, unitPrice: 160 }, { minUnits: 1200, unitPrice: 135 }],
      synth: { count: 430, budget: [300, 1500], max: [1, 4] } },
    { id: "bramley", name: "Bramley cooking apples", category: "fruit", farm: "Minster Orchard, Southwell", miles: 14,
      unit: "kg", plural: "kg", caseSize: 10, caseLabel: "10 kg box", refPrice: 220,
      tiers: [{ minUnits: 10, unitPrice: 150 }, { minUnits: 250, unitPrice: 120 }, { minUnits: 1000, unitPrice: 95 }],
      synth: { count: 160, budget: [500, 2000], max: [4, 15] } },
    { id: "potatoes", name: "Maris Piper potatoes", category: "veg", farm: "Sandfield Growers, Ollerton", miles: 19,
      unit: "25 kg sack", plural: "sacks", caseSize: 1, caseLabel: "sack", refPrice: 1750,
      tiers: [{ minUnits: 1, unitPrice: 1100 }, { minUnits: 40, unitPrice: 950 }, { minUnits: 150, unitPrice: 800 }],
      synth: { count: 140, budget: [800, 2500], max: [1, 2] } },
    { id: "vegbag", name: "Seasonal veg bag", category: "veg", farm: "Vale Veg Co-op, Bingham", miles: 10,
      unit: "8 kg bag", plural: "bags", caseSize: 1, caseLabel: "bag", refPrice: 1800,
      tiers: [{ minUnits: 1, unitPrice: 1600 }, { minUnits: 50, unitPrice: 1400 }, { minUnits: 150, unitPrice: 1250 }],
      synth: { count: 190, budget: [1400, 3000], max: [1, 2] } },
    { id: "eggs", name: "Free-range eggs, graded", category: "eggs", farm: "Trentside Hens, Gunthorpe", miles: 9,
      unit: "tray of 30", plural: "trays", caseSize: 1, caseLabel: "tray", refPrice: 900,
      tiers: [{ minUnits: 1, unitPrice: 850 }, { minUnits: 40, unitPrice: 750 }, { minUnits: 120, unitPrice: 660 }],
      synth: { count: 210, budget: [700, 2000], max: [1, 2] } },
    { id: "chicken", name: "Whole free-range chicken", category: "meat", farm: "Clay Lane Poultry, Radcliffe", miles: 6,
      unit: "bird, ~2 kg", plural: "birds", caseSize: 6, caseLabel: "crate of 6", refPrice: 1050,
      tiers: [{ minUnits: 6, unitPrice: 1000 }, { minUnits: 60, unitPrice: 900 }, { minUnits: 180, unitPrice: 800 }],
      synth: { count: 120, budget: [900, 4000], max: [1, 4] } },
    { id: "beef", name: "Grass-fed beef share", category: "meat", farm: "Hollow Lane Farm, Cropwell Bishop", miles: 11,
      unit: "12 kg share", plural: "shares", caseSize: 8, caseLabel: "half steer (8 shares)", refPrice: 16800,
      tiers: [{ minUnits: 8, unitPrice: 13500 }, { minUnits: 16, unitPrice: 12500 }, { minUnits: 32, unitPrice: 11800 }],
      synth: { count: 34, budget: [12000, 26000], max: [1, 2] } },
  ];

  // Synthetic demand from the rest of the network, tagged by pot.
  const potWeights = pots.map((p) => p.members);
  const weightTotal = potWeights.reduce((a, b) => a + b, 0);
  function pickPot(r) {
    let x = r() * weightTotal;
    for (let i = 0; i < pots.length; i++) { x -= potWeights[i]; if (x < 0) return pots[i].id; }
    return pots[0].id;
  }
  const networkDemand = {};
  items.forEach((item, idx) => {
    const r = rng(1000 + idx);
    const [bLo, bHi] = item.synth.budget;
    const [mLo, mHi] = item.synth.max;
    networkDemand[item.id] = Array.from({ length: item.synth.count }, (_, i) => ({
      memberId: `n${idx}_${i}`,
      pot: pickPot(r),
      budget: Math.round((bLo + r() * (bHi - bLo)) / 10) * 10,
      maxUnits: mLo + Math.floor(r() * (mHi - mLo + 1)),
      seq: 10 + i,
    }));
  });

  // Real people in your pot (demo). Their ledgers exist so pickup and
  // delegation can be shown end to end.
  const people = {
    you: { name: "You", initials: "YO" },
    sam: { name: "Sam", initials: "SA" },
    priya: { name: "Priya", initials: "PR" },
    jo: { name: "Jo", initials: "JO" },
    aisha: { name: "Aisha", initials: "AI" },
  };

  const startingEvents = [
    // last cycle (3): you put £10 on strawberries, got 3 punnets at £1.60, £5.20 carried over
    { type: "deposit", member: "you", amount: 5000, at: "2026-09-18T19:02" },
    { type: "move", member: "you", from: "unassigned", to: "strawberries", amount: 1000, at: "2026-09-18T19:03" },
    { type: "commit", member: "you", cycle: 3, item: "strawberries", units: 3, amount: 480, at: "2026-09-24T20:00" },
    { type: "collect", member: "you", cycle: 3, by: "you", at: "2026-09-26T11:48" },
    // this cycle (4)
    { type: "move", member: "you", from: "unassigned", to: "eggs", amount: 800, at: "2026-09-26T13:10" },
    { type: "move", member: "you", from: "unassigned", to: "vegbag", amount: 1500, at: "2026-09-26T13:11" },
    { type: "move", member: "you", from: "unassigned", to: "bramley", amount: 600, at: "2026-09-26T13:12" },
    { type: "deposit", member: "sam", amount: 3000, at: "2026-09-25T08:30" },
    { type: "move", member: "sam", from: "unassigned", to: "eggs", amount: 800, at: "2026-09-25T08:31" },
    { type: "move", member: "sam", from: "unassigned", to: "potatoes", amount: 1100, at: "2026-09-25T08:31" },
    { type: "deposit", member: "priya", amount: 4000, at: "2026-09-25T21:14" },
    { type: "move", member: "priya", from: "unassigned", to: "strawberries", amount: 600, at: "2026-09-25T21:15" },
    { type: "move", member: "priya", from: "unassigned", to: "chicken", amount: 2000, at: "2026-09-25T21:15" },
  ];

  const startingMax = {
    you: { strawberries: 3, eggs: 1, vegbag: 1, bramley: 4 },
    sam: { eggs: 1, potatoes: 1 },
    priya: { strawberries: 2, chicken: 2 },
  };

  const chat = [
    { who: "aisha", at: "Thu 18:02", text: "Anyone doing a batch of Bramley chutney this week? I've got jars spare." },
    { who: "jo", at: "Thu 18:40", text: "Yes! Could do Sunday at the hall kitchen if 3–4 of us split the vinegar and sugar." },
    { who: "sam", at: "Fri 08:33", text: "I'm on nights Saturday. @You could you grab my eggs and spuds? Sent you a collect request." },
    { who: "priya", at: "Fri 21:20", text: "I'm driving to the hub anyway, happy to do a Hyson Green run for 2–3 people." },
  ];

  window.CC_DATA = {
    cycle: { number: 4, closesAt: "2026-10-01T20:00:00+01:00", collectOn: "2026-10-03" },
    you: { id: "you", pot: "sneinton-2", postcode: "NG2 4", lat: 52.9478, lng: -1.1395 },
    deliveryFee: 350,
    deliveryDistricts: ["NG1", "NG2", "NG3"],
    refNote: "Supermarket prices are placeholders. The live site checks three major supermarkets weekly, like-for-like (same weight, grade and production standard), and shows the date and source.",
    hubs, pots, postcodes, items, networkDemand, people, startingEvents, startingMax, chat,
    // margin split for every £10 (landing page)
    split: [
      { label: "Farmer", amount: 8.7, key: "farmer" },
      { label: "Coordinator wage", amount: 0.55, key: "wage" },
      { label: "Hub: hall, insurance, cold chain", amount: 0.4, key: "hub" },
      { label: "Card fees", amount: 0.2, key: "fees" },
      { label: "Reserve", amount: 0.15, key: "reserve" },
    ],
  };
})();
