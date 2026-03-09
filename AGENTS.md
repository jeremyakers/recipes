# PROJECT KNOWLEDGE BASE

**Generated:** 2026-03-09
**Commit:** fd037b8
**Branch:** main

## OVERVIEW
Legacy procedural PHP recipe manager with direct `.php` endpoints, a shared `header.inc` bootstrap, ODBC-backed data access, and a few local C utilities for ODBC inspection.

## STRUCTURE
```text
./
├── index.php                  # main recipe search entry point
├── ingredients.php            # ingredient search entry point
├── showrecipe.php             # recipe details page
├── editrecipe.php             # recipe create/edit flow
├── editingredient.php         # ingredient create/edit flow
├── editrecipeingredients.php  # recipe ingredient management
├── pricereport.php            # bulk ingredient cost editor
├── server.php                 # autocomplete endpoint
├── getdata.php                # standalone nutrition fetch helper
├── header.inc                 # shared session, ODBC, helper functions
├── odbctest.php               # standalone ODBC connectivity test
├── listdsn.c                  # ODBC DSN helper source
├── listtables.c               # ODBC table helper source
└── backup/                    # editor backup copies; not source of truth
```

## WHERE TO LOOK
| Task | Location | Notes |
|------|----------|-------|
| Recipe search/listing | `index.php` | Main entry page; mixes filters, session state, and query building |
| Ingredient search | `ingredients.php` | Similar structure to `index.php` |
| Recipe details | `showrecipe.php` | Pulls one recipe and renders ingredients + instructions |
| Recipe editing | `editrecipe.php` | Uses `../fckeditor/fckeditor.php`; insert/update/delete logic inline |
| Ingredient editing | `editingredient.php` | Inline persistence plus nutritiondata scraping helpers |
| Recipe ingredient editing | `editrecipeingredients.php` | Editing flow plus autocomplete wiring |
| Shared DB/session helpers | `header.inc` | Single bootstrap; defines ODBC env and helper functions |
| Autocomplete backend | `server.php` | Legacy endpoint; currently mixes ODBC bootstrap with `mysql_fetch_assoc()` |
| ODBC smoke test | `odbctest.php` | Minimal direct connectivity check |
| Local ODBC helper binaries | `listdsn.c`, `listtables.c` | Not part of request/response web flow |

## CODE MAP
| Symbol | Type | Location | Role |
|--------|------|----------|------|
| `dbquery` | function | `header.inc` | Execute ODBC queries through global `$dbh` |
| `db_fetch_array` | function | `header.inc` | Fetch one ODBC row |
| `db_error` | function | `header.inc` | Read ODBC error from active handle |
| `print_ingredients` | function | `header.inc` | Render recipe ingredients table and rollups |
| `print_ingredient_rows` | function | `header.inc` | Recursive ingredient/subrecipe traversal |
| `print_category_options` | function | `header.inc` | Render category `<option>` list |
| `print_ingredient_options` | function | `header.inc` | Render ingredient `<option>` list |
| `print_recipe_options` | function | `header.inc` | Render recipe `<option>` list |

## CONVENTIONS
- Flat root-level app: nearly all live code sits in the repo root; there is no `src/`, router, or framework structure.
- `header.inc` is the shared bootstrap for session state, ODBC setup, and helper functions; most page changes touch it indirectly.
- File names are lowercase and descriptive: `showrecipe.php`, `editrecipe.php`, `editrecipeingredients.php`.
- Code style is procedural PHP with mixed HTML/PHP rendering and lowercase underscore-style helper names.
- PHP tag usage is inconsistent: some files use `<?php`, others require `short_open_tag` via `<?`.

## ANTI-PATTERNS (THIS PROJECT)
- Do not assume modern PHP compatibility. Files still use short tags and removed `mysql_*` calls in places like `server.php`, `editrecipe.php`, and `editingredient.php`.
- Do not assume missing includes are in-repo. `header.inc` requires `../mobile_device_detect.php`; edit flows require `../fckeditor/fckeditor.php`.
- Do not treat `backup/` as active code. It contains editor backup copies with `~` suffixes.
- Do not assume safe input handling. Queries are built inline from request data across the app; inspect each flow before editing.
- Do not assume local reproducibility without environment work. `header.inc` hardcodes Composite Information Server ODBC paths and expects a `recipes` DSN.

## UNIQUE STYLES
- SQL uses quoted identifiers like `"time"`, `"size"`, and `"type"`, likely due to the ODBC/Composite backend.
- The app relies on session variables for UI state such as sort order, search flags, and mobile/wide display mode.
- `header.inc` is both configuration and view-helper library; changing it can affect every endpoint.
- Some standalone utilities bypass the shared bootstrap entirely: `getdata.php`, `odbctest.php`, and the C helpers.

## COMMANDS
```bash
php -l index.php
php -l header.inc
php -l editrecipe.php
php -l editingredient.php
./listdsn
./listtables
php odbctest.php
```

## NOTES
- Current repo signals: no tests, no CI workflows, no Composer config, no framework config.
- `server.php` appears internally inconsistent because it requires `header.inc` but still reads rows with `mysql_fetch_assoc()`.
- `getdata.php` and nutrition-copy code in `editingredient.php` fetch remote URLs directly; verify intent before touching them.
- Use the root `README.md` for setup context; there are no child modules with enough depth to justify nested `AGENTS.md` files right now.
