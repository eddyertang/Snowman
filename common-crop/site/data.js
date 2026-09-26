/*
 * Sample data for the shell site.
 *
 * Every farm, price and hub here is a PLACEHOLDER. The shapes mirror the
 * tables in ../backend/schema.sql, so swapping this file for real API calls
 * (see store.js) needs no changes to the rendering code in app.js.
 */
window.CC_DATA = {
  // Fortnightly order cycle. app.js rolls these dates forward in 14-day steps
  // so the demo never shows an expired cycle.
  cycle: {
    number: 3,
    opensAt: "2026-09-25T09:00:00+01:00",
    closesAt: "2026-10-01T20:00:00+01:00",
    collectOn: "2026-10-03",
  },

  stats: { households: 142, farms: 6, avgMiles: 13 },

  // Margin split for every £10 spent (must add to 10).
  split: [
    { label: "Farmer", amount: 8.7, key: "farmer" },
    { label: "Coordinator wage", amount: 0.55, key: "wage" },
    { label: "Hub: hall, insurance, cold chain", amount: 0.4, key: "hub" },
    { label: "Card fees", amount: 0.2, key: "fees" },
    { label: "Reserve", amount: 0.15, key: "reserve" },
  ],

  farms: [
    { id: "hollow-lane", name: "Hollow Lane Farm", village: "Cropwell Bishop", miles: 11, kind: "Grass-fed beef & lamb" },
    { id: "sandfield", name: "Sandfield Growers", village: "Ollerton", miles: 19, kind: "Potatoes, carrots, onions" },
    { id: "minster-orchard", name: "Minster Orchard", village: "Southwell", miles: 14, kind: "Bramleys & dessert apples" },
    { id: "trentside", name: "Trentside Hens", village: "Gunthorpe", miles: 9, kind: "Free-range eggs (graded)" },
    { id: "vale-veg", name: "Vale Veg Co-op", village: "Bingham", miles: 10, kind: "Seasonal mixed veg" },
    { id: "clay-lane", name: "Clay Lane Poultry", village: "Radcliffe-on-Trent", miles: 6, kind: "Free-range chicken" },
  ],

  // category: veg | fruit | meat | eggs
  // A crowd-buy only goes to the farm when `pledged` reaches `target`.
  buys: [
    {
      id: "beef-half", category: "meat", farm: "hollow-lane",
      name: "Half a beef steer, shared 8 ways",
      unit: "1 share · ~12 kg mixed cuts, vacuum-packed & labelled",
      price: 125, perKg: 10.42, target: 8, pledged: 5,
      note: "Butchered at an approved cutting plant. Collect chilled or frozen.",
    },
    {
      id: "bramley", category: "fruit", farm: "minster-orchard",
      name: "Bramley cooking apples",
      unit: "10 kg box · this year's harvest",
      price: 12, perKg: 1.2, target: 25, pledged: 9,
      note: "The Bramley started in Southwell in 1809. These come from 14 miles away.",
    },
    {
      id: "potatoes", category: "veg", farm: "sandfield",
      name: "Maris Piper potatoes",
      unit: "25 kg sack · unwashed, stores for months",
      price: 9.5, perKg: 0.38, target: 40, pledged: 31,
      note: "Sherwood sandland spuds. Keep somewhere cool and dark.",
    },
    {
      id: "veg-bag", category: "veg", farm: "vale-veg",
      name: "Seasonal veg bag",
      unit: "~8 kg · squash, leeks, kale, onions, beetroot",
      price: 14, perKg: 1.75, target: 50, pledged: 38,
      note: "Contents change with what's ready that week.",
    },
    {
      id: "carrots", category: "veg", farm: "sandfield",
      name: "Carrots, grade-outs",
      unit: "10 kg sack · odd shapes, same taste",
      price: 5, perKg: 0.5, target: 30, pledged: 30,
      note: "Too wonky for supermarket spec. Target reached, so this one is ordered.",
    },
    {
      id: "eggs", category: "eggs", farm: "trentside",
      name: "Free-range eggs",
      unit: "Tray of 30 · mixed sizes, graded & stamped",
      price: 7.5, perKg: null, target: 40, pledged: 22,
      note: "From a registered packing centre, as the law requires for hub sales.",
    },
    {
      id: "chicken", category: "meat", farm: "clay-lane",
      name: "Whole free-range chickens",
      unit: "Pack of 3 · ~2 kg each, oven-ready",
      price: 27, perKg: 4.5, target: 20, pledged: 4,
      note: "Slower-grown birds. Freeze well.",
    },
    {
      id: "lamb-half", category: "meat", farm: "hollow-lane",
      name: "Half a lamb",
      unit: "1 share · ~9 kg, jointed & labelled",
      price: 95, perKg: 10.56, target: 6, pledged: 2,
      note: "Legs, shoulder, chops and mince, packed by cut.",
    },
  ],

  hubs: [
    { id: "sneinton", name: "Sneinton", venue: "Community hall (proposed)", when: "Sat 10am–1pm", status: "pilot" },
    { id: "west-bridgford", name: "West Bridgford", venue: "Church hall (proposed)", when: "Sat 10am–1pm", status: "planned" },
    { id: "beeston", name: "Beeston", venue: "Café back room (proposed)", when: "Sat 9am–12pm", status: "planned" },
    { id: "arnold", name: "Arnold", venue: "Scout hut (proposed)", when: "Sat 10am–1pm", status: "vote" },
  ],
};
