# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
- **Primary: project and business owners across the Arab world** (Egypt first, also the Gulf and other Arab countries) who need someone to execute work for them — websites, marketing, design, content, speed fixes — and must trust the platform before paying. Many are not technical and do not know exactly which service they need.
- **Secondary: freelancers** who join, get approved, and send offers on client requests.
- **Internal: the Nafizha team** (admin panel) who match, follow up, decide disputes and move money.

## Product Purpose
Nafizha (نفّذها, "get it done") connects a client with a vetted freelancer and stays in the middle until the work is delivered: the client describes the need (guided questions, a free-text project, a free site check or a diagnosis), approved freelancers send offers, the client picks one and pays through the site, and the money is released to the freelancer only after the client approves the work. Success = a client who was unsure gets the right work done without losing money.

## Positioning
All four are confirmed differentiators the design must make visible (user, 2026-10-18):
1. **Money held until approval** — «يبقى المبلغ لدى نفّذها حتى تعتمد العمل» (never the word «ضمان»).
2. **A team that helps you choose** — «ساعدوني أختار», «شخّص مشروعك», «اختبر موقعك مجانًا»: the client is not alone.
3. **«مشروعك ما بيوقف»** — staged projects; if a freelancer stops, the project continues with another.
4. **Vetted freelancers** — every freelancer is reviewed and approved (confirmed email or team check, professional photo) before sending offers.

## Operating Context
- Arabic-first, right-to-left; Egyptian-friendly wording in places. Payments in EGP via EasyKash (card, Fawry, wallets); approximate USD shown on request.
- Entry points: homepage five cards (اطلب خدمة / شخّص مشروعك / مشروع بمراحل / انشر مشروعك / انضم كمستقل), services catalog (42 services in 6 groups), freelancers directory, blog, landing pages.
- Account area for clients (requests, offers, projects, balance) and freelancers (client requests board, offers, profile checklist, projects).
- Many visitors arrive on phones.

## Capabilities and Constraints
- Laravel 11 + Blade + Bootstrap 5 RTL theme (`public/assets/website/css/styles.css`, plugins.css); jQuery, Livewire. No build step for site CSS.
- Brand colour is admin-editable (`settings.website_color` → `--main-color`, default #3FB699).
- Most page texts are admin-editable content areas (ContentAreas); design must not hard-code copy that lives there.
- The admin panel (separate theme) is out of scope for the 2026-10-18 design round.

## Brand Commitments
- Name «نفّذها»; logo `public/assets/website/img/logo.png` (green mark + charcoal Arabic wordmark).
- **All colours must come from the logo** (user, 2026-10-18): green #3FB699 and charcoal #2B3038, with their tints/shades and neutrals.
- **The logo green is the primary colour** (user, 2026-10-18): buttons, links and highlights are green (with charcoal text for readability).
- Wording rules: never «ضمان» for held money; clients = «عميل», freelancers = «مستقل» (never «مشتري/بائع»).

## Evidence on Hand
- Real assets: logo, services catalog data, admin-managed hero/cards/steps/footer content, freelancer photos when approved.
- No verified testimonials, client counts, ratings totals or press exist yet — never invent numbers, logos or quotes.

## Product Principles
1. Trust before transaction: show how money and work are protected at the moment of decision.
2. Never leave the client alone: every path offers a human-help exit.
3. Guide, don't overwhelm: one clear next step per screen.
4. Arabic-native, not translated: RTL layouts, Arabic typography and rhythm first.
5. Phone-first: every flow must be comfortable on a small screen.
