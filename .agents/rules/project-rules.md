---
trigger: always_on
description: Mandatory architecture, grading rubric, and max 5 files per commit rules for the Omul Potrivit project.
---

# Omul Potrivit — Mandatory Project Development Rules

> **Scope:** These rules apply to **every AI Agent and developer** creating, editing, or reviewing code in `omulpotrivit`. Any new feature, bug fix, or refactor **must** strictly adhere to all rules below.

---

## Rule 0 (CRITICAL): Maximum 5 Files Created / Updated Per Commit

- **Hard Cap:** **NEVER** create, modify, rename, or delete more than **5 files** in a single Git commit.
- **Atomic Batching:** Any task touching more than 5 files **must** be split into sequential, logical, self-contained commits of `<= 5` files each, for example:
  1. **Commit 1:** Accessors & Services (e.g., `ListingAccessor`, `ListingService`)
  2. **Commit 2:** Events & Listeners (e.g., `ListingPromoted`, listeners)
  3. **Commit 3:** Controllers & Routes (e.g., `ListingController`, `routes/web.php`, `routes/api.php`)
  4. **Commit 4:** Blade Views & Frontend JS
  5. **Commit 5:** Seeders, Tests & Documentation (`PROJECT_ROADMAP.md`, `README.md`)
- **Pre-Commit Verification:** Before every `git commit`, always verify the staged file count is `<= 5`:
  ```powershell
  (git diff --cached --name-only | Measure-Object -Line).Lines
  ```
  If the count exceeds `5`, unstage excess files and commit in smaller batches.

---

## Rule 1: Always Consult and Maintain `PROJECT_ROADMAP.md`

- **Lookup First:** Before scanning the codebase or creating new files, read `PROJECT_ROADMAP.md` to locate existing routes, controllers, services, accessors, models, migrations, and views.
- **Keep the Map Current:** Whenever you add, rename, or remove routes, services, accessors, events, listeners, models, migrations, or views, update `PROJECT_ROADMAP.md` (either within the 5-file commit budget or as its own commit).

---

## Rule 2: Strict 4-Layer Architecture (`Controller -> Service -> Accessor -> Model`)

Every feature must respect strict separation of concerns across these four layers:

1. **Controllers (`app/Http/Controllers/` & `app/Http/Controllers/Api/`):**
   - Responsible **only** for HTTP request validation, authorization checks, calling `Service` methods, and returning formatted responses (`ListingViewModel` / Blade views for web; `ListingResource` / JSON for API).
   - Must receive `*Service` dependencies via **Constructor Dependency Injection**.
   - Must **NEVER** call Eloquent Models directly (`Listing::...`, `User::...`) or implement business logic directly.
   - **No DB Queries in Route Closures:** Closures in `routes/web.php` and `routes/api.php` must **NEVER** query Eloquent Models directly. Route every endpoint through a Controller backed by a Service.
2. **Services (`app/Services/`):**
   - **Mandatory layer** for all application business logic: listing quota enforcement (`canUserCreateListing`), home page featured ranking & active-only filtering, promotion plan activation, review moderation, caching (`Cache`), logging (`Log`), and dispatching domain events (`Event::dispatch`).
   - Must receive `*Accessor` dependencies via **Constructor Dependency Injection**.
3. **Data Access Layer / Accessors (`app/Accessors/`):**
   - All Eloquent ORM queries (`with()`, `where()`, `find()`, `create()`, `update()`, `delete()`, logical delete updates) must be encapsulated inside `*Accessor` classes.
4. **Models (`app/Models/`):**
   - Define `$table` (when non-standard, e.g. `requests`, `notifications`), `$fillable`, `$casts`, and Eloquent relationships (`belongsTo`, `hasMany`, `belongsToMany`).

---

## Rule 3: Grading Rubric Compliance (Mandatory Technical Requirements)

Every new feature or modification must preserve and advance all grading requirements:

1. **Admin Section (`2.0 p`):**
   - Must implement **complete CRUD (Create, Read, Update, Delete)** for at least **2 entities**, where at least one entity has a **foreign key** (e.g., `Listing` with `category_id` & `provider_id`, plus `User` or `Category`).
   - Admin Create and Edit forms for foreign-key entities must expose dropdowns/controls to assign and update the foreign key (e.g., `category_id`).
2. **User Section with Plans & API Controls (`1.0 p`):**
   - The authenticated user section (e.g., `resources/views/listings/my-listings.blade.php`) must display available promotion plans (`PromotionPlan`) and trigger core listing/promotion controls via **REST API endpoints** (`/api/...` using `fetch` or `axios`), rather than relying solely on traditional server-rendered forms.
3. **Main Page & Business Logic in Services (`2.0 p`):**
   - The main landing page (`GET /`) must delegate to the `Services` layer (`ListingService`, `CategoryService`) to retrieve active categories and active listings (`status = 'activ'`), prioritizing promoted listings (`is_featured = true`).
   - Application-specific business rules (e.g., free-plan 1-listing quota, promotion eligibility, analytics visibility gating) must reside in `app/Services/`.
4. **Implementation Complexity (`1.0 p` — All 4 Required):**
   - **Dependency Injection:** Constructor DI across `Controller -> Service -> Accessor`.
   - **Cache (`Illuminate\Support\Facades\Cache`):** Cache read-heavy Service queries (`Cache::remember(...)` for home listings, categories, promotion plans) and invalidate affected cache keys (`Cache::forget(...)`) on every create, update, or delete.
   - **Logging (`Illuminate\Support\Facades\Log`):** Log significant business and moderation actions (`Log::info`, `Log::warning`, `Log::error`) inside Services (listing creation/updates, quota blocks, promotions, admin modifications/deletions).
   - **Logical Deletion:** Prefer logical deletion (`status = 'inactiv'`, `is_hidden = true`, or Laravel `SoftDeletes`) over destructive deletes, and ensure all public/user read queries exclude logically deleted items.
5. **Bonus — Decoupled Event Communication (`1.0 p`):**
   - At least one core business operation inside `app/Services/` (e.g., listing promotion or creation) must publish a custom domain `Event` (`app/Events/*`) handled by **at least 2 independent Listeners** (`app/Listeners/*`), such as:
     - **Consumer 1:** Creating an in-app notification via `AppNotification` (`notifications` table).
     - **Consumer 2:** Recording an analytics entry via `StatEvent` (`stat_events` table) and/or audit log.

---

## Rule 4: Code Hygiene, Security & UI Consistency

- **Reuse Existing Schema-Ready Models:** Before creating a new migration or model, check `app/Models/` (`PromotionPlan`, `Promotion`, `Payment`, `StatEvent`, `AppNotification`, `ServiceRequest`, `Offer`, `Skill`). Wire existing tables rather than creating duplicates.
- **No Hardcoded User IDs:** Always resolve the acting user via `auth()->id()` (or `auth:sanctum` on protected API endpoints), never hardcoded IDs such as `1`.
- **Ownership & Role Authorization:** Verify ownership (`$listing->provider_id === auth()->id()`) or `admin` role (`AdminMiddleware`) before allowing updates or deletions.
- **Blade & Tailwind Consistency:** Use `<x-app-layout>` (authenticated views) or `<x-guest-layout>` (guest views) with Tailwind CSS; do not add standalone raw HTML pages with inline `<style>` blocks.
- **Keep Documentation Updated:** Maintain `README.md` / course documentation alongside `PROJECT_ROADMAP.md` as features are completed.
