# What your last 11 prompts did

Written 12 October 2026, about 22:00 UK. Oldest first. Times are UK (BST).

| # | When | You said | What happened | Result |
|---|---|---|---|---|
| 1 | Thu 8 Oct, 20:36 | "how does a simple box check prevent a bot? can't it just click it?" | I explained Cloudflare Turnstile: the tick box is only the visible part. Before it passes anyone, it checks the browser, how it behaves and how trustworthy its connection is, and many people never see a box at all. | Explanation only, no changes. |
| 2 | Mon 12 Oct, 17:28 | "prompts/P03-data-contract-storage-design.md" | I looked for the P03 prompt in the repo and didn't find it. | I asked where it was. |
| 3 | 17:30 | "It's in Google drive" | I downloaded the programme bundle from Drive (read only) and ran P03 in full. It produced the data contract, a register of 31 data sources (each with its own licence, all still to be verified), the storage plan, the rendering decision, the URL scheme, and two draft database changes (not applied). | [PR #169](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/169) opened. All 10 acceptance criteria passed. **You merged it at 19:38.** |
| 4 | 19:54 | "prompts/P03-data-contract-storage-design.md" (again) | I checked Drive for a newer P03 prompt. There wasn't one, so P03 was already done. | Nothing re-run. I suggested P04 next. |
| 5 | 19:56 | "p04" | I started P04 and read the live database, read-only, to build a snapshot of how it is set up. You declined the query for database functions 17 to 30, so I stopped. | Paused, waiting for you. |
| 6 | 20:03 | /fewer-permission-prompts | I scanned this session's tool use and added four read-only tools to the "don't ask" list (database logs, Claude Docs, Drive search, environment docs). Running SQL stayed on "ask", because it can also change data. | [PR #170](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/170). **I merged it** once its checks passed (your rule for green PRs). |
| 7 | 20:07 | "(this is a check…) You can pre-approve that tool…" (advice about making Supabase read-only) | I confirmed the read-only tools were already allowed. Allowing SQL needs the read-only switch on the Supabase connector first, which only you can turn on. | Nothing changed. Read-only is still **off** (I checked later). |
| 8 | 20:07 | "approved to all" | I finished P04: the read-only queries, then the baseline, the drift list, finalising P03's two migrations, a backfill script, timings for the five key queries, the apply runbook and the handoff. Nothing was applied to the live database. | [PR #171](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/171) opened. **You merged it at 20:27.** I opened [PR #172](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/172) to fix a guessed preview link. |
| 9 | 20:30 | "merged" | I assumed this meant #172 and said all P04 PRs were merged. **That was wrong:** you meant #171, and #172 is still open. I corrected this later. | No change. #172 is still open. |
| 10 | 20:43 | The P04-fix prompt | I fixed what the lead verify found:<br>• **Licences:** the EA flood licence conflict is recorded; the Ofcom note no longer claims compliance.<br>• **Ofcom delete job:** added, shipped switched off.<br>• **Storage figures:** recalculated (589 / 692 / 864 MiB).<br>• **Ring size:** kept as two estimates for you to decide.<br>• **Cloudflare paid-plan figures:** "$5" and "30 s" marked as needing verification.<br>• **Query speeds:** re-timed the same way as P01b; all faster.<br>• **Baseline:** moved out of the migrations folder; runbook gains a history step.<br>• **Handoffs:** real timestamps and honest results in P03.json and P04.json. | [PR #173](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/173) is open with checks green. **Waits for you.** |
| 11 | 21:56 | "have you been adding to the google drive file?" | I checked this session's record. | No. Drive was only read: 6 searches and 1 download. All work is in GitHub. |

## Still waiting on you

1. **Merge [PR #173](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/173)** (P04-fix).
2. **Close [PR #172](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/172).** #173 already includes its fix.
3. **Turn on the Ofcom cache expiry job before 24 October.** Add the GitHub variable `OFCOM_CACHE_EXPIRY_ENABLED` = `true`, then run the job once as a dry run and once with "apply" ticked. The steps are in #173.
4. **Cloudflare build watch paths.** In placefit-uk → Settings → Builds, include `web/*`.
5. **Apply P04's two database migrations** with `docs/programme/P04-apply-runbook.md`, or tell me to.
6. **Decisions:**
   - ring size (1,000 or 1,200 places);
   - which licence applies to the EA flood data;
   - check the Cloudflare Workers Paid price and CPU limit;
   - verify the 31 source licences (from P03);
   - the P03 rendering and URL decisions.
7. **Optional:** turn on read-only for the Supabase connector, so read-only queries stop asking you.

## Pull requests in this stretch

| PR | What | State |
|---|---|---|
| [#169](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/169) | P03: data contract, storage and rendering design | Merged by you |
| [#170](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/170) | Four read-only tools allowed without a prompt | Merged by Claude (green) |
| [#171](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/171) | P04: migration and performance | Merged by you |
| [#172](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/172) | P04 handoff preview-link fix | Open, to close |
| [#173](https://github.com/eddyertang/rork-foundation--trust-and-scale-re/pull/173) | P04-fix: follow-up fixes | Open, waits for you |

Nothing in any of these was applied to the live database.
