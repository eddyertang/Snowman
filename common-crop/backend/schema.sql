-- Common Crop: Postgres schema (ready for Supabase).
-- Money is integer pence. The ledger is append-only: balances are computed
-- from ledger_events and never stored or edited directly. The same rules run
-- in site/engine.js, so the browser preview and the server always agree.

create extension if not exists "pgcrypto";

-- Places and groups ----------------------------------------------------------

create table hubs (
  id    text primary key,
  name  text not null,
  venue text not null,
  address text,
  lat   double precision not null,
  lng   double precision not null
);

-- A pot is a local group of up to `capacity` households: one hub, one
-- collection slot, one group chat. A new pot opens when one fills.
create table pots (
  id        text primary key,
  name      text not null,
  hub_id    text not null references hubs(id),
  slot_day  smallint not null default 6,      -- Saturday
  slot_from time not null,
  slot_to   time not null,
  capacity  int  not null default 120,
  delivery_available boolean not null default false
);

create table members (
  id            uuid primary key default gen_random_uuid(),
  auth_user_id  uuid unique,                  -- supabase auth.users.id
  display_name  text not null,                -- shown in group chat
  email         text not null unique,
  postcode      text not null,
  pot_id        text not null references pots(id),
  chat_opt_in   boolean not null default false,
  wants_delivery boolean not null default false,
  stripe_customer_id text,
  created_at    timestamptz not null default now()
);

-- Supply ---------------------------------------------------------------------

create table farms (
  id       text primary key,
  name     text not null,
  village  text not null,
  miles    numeric(5,1),
  fsa_registration_ref text,
  approval_number      text,                  -- meat plant / egg packing centre
  payment_terms_days   int not null default 7
);

create table items (
  id        text primary key,
  farm_id   text not null references farms(id),
  name      text not null,
  category  text not null,
  unit      text not null,                    -- '400 g punnet'
  unit_plural text not null,
  per_unit  numeric(8,3) not null,           -- base units (kg/each) in one unit
  case_size int  not null check (case_size > 0),
  case_label text not null,                   -- 'flat of 8'
  chilled   boolean not null default false,
  active    boolean not null default true
);

-- Bulk price ladder, set per cycle (prices move with the season).
create table item_tiers (
  cycle_id   int  not null,
  item_id    text not null references items(id),
  min_units  int  not null check (min_units > 0),
  unit_price int  not null check (unit_price > 0),  -- pence, incl. margin
  farm_price int  not null check (farm_price > 0),  -- pence the farm receives
  primary key (cycle_id, item_id, min_units)
);

