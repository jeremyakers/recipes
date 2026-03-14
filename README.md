# recipes

Legacy PHP recipe management app.

## What it does

- Search recipes by name, category, and ingredient filters.
- View recipe details, ingredient lists, nutrition data, and instructions.
- Create and edit recipes and ingredients.
- Generate a simple ingredient price report.
- Connect to a MySQL database through the shared `mysqli` bootstrap in `header.inc`.

## Project layout

- `index.php` - main recipe search page
- `showrecipe.php` - recipe details page
- `layout.inc` - shared shell and styling for public-facing pages
- `editrecipe.php` - recipe editor
- `ingredients.php` - ingredient search page
- `editingredient.php` - ingredient editor
- `editrecipeingredients.php` - recipe ingredient editing flow
- `pricereport.php` - ingredient cost update/report page
- `header.inc` - shared session setup, database connection, and helper functions
- `odbctest.php` - simple database connectivity smoke test
- `listdsn.c`, `listtables.c` - legacy ODBC helper utilities kept for reference

## Runtime notes

- The app now uses full `<?php` tags, so it does not depend on `short_open_tag`.
- Database access is routed through `mysqli` in `header.inc`.
- Database credentials can come from a local `config.local.php` file, with environment variables as a fallback.
- `header.inc` can use `../mobile_device_detect.php` when present, but falls back cleanly if it is missing.
- `editrecipe.php` enhances the recipe instructions textarea with CKEditor 5 when the editor script loads.
- `editingredient.php` can use USDA FoodData Central for nutrition lookup when a local API key is configured.
- `editrecipeingredients.php` now uses a native datalist ingredient picker instead of the old Scriptaculous autocomplete dependency.

## Local setup

1. Run the app in an environment with PHP and the `mysqli` extension enabled.
2. Copy `config.local.example.php` to `config.local.php` and fill in your real MySQL host, database, username, and password.
3. If you prefer, you can still use `RECIPES_DB_HOST`, `RECIPES_DB_PORT`, `RECIPES_DB_NAME`, `RECIPES_DB_USER`, and `RECIPES_DB_PASS` instead of the local config file.
4. Optionally place `mobile_device_detect.php` at `../mobile_device_detect.php`.
5. Optionally add `usda_api_key` to `$app_config` in `config.local.php` if you want USDA nutrition lookup in the ingredient editor.
6. Serve the directory with your PHP-capable web server.

## Notes for future cleanup

- Input handling is currently inline and would benefit from validation and escaping improvements.
- Database access still uses inline SQL and would benefit from prepared statements.
- A modern setup guide and schema documentation still need to be added.
