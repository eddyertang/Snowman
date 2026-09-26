-- 001: founding-member waitlist + demand survey.
-- The public site can INSERT only. Nobody can read, change or delete rows
-- with the public key; the team reads them in the Supabase dashboard.

create extension if not exists citext;

create table public.waitlist (
  id           uuid primary key default gen_random_uuid(),
  email        citext not null unique check (length(email) between 5 and 254 and email ~ '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
  postcode     text not null check (length(postcode) between 2 and 10),
  -- outward code only (e.g. NG2), derived below; enough to plan hubs
  district     text generated always as (upper(split_part(trim(postcode), ' ', 1))) stored,
  interests    text[] not null default '{}'
               check (interests <@ array['veg','fruit','eggs','meat','dairy','bakery']::text[]),
  weekly_spend text check (weekly_spend in ('under_20','20_40','40_70','over_70')),
  would_collect text check (would_collect in ('yes','maybe','delivery_only')),
  consent_updates boolean not null default false,
  source       text check (length(source) <= 40),
  created_at   timestamptz not null default now()
);

alter table public.waitlist enable row level security;

-- Anyone may sign up; the row must say they agreed to hear from us.
create policy "public can join waitlist"
  on public.waitlist for insert
  to anon, authenticated
  with check (consent_updates = true);

-- No select/update/delete policies: rows are private to the team.
revoke all on public.waitlist from anon, authenticated;
grant insert (email, postcode, interests, weekly_spend, would_collect, consent_updates, source)
  on public.waitlist to anon, authenticated;

-- Aggregate view for planning (read with the service role / dashboard only).
create view public.waitlist_by_district
  with (security_invoker = true) as
  select district,
         count(*) as signups,
         count(*) filter (where 'meat' = any(interests)) as want_meat,
         count(*) filter (where would_collect = 'yes') as will_collect
  from public.waitlist
  group by district
  order by signups desc;

revoke all on public.waitlist_by_district from anon, authenticated;
