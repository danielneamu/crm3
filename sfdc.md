# CRM3 — SFDC Reports Module: Architecture & Build Plan

## Overview

A new standalone **SFDC Reports** section added to CRM3, covering Salesforce opportunity data synced daily into three new database tables (`sfdc_main`, `sfdc_won`, `sfdc_log`). The module is fully independent — existing controllers, models, views, and API files are untouched. The only modification to shared files is a single nav item added to `includes/navbar.php`.

---

## Database Tables

| Table | Primary Key | Purpose | Rows/Year (est.) |
|-------|-------------|---------|-----------------|
| `sfdc_main` | `Opportunity_Reference_ID` | Active pipeline opportunities | ~3,000 |
| `sfdc_won` | `id` (AUTO_INCREMENT) | Closed Won deals at product-line level | ~600 |
| `sfdc_log` | `Log_ID` (AUTO_INCREMENT) | Historical stage snapshots for trend tracking | TBD |

All three tables are **read-only from Salesforce's perspective** — updated daily by an external sync process outside CRM3 scope.

### Field Behaviour Rules (`sfdc_won`)

| Field | Label | Behaviour |
|-------|-------|-----------|
| `Revised_AOV` | Revised AOV | Editable. Defaults from `Product_Annual_Recurring_Order_Value` on insert. User override preserved on re-sync. |
| `Revised_NPV` | Revised NPV | Editable. Defaults from parsed value in `Description` on insert. User override preserved on re-sync. |
| `Type` | Type | Editable dropdown: `Fixed / ICT / Other`. Local to `sfdc_won` — not synced to `sfdc_main`. Opportunity-level. |
| `Description` | Description | Read-only. Opportunity-level reference. Continues to sync from source. |
| `Real_Flag` (`sfdc_main`) | Real | Editable checkbox toggle. Local to `sfdc_main`. |

---

## Module Principles

- **Zero impact** on existing CRM3 files (controllers, models, views, API endpoints, public pages)
- **One shared base layer** (`SfdcBase*`) consumed by both sub-modules — no duplication
- **Won and Pipeline are fully independent** — neither module imports from the other
- **Modular views** — shared UI components (filter bar, tab switcher, inline edit JS) live in `views/sfdc/common/`
- **Future-proof** — adding a new sub-module (Projects, Log History) requires only new files following the same pattern, zero refactoring

---

## Folder Structure

```
public/
├── sfdc_won.php                        ← Won sub-module entry point
└── sfdc_pipeline.php                   ← Pipeline sub-module entry point

api/
├── sfdc_won.php                        ← Won-only AJAX/DataTables API
├── sfdc_pipeline.php                   ← Pipeline-only AJAX/DataTables API
└── sfdc_common.php                     ← Shared bootstrap (DB init, auth, CORS, error handling)

app/
├── controllers/
│   ├── SfdcBaseController.php          ← Auth check, filter parsing, JSON response formatter
│   ├── SfdcWonController.php           ← Won actions: get, dashboard data, inline edit
│   └── SfdcPipelineController.php      ← Pipeline actions: get, dashboard data, inline edit
│
├── models/
│   ├── SfdcBaseModel.php               ← DB connection, getTeams(), getAgents(), getFiscalPeriods()
│   ├── SfdcWonModel.php                ← Queries on sfdc_won; default logic for Revised_AOV/NPV
│   └── SfdcPipelineModel.php           ← Queries on sfdc_main; sfdc_log stub for future history
│
└── views/
    └── sfdc/
        ├── won/
        │   ├── table.php               ← Won DataTable view
        │   └── dashboard.php           ← Won ChartJS dashboard view
        ├── pipeline/
        │   ├── table.php               ← Pipeline DataTable view
        │   └── dashboard.php           ← Pipeline ChartJS dashboard view
        └── common/
            ├── _filters.php            ← Shared filter bar: team / agent / month / quarter
            ├── _tabs.php               ← Shared Table / Dashboard tab switcher
            └── _inline_edit.js         ← Shared inline edit JS (cell click → AJAX → save)

includes/
└── navbar.php                          ← +1 line: SFDC dropdown with Won / Pipeline links
```

