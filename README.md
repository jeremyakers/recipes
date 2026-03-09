# recipes

Legacy PHP recipe management app.

## What it does

- Search recipes by name, category, and ingredient filters.
- View recipe details, ingredient lists, nutrition data, and instructions.
- Create and edit recipes and ingredients.
- Generate a simple ingredient price report.
- Connect to the backing data source through ODBC.

## Project layout

- `index.php` - main recipe search page
- `showrecipe.php` - recipe details page
- `editrecipe.php` - recipe editor
- `ingredients.php` - ingredient search page
- `editingredient.php` - ingredient editor
- `editrecipeingredients.php` - recipe ingredient editing flow
- `pricereport.php` - ingredient cost update/report page
- `header.inc` - shared session setup, database connection, and helper functions
- `odbctest.php` - small ODBC connectivity test script
- `listdsn.c`, `listtables.c` - local ODBC helper utilities

## Runtime notes

- The app uses older PHP style, including short tags in some files.
- Database access is routed through ODBC in `header.inc`.
- The current configuration expects a local Composite Information Server ODBC install.
- `header.inc` also includes `../mobile_device_detect.php`, which is not part of this repository.

## Local setup

1. Run the app in an environment with PHP and ODBC support enabled.
2. Configure the expected ODBC driver and DSN for the `recipes` database.
3. Make sure `mobile_device_detect.php` exists at the expected relative path, or update the include.
4. Serve the directory with your PHP-capable web server.

## Notes for future cleanup

- Input handling is currently inline and would benefit from validation and escaping improvements.
- Database configuration is embedded in shared include code and should eventually move to environment-based configuration.
- A modern setup guide and schema documentation still need to be added.
