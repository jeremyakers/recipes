# Smoke Baseline

## Local app boot

Start the local PHP server from the repo root:

```bash
php -S 127.0.0.1:8000
```

The app reads MySQL credentials from `config.local.php`.

## Run the baseline

```bash
python .sisyphus/smoke_baseline.py
```

## Evidence

- JSON smoke results: `.sisyphus/evidence/task-1-smoke-baseline.json`
- PHP lint output: `.sisyphus/evidence/task-1-smoke-baseline-lint.txt`
- Failure output: `.sisyphus/evidence/task-1-smoke-baseline-error.txt`

## Covered flows

- `index.php`
- `ingredients.php`
- `showrecipe.php`
- `editrecipe.php`
- `editingredient.php`
- `editrecipeingredients.php`

## Notes

- The current baseline uses HTTP form submissions and direct DB assertions.
- Playwright MCP browser automation is currently blocked in this environment because the expected Chrome binary path is unavailable.