---

## Dependency Map

```
public/sfdc_won.php
    └── SfdcWonController
            ├── SfdcBaseController   ← auth, response format, filter parsing
            └── SfdcWonModel
                    └── SfdcBaseModel  ← DB, teams, agents, fiscal periods

public/sfdc_pipeline.php
    └── SfdcPipelineController
            ├── SfdcBaseController
            └── SfdcPipelineModel
                    └── SfdcBaseModel

api/sfdc_won.php
    ├── sfdc_common.php              ← shared bootstrap
    └── SfdcWonController → SfdcWonModel

api/sfdc_pipeline.php
    ├── sfdc_common.php
    └── SfdcPipelineController → SfdcPipelineModel
```

---

## API Endpoint Reference

### `api/sfdc_won.php`

| Action | Method | Description |
|--------|--------|-------------|
| `get_won` | GET | Paginated `sfdc_won` data for DataTables server-side or full client-side load |
| `get_won_dashboard` | GET | Aggregated data for Won charts (grouped by team/agent/month/quarter) |
| `update_won_field` | POST | Inline edit: `Revised_AOV`, `Revised_NPV`, `Type` |

### `api/sfdc_pipeline.php`

| Action | Method | Description |
|--------|--------|-------------|
| `get_pipeline` | GET | Paginated `sfdc_main` data for DataTables |
| `get_pipeline_dashboard` | GET | Aggregated data for Pipeline charts |
| `update_pipeline_field` | POST | Inline edit: `Real_Flag` |

### `api/sfdc_common.php` (shared bootstrap)

Handles: DB connection (reuses existing `config/database.php`), session/auth validation, CORS headers, JSON error responses.

---

## DataTables Strategy

| Module | Mode | Rationale |
|--------|------|-----------|
| Won (`sfdc_won`) | Client-side | ~600 rows/year — full load is fast; simpler filtering |
| Pipeline (`sfdc_main`) | Server-side | ~3,000 rows/year — server-side with year/period filter reduces active set |

Both modules use the same filter bar (`_filters.php`) providing: Team (`Owner_Role`), Agent (`Opportunity_Owner`), Month, Quarter, Fiscal Period.

---

## Inline CRUD — Editable Columns

| Table | Column | UI Control | Notes |
|-------|--------|-----------|-------|
| `sfdc_won` | `Revised_AOV` | Numeric input | Shows source value (`Product_Annual_Recurring_Order_Value`) as placeholder |
| `sfdc_won` | `Revised_NPV` | Numeric input | Shows parsed NPV from `Description` as placeholder |
| `sfdc_won` | `Type` | Dropdown | Options: Fixed / ICT / Other |
| `sfdc_main` | `Real_Flag` | Checkbox toggle | Filters real vs. test data across dashboards |

All other columns are **read-only**. Inline edit via `_inline_edit.js` — click cell → input appears → blur/enter → AJAX POST to API → visual confirmation.

---

## Page Structure — Each Sub-Module

Each public page (`sfdc_won.php`, `sfdc_pipeline.php`) renders:

```
[Header / Navbar]
[Page Title + Period Selector]
[Filter Bar: Team | Agent | Month | Quarter]   ← _filters.php

[Tab: Table] [Tab: Dashboard]                  ← _tabs.php

--- Table Tab ---
[DataTable with inline-editable columns]

--- Dashboard Tab ---
[ChartJS charts]
```

---

## Dashboard Charts

### Won Dashboard (`sfdc_won`)

| Chart | Type | Dimensions |
|-------|------|-----------|
| Monthly ARR Closed | Bar | by month, grouped by team or agent |
| Quarterly TCV Summary | Grouped Bar | Q1–Q4, team comparison |
| Deal Type Breakdown | Pie | Fixed / ICT / Other |
| Top Accounts | Horizontal Bar | by `Annual_Order_Value_Multi` |

### Pipeline Dashboard (`sfdc_main`)

