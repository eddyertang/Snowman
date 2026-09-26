-- Common Crop: Postgres schema (ready for Supabase).
-- Mirrors the shapes in site/data.js so the front end can switch from sample
-- data to live data by changing only site/store.js.

create extension if not exists "pgcrypto";

-- People -----------------------------------------------------------------

create table members (
  id            uuid primary key default gen_random_uuid(),
  auth_user_id  uuid unique,                -- supabase auth.users.id
  name          text not null,
  email         text not null unique,
  postcode      text not null,
  home_hub_id   text,
  stripe_customer_id text,
  is_founding   boolean not null default false,
  created_at    timestamptz not null default now()
);

create table waitlist (
  id         uuid primary key default gen_random_uuid(),
  name       text not null,
  email      text not null,
  postcode   text not null,
  hub_id     text,
  interests  text[] not null default '{}',
  created_at timestamptz not null default now()
);

-- Supply -----------------------------------------------------------------

create table farms (
  id          text primary key,             -- slug, e.g. 'minster-orchard'
  name        text not null,
  village     text not null,
  miles       numeric(5,1),
  kind        text,
  contact     text,
  -- compliance evidence we hold for this supplier
  fsa_registration_ref text,
  approval_number      text,                -- for meat / egg packing centre
  payment_terms_days   int not null default 7,
  active      boolean not null default true
);

create table farm_enquiries (
  id         uuid primary key default gen_random_uuid(),
  farm       text not null,
  name       text not null,
  contact    text not null,
  produce    text,
  created_at timestamptz not null default now()
);

create table hubs (
  id      text primary key,
  name    text not null,
  venue   text not null,
  address text,
  opens   time,
  closes  time,
  weekday smallint,                         -- 6 = Saturday
  status  text not null default 'planned' check (status in ('pilot','live','planned','vote'))
);

-- Order cycles -------------------------------------------------------------

create table cycles (
  id          serial primary key,
  number      int not null unique,
  opens_at    timestamptz not null,
  closes_at   timestamptz not null,
  collect_on  date not null,
  status      text not null default 'open' check (status in ('draft','open','closed','ordered','collected'))
);

create table crowd_buys (
  id          text not null,
  cycle_id    int not null references cycles(id),
  farm_id     text not null references farms(id),
  category    text not null check (category in ('veg','fruit','meat','eggs','dairy','other')),
  name        text not null,
  unit        text not null,
  price_pence int  not null,                -- price per share to member
  farm_price_pence int not null,            -- what the farm receives per share
  per_kg_pence int,
  target      int  not null check (target > 0),
  note        text,
  chilled     boolean not null default false,
  primary key (id, cycle_id)
);

create table pledges (
  id          uuid primary key default gen_random_uuid(),
  member_id   uuid not null references members(id),
  buy_id      text not null,
  cycle_id    int  not null,
  qty         int  not null check (qty > 0),
  hub_id      text not null references hubs(id),
  -- saved card (SetupIntent) now; PaymentIntent created only if target met
  stripe_payment_method_id text,
  stripe_payment_intent_id text,
  status      text not null default 'pledged'
              check (status in ('pledged','charged','refunded','cancelled','collected')),
  created_at  timestamptz not null default now(),
  foreign key (buy_id, cycle_id) references crowd_buys(id, cycle_id)
);

create view crowd_buys_with_totals as
select b.*,
       coalesce(sum(p.qty) filter (where p.status <> 'cancelled'), 0) as pledged,
       coalesce(sum(p.qty) filter (where p.status <> 'cancelled'), 0) >= b.target as target_met
from crowd_buys b
left join pledges p on p.buy_id = b.id and p.cycle_id = b.cycle_id
group by b.id, b.cycle_id;

-- Food safety records -----------------------------------------------------
-- Traceability ("one step back, one step forward") comes from pledges +
-- deliveries. Temperature logs back up the hub's Safer Food Better Business pack.

create table deliveries (
  id          uuid primary key default gen_random_uuid(),
  cycle_id    int  not null references cycles(id),
  farm_id     text not null references farms(id),
  hub_id      text not null references hubs(id),
  received_at timestamptz not null default now(),
  received_by text not null,
  batch_ref   text,                         -- producer lot / use-by
  temp_c      numeric(4,1),                 -- probe reading on arrival (chilled goods)
  accepted    boolean not null default true,
  notes       text
);

create table temperature_logs (
  id        uuid primary key default gen_random_uuid(),
  hub_id    text not null references hubs(id),
  unit      text not null,                  -- 'fridge 1', 'freezer'
  temp_c    numeric(4,1) not null,
  logged_at timestamptz not null default now(),
  logged_by text not null
);