-- Supermarket reference prices must be like-for-like, dated and sourced
-- (comparative advertising rules).
-- One row per pack checked. The engine uses the cheapest price per base unit
-- (kg or each) per component, weighted by share (e.g. a beef share's cuts).
create table reference_prices (
  item_id    text not null references items(id),
  component  text not null default 'all',     -- 'mince', 'steak'... or 'all'
  share      numeric(4,3) not null default 1, -- component's share of our unit
  checked_on date not null,
  retailer   text not null,
  product    text not null,                   -- exact product, same quality
  basis      text not null check (basis in ('kg','each')),
  pack_size  numeric(8,3) not null check (pack_size > 0),
  pack_price int  not null check (pack_price > 0),
  primary key (item_id, component, checked_on, retailer, product)
);

-- Cycles and allocation ------------------------------------------------------

create table cycles (
  id         int primary key,
  opens_at   timestamptz not null,
  closes_at  timestamptz not null,
  collect_on date not null,
  status     text not null default 'open'
             check (status in ('open','allocating','awaiting_pickup','closed'))
);

-- Member's "most I'd use" per item; the budget is their item earmark.
create table item_preferences (
  member_id uuid not null references members(id),
  item_id   text not null references items(id),
  max_units int  not null check (max_units > 0),
  primary key (member_id, item_id)
);

-- Frozen output of the engine at close: what was bought and at which tier.
create table cycle_item_results (
  cycle_id   int  not null references cycles(id),
  item_id    text not null references items(id),
  bought     boolean not null,
  unit_price int,
  units      int not null default 0,
  cases      int not null default 0,
  engine_version text not null,
  primary key (cycle_id, item_id)
);

-- Ledger ----------------------------------------------------------------------
--   deposit  card -> unassigned          withdraw  unassigned -> card
--   move     between unassigned and item earmarks
--   commit   earmark -> committed (food bought for this member at close)
--   collect  committed -> spent (QR scanned; by = who collected)
--   forfeit  committed -> forfeited (not collected by end of window)

create table ledger_events (
  id         bigserial primary key,
  member_id  uuid not null references members(id),
  type       text not null check (type in ('deposit','move','commit','collect','forfeit','withdraw')),
  amount     int  not null default 0 check (amount >= 0),
  from_bucket text,                           -- 'unassigned' or item id
  to_bucket   text,
  item_id    text references items(id),
  units      int,
  cycle_id   int references cycles(id),
  collected_by uuid references members(id),
  stripe_ref text,                            -- payment/refund id for deposit/withdraw
  created_at timestamptz not null default now(),
  created_by text not null                    -- 'member', 'engine', 'hub:<volunteer id>'
);
create index on ledger_events (member_id, id);
create index on ledger_events (cycle_id, member_id);

-- Nobody edits history: corrections are new events.
create rule ledger_no_update as on update to ledger_events do instead nothing;
create rule ledger_no_delete as on delete to ledger_events do instead nothing;

-- Pickup ----------------------------------------------------------------------

-- Both sides must agree. Only 'accepted' moves the parcel to another holder.
create table delegations (
  cycle_id  int  not null references cycles(id),
  from_member uuid not null references members(id),
  to_member   uuid not null references members(id),
  status    text not null default 'pending' check (status in ('pending','accepted','declined','cancelled')),
  requested_at timestamptz not null default now(),
  responded_at timestamptz,
  primary key (cycle_id, from_member),
  check (from_member <> to_member)
);

create table handovers (
  id          uuid primary key default gen_random_uuid(),
  cycle_id    int  not null references cycles(id),
  holder_id   uuid not null references members(id),
  scanned_by  text not null,                  -- volunteer id
  hub_id      text not null references hubs(id),
  released_members uuid[] not null,
  scanned_at  timestamptz not null default now()
);

-- Group chat --------------------------------------------------------------------

create table chat_messages (
  id        bigserial primary key,
  pot_id    text not null references pots(id),
  member_id uuid not null references members(id),
  body      text not null check (length(body) between 1 and 500),
  created_at timestamptz not null default now(),
  removed_at timestamptz,
  removed_reason text
);

create table chat_reports (
  message_id bigint not null references chat_messages(id),
  reporter_id uuid not null references members(id),
  reason     text not null,
  created_at timestamptz not null default now(),
  resolved_at timestamptz,
  primary key (message_id, reporter_id)
);

-- Food safety records ---------------------------------------------------------

create table deliveries (
  id          uuid primary key default gen_random_uuid(),
  cycle_id    int  not null references cycles(id),
  farm_id     text not null references farms(id),
  hub_id      text not null references hubs(id),
  received_at timestamptz not null default now(),
  received_by text not null,
  batch_ref   text,
  temp_c      numeric(4,1),
  accepted    boolean not null default true,
  notes       text
);

create table temperature_logs (
  id        uuid primary key default gen_random_uuid(),
  hub_id    text not null references hubs(id),
  unit      text not null,
  temp_c    numeric(4,1) not null,
  logged_at timestamptz not null default now(),
  logged_by text not null
);

create table waitlist (
  id         uuid primary key default gen_random_uuid(),
  name       text not null,
  email      text not null,
  postcode   text not null,
  interests  text[] not null default '{}',
  created_at timestamptz not null default now()
);

create table farm_enquiries (
  id         uuid primary key default gen_random_uuid(),
  farm       text not null,
  name       text not null,
  contact    text not null,
  produce    text,
  created_at timestamptz not null default now()
);