| Chart | Type | Dimensions |
|-------|------|-----------|
| Stage Distribution | Horizontal Bar / Funnel | count and value per stage |
| Pipeline by Team | Stacked Bar | `Owner_Role` × amount |
| Close Date Concentration | Scatter / Timeline | opportunities over time |
| Real vs. Test Toggle | Filter | driven by `Real_Flag` |

---

## Slicing Dimensions (Both Modules)

- **Team** — via `Owner_Role`
- **Agent** — via `Opportunity_Owner`
- **Month** — derived from `Close_Date`
- **Quarter** — derived from `Close_Date` or `Fiscal_Period`

---

## Future Sub-Modules (No Refactoring Required)

| Sub-Module | Trigger | What It Adds |
|------------|---------|-------------|
| **Pipeline History** | When `sfdc_log` has enough data | Stage evolution charts; `sfdc_log` indexed on `Snapshot_Date` + `Opportunity_Reference_ID` — ready now |
| **Projects ↔ SFDC** | TBD | Join CRM3 projects table with `sfdc_main` on `Opportunity_Reference_ID`; highlights projects missing OPP ID |

Pattern for any new sub-module:
```
SfdcXxxModel     extends SfdcBaseModel
SfdcXxxController  extends SfdcBaseController
api/sfdc_xxx.php   requires sfdc_common.php
views/sfdc/xxx/    table.php + dashboard.php
public/sfdc_xxx.php
```

---

## Shared Infrastructure (`SfdcBase*`)

### `SfdcBaseModel.php`
- Reuses existing `config/database.php` — same DB connection as rest of CRM3
- `getTeams()` — returns distinct `Owner_Role` values (from `sfdc_main` or `sfdc_won`)
- `getAgents()` — returns distinct `Opportunity_Owner` values
- `getFiscalPeriods()` — returns distinct `Fiscal_Period` values
- Base query builder with filter application (team, agent, period, date range)

### `SfdcBaseController.php`
- `checkAuth()` — validates session using existing CRM3 auth
- `parseFilters()` — extracts and sanitises `$_GET` filter params
- `jsonSuccess($data)` / `jsonError($msg)` — standard response format
- `requireMethod($method)` — enforces GET/POST on API endpoints

### `api/sfdc_common.php`
- Requires `config/database.php`
- Sets JSON headers, error handler
- Validates session before any action runs

---

## Navbar Change (Only Shared File Modified)

One addition to `includes/navbar.php`:

```html
<!-- SFDC dropdown -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">SFDC</a>
  <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="/public/sfdc_won.php">Won</a></li>
    <li><a class="dropdown-item" href="/public/sfdc_pipeline.php">Pipeline</a></li>
  </ul>
</li>
```

---

## Build Sequence

### Phase 1 — Won DataTable (First Build)
1. `SfdcBaseModel.php` — DB connection + shared lookup methods
2. `SfdcBaseController.php` — auth, filter parse, JSON response
3. `api/sfdc_common.php` — bootstrap
4. `SfdcWonModel.php` — `sfdc_won` queries + default logic for `Revised_AOV` / `Revised_NPV`
5. `SfdcWonController.php` — `get_won`, `update_won_field`
6. `api/sfdc_won.php` — action router
7. `views/sfdc/common/_filters.php` — filter bar UI
8. `views/sfdc/common/_tabs.php` — tab switcher
9. `views/sfdc/common/_inline_edit.js` — inline edit JS
10. `views/sfdc/won/table.php` — DataTable with editable columns
11. `public/sfdc_won.php` — entry point
12. `includes/navbar.php` — +1 SFDC dropdown item

### Phase 2 — Won Dashboard
- `SfdcWonController.php` — add `get_won_dashboard` action
- `views/sfdc/won/dashboard.php` — ChartJS charts

### Phase 3 — Pipeline DataTable + Dashboard
- Mirrors Phase 1 & 2 pattern using `SfdcPipeline*` files

### Phase 4 — Future (TBD)
- Projects ↔ SFDC connection sub-module
- Pipeline History via `sfdc_log`

