# OpenGovernment

**A civic transparency platform you can deploy in any country.**

Local governments publish what they spend. Citizens read it, comment on it, and post
problems they want fixed. The platform groups duplicate problems together so the
collective voice of a community is loud and clear instead of spread across a hundred
near-identical posts.

OpenGovernment is open source (MIT) and built to be **white-labelled per country** — each
country (or even sub-national jurisdiction) can fork this repo, adjust a single config
file plus a `.env`, and run their own instance with their own currency, their own
administrative tier names, and their own identity-verification provider.

> Built with Laravel 12, Inertia + Vue, `laravel/ai`, and `laravel/mcp`.

---

## Table of contents

- [What it does](#what-it-does)
- [Why it's white-labelled](#why-its-white-labelled)
- [The agentic side](#the-agentic-side)
- [MCP server](#mcp-server)
- [Architecture](#architecture)
- [Local setup](#local-setup)
- [Deploying your country's instance](#deploying-your-countrys-instance)
- [Identity verification](#identity-verification)
- [Funding the platform](#funding-the-platform)
- [Contributing](#contributing)
- [Roadmap](#roadmap)
- [Security disclosure](#security-disclosure)
- [License](#license)

---

## What it does

Two flagship features:

### 1. Spending records
Verified government officials publish what their local government has spent money on —
title, amount, currency, vendor, date, optional link to the source document. Anyone can
browse them; verified citizens can comment.

### 2. Citizen issues
Citizens post the problems they want fixed in their area (potholes, water outages,
streetlight failures). Other residents upvote or downvote. The ranked list is the public
backlog officials see in their dashboard.

### 3. Agentic chat (the glue)
A citizen doesn't have to know whether to file a new issue or upvote an existing one.
They open the chat, describe the problem in their own words, and the assistant:

1. Calls `search_similar_issues` to look for existing posts in the same LGA.
2. If there's a likely match, asks the user to confirm: _"Is this the same problem?
   `Potholes along Awolowo Way are getting worse` (12 votes)"._
3. On _yes_ → calls `upvote_issue`. On _no_ → calls `create_issue` with a clean
   rewritten title and body.

The same conversational flow handles _"how much did Ikeja spend on roads last year?"_
by calling `search_spending_records` or `get_lga_summary`.

---

## Why it's white-labelled

The dynamics of public-spending transparency aren't unique to one country. Nigeria
needs this. Kenya needs this. Ghana, Brazil, Indonesia — every democracy with local
budgets has the same problem.

So OpenGovernment is designed so a country's civic-tech community can fork once and
deploy their own instance without touching the core code:

| What changes per country | Where you change it |
| --- | --- |
| Brand name, tagline, support email | `.env` (`OG_BRAND_NAME` etc.) |
| Region tier name (State / Province / County) | `countries` table — `region_label` |
| Local government tier name (LGA / Council / Municipality) | `countries` table — `local_government_label` |
| Currency code & symbol | `countries` table |
| Identity verification provider (NIN/BVN, national ID, etc.) | bind a driver in `AppServiceProvider` |
| Donation provider (Paystack, Stripe, Flutterwave) | bind a driver in `AppServiceProvider` |
| AI provider (OpenAI, Anthropic, Gemini, self-hosted Ollama) | `.env` (`OG_AI_DRIVER`, `OG_AI_MODEL`) |
| Seed data (states, LGAs) | `database/seeders/CountrySeeder.php` |

Everything else — the data model, the agentic flow, the MCP server, the UI — is shared.

---

## The agentic side

We use [`laravel/ai`](https://packagist.org/packages/laravel/ai) for the agent and
[`laravel/mcp`](https://packagist.org/packages/laravel/mcp) for the MCP server.

The agent (`app/Ai/Agents/CivicAgent.php`) has four tools:

| Tool | Purpose |
| --- | --- |
| `search_similar_issues` | Lexical similarity match against existing issues in an LGA. The agent always calls this *before* creating a new issue. |
| `create_issue` | Files a new issue. Requires an authenticated, identity-verified citizen. |
| `upvote_issue` | Adds the citizen's upvote to an existing issue. |
| `search_spending_records` | Full-text-ish search over published spending records. |

Each tool delegates to a service class in `app/Services/Civic/` — same code path the
HTTP controllers use. There is **one source of truth** for issue creation, voting,
similarity search, and spending search; the AI tools, the MCP tools, and the HTTP
controllers all funnel through it.

### About duplicate detection

The MVP uses PHP's `similar_text` for the similarity score — a deliberately simple
choice so the project runs without an embeddings provider configured. Production
deployments should swap `App\Services\Civic\IssueSearchService` for an embeddings-based
implementation (pgvector + `laravel/ai` embeddings is the natural fit). The agent flow
is unchanged.

### About post moderation

Every citizen-authored post (issues and spending comments) requires the user to be
identity-verified — see [Identity verification](#identity-verification). This is the
single biggest knob against brigading and bot-driven manipulation of the issue ranking.

---

## MCP server

OpenGovernment exposes its tools over the [Model Context Protocol](https://modelcontextprotocol.io/)
at **`POST /mcp`** (the same endpoint also accepts `GET` and `DELETE` per the MCP spec).

This means external clients — Claude Desktop, Cursor, VS Code, scripts, other agents —
can read spending records, search issues, and (when authenticated as a citizen) file
or upvote issues, all without scraping HTML.

### Adding the deployed server to Claude Desktop

Edit `~/Library/Application Support/Claude/claude_desktop_config.json` and add:

```json
{
  "mcpServers": {
    "opengovernment": {
      "transport": { "type": "http", "url": "https://your-deployment.example.org/mcp" }
    }
  }
}
```

Read-only tools (`list_local_governments`, `search_spending_records`, `get_lga_summary`,
`search_similar_issues`) work anonymously. Write tools (`create_issue`, `upvote_issue`)
require Sanctum auth.

### Running the MCP server locally (stdio)

For development against a checked-out clone:

```bash
php artisan mcp:start opengovernment
```

That starts the server over stdio so you can wire it into an editor like Claude
Desktop / Cursor as a local MCP server.

### Inspector

```bash
php artisan mcp:inspector
```

Opens the official MCP Inspector pointing at this project, handy for testing
schemas while developing tools.

---

## Architecture

```
app/
├── Ai/
│   ├── Agents/CivicAgent.php          # the conversational agent
│   └── Tools/                          # laravel/ai tool implementations
├── Mcp/
│   ├── Servers/CivicMcpServer.php     # MCP server registration
│   └── Tools/                          # laravel/mcp tool implementations
├── Services/
│   ├── Civic/                          # the single source of truth for civic ops
│   │   ├── IssueRegistrarService.php   #   - create / vote
│   │   ├── IssueSearchService.php      #   - similarity search
│   │   ├── SpendingSearchService.php   #   - spending queries + summary
│   │   └── LgaResolverService.php      #   - "which LGA is this?"
│   ├── Identity/                       # pluggable identity-verification drivers
│   └── Donations/                      # pluggable donation drivers
├── Http/Controllers/
│   ├── SpendingRecordController.php    # public read + show
│   ├── SpendingCommentController.php
│   ├── IssueController.php             # public read + citizen create
│   ├── IssueVoteController.php
│   ├── ChatController.php              # /chat endpoint that runs the agent
│   ├── DonationController.php
│   └── Government/
│       ├── GovAuthController.php       # separate `government` guard
│       ├── GovDashboardController.php
│       └── GovSpendingController.php   # publish spending
└── Models/
    ├── Country.php · State.php · LocalGovernment.php
    ├── User.php (citizen) · GovernmentOfficial.php
    ├── SpendingRecord.php · SpendingComment.php
    ├── Issue.php · IssueVote.php · IssueCluster.php
    └── Donation.php
```

**Two auth guards**: `web` for citizens, `government` for officials. They never share
sessions; the right to publish spending data is held behind a different door from the
right to comment on it.

**One database**: SQLite by default for dev (`database/database.sqlite`). Production
deployments should switch to Postgres for the JSON support and, once you adopt
embeddings, pgvector.

---

## Local setup

Requires PHP 8.3+, Composer 2.x, Node 20+.

```bash
git clone https://github.com/provydon/opengovernment.git
cd opengovernment

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate:fresh --seed
npm run dev          # one terminal
php artisan serve    # another terminal
```

Visit `http://localhost:8000`.

### Demo accounts (created by `DemoDataSeeder`)

| Role | Email | Password |
| --- | --- | --- |
| Citizen (verified) | `demo-citizen@opengovernment.test` | `password` |
| Government official | `demo-official@opengovernment.test` | `password` |

Sign in as the official at `/government/login` to see the publishing dashboard.

### Enabling the agentic chat

The chat needs a real AI provider key. Add either:

```env
OG_AI_DRIVER=openai
OG_AI_MODEL=gpt-4o-mini
OPENAI_API_KEY=sk-...
```

or

```env
OG_AI_DRIVER=anthropic
OG_AI_MODEL=claude-haiku-4-5-20251001
ANTHROPIC_API_KEY=sk-ant-...
```

Without a key, `/chat` still renders but returns a friendly error message.

---

## Deploying your country's instance

1. **Fork this repo.** Rename to something like `opengovernment-gh` (Ghana) or
   `opengovernment-ke` (Kenya).
2. **Edit `database/seeders/CountrySeeder.php`** — replace or add a `seed<YourCountry>()`
   method seeding your administrative regions and local councils. Real deployments
   should aim for full coverage, not the sample subset.
3. **Set `OG_DEFAULT_COUNTRY`** in `.env` to your two-letter ISO code so unauthenticated
   visitors land in the right country.
4. **Implement an identity-verification driver** for your country (see below). Bind it
   in `AppServiceProvider::register()`.
5. **Configure your donation provider** — Paystack is the default. Stripe / Flutterwave
   / Mpesa Daraja all fit the `DonationProvider` interface in `app/Services/Donations/`.
6. **Deploy.** This is a regular Laravel app — Forge, Vapor, Render, Fly.io, your own
   VPS, anywhere.
7. **Tell us.** PR a row to the "Active deployments" section below so citizens can find
   their country's instance.

### Active deployments

| Country | URL | Maintained by |
| --- | --- | --- |
| _Add yours via PR_ | | |

---

## Identity verification

Identity verification is the load-bearing piece of the moderation model. Without it,
anyone can spin up bot accounts and brigade the issue ranking.

The contract is in `app/Services/Identity/IdentityVerificationProvider.php`. A driver
takes a payload (whatever fields make sense for the country), hashes any sensitive
IDs, and returns a result. **Raw national IDs are never persisted.**

Built-in drivers:

| Driver | Country | Notes |
| --- | --- | --- |
| `stub` | any | Dev only. Accepts anything and produces deterministic hashes. |
| `ng-dojah` _(TODO)_ | Nigeria | NIN + BVN via [Dojah](https://dojah.io). |
| `ng-verifyme` _(TODO)_ | Nigeria | NIN + BVN via [VerifyMe](https://verifyme.ng). |

To plug in your country's driver:

```php
// app/Services/Identity/KeIdProvider.php
class KeIdProvider implements IdentityVerificationProvider { /* ... */ }

// app/Providers/AppServiceProvider.php
$this->app->singleton(IdentityVerificationProvider::class, function () {
    return match (config('opengovernment.identity.driver')) {
        'stub' => new StubIdentityProvider,
        'ke-id' => new KeIdProvider,
        default => new StubIdentityProvider,
    };
});
```

Then `OG_IDENTITY_DRIVER=ke-id` in `.env`.

---

## Funding the platform

Identity-verification calls, AI usage, and infrastructure all cost real money. There
are no ads on this platform and no data is sold.

We fund each country's deployment through donations. The donation flow is built in
(`/donate`) and supports Paystack out of the box; swap providers per deployment by
implementing `app/Services/Donations/DonationProvider`.

If you operate a country's instance, you're encouraged to publish your costs and
donations transparently — same principle as the spending records themselves.

---

## Contributing

We want this platform to be owned by the civic-tech communities that use it.
Contributions especially welcome from:

- Country contributors who can seed their states/counties/LGAs accurately.
- Builders of identity-verification drivers for their country.
- Frontend devs who want to tighten the UX (the current Vue pages are intentionally
  minimal).
- Anyone who can replace the lexical similarity scorer with an embeddings-based one.

Standard flow: fork → branch → PR. Please run `php artisan test` and `npm run build`
before opening a PR.

### Code style
- PHP: PSR-12, run `./vendor/bin/pint`.
- JS/Vue: run `npm run format` (Prettier).
- One source of truth: business logic lives in `app/Services/Civic/`. HTTP controllers,
  AI tools, and MCP tools should all be thin wrappers over those services.

---

## Roadmap

Tracked in [`ROADMAP.md`](./ROADMAP.md) once it exists. Top priorities:

- [ ] Embedding-based similarity scoring (pgvector + `laravel/ai` embeddings).
- [ ] Working Paystack driver for donations.
- [ ] At least one real identity-verification driver for Nigeria (Dojah).
- [ ] Government admin panel to approve / suspend official accounts.
- [ ] Email notifications: officials notified when an issue in their LGA crosses N upvotes.
- [ ] Multilingual UI.
- [ ] Public API + OpenAPI spec (the MCP server already gives programmatic access; the
      REST API is the natural complement).

---

## Security disclosure

If you find a vulnerability, **do not open a public issue**. Email
`security@opengovernment.org` (or the address listed by your country's deployment) with
details. We respond within 72 hours.

The categories we particularly want to hear about:

- Anything that lets an unverified user post or vote on issues.
- Anything that bypasses the government-guard separation.
- Anything that allows reading or correlating the hashed identity fields.
- Anything that lets a citizen impersonate an official, or vice versa.

---

## License

MIT. See [LICENSE](./LICENSE).

Built on top of [Laravel](https://laravel.com), [Inertia.js](https://inertiajs.com),
[`laravel/ai`](https://packagist.org/packages/laravel/ai), and
[`laravel/mcp`](https://packagist.org/packages/laravel/mcp).
