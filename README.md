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
- Database credentials come from environment variables.
- `header.inc` can use `../mobile_device_detect.php` when present, but falls back cleanly if it is missing.
- `editrecipe.php` uses FCKeditor when available and falls back to a plain `<textarea>` otherwise.

## Local setup

1. Run the app in an environment with PHP and the `mysqli` extension enabled.
2. Set `RECIPES_DB_HOST`, `RECIPES_DB_PORT`, `RECIPES_DB_NAME`, `RECIPES_DB_USER`, and `RECIPES_DB_PASS` for your MySQL database.
3. Optionally place `mobile_device_detect.php` at `../mobile_device_detect.php`.
4. Optionally place FCKeditor at `../fckeditor/fckeditor.php`; otherwise recipe editing falls back to a normal textarea.
5. Serve the directory with your PHP-capable web server.

## Notes for future cleanup

- Input handling is currently inline and would benefit from validation and escaping improvements.
- Database access still uses inline SQL and would benefit from prepared statements.
- A modern setup guide and schema documentation still need to be added.
