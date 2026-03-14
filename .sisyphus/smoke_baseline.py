#!/usr/bin/env python3

import json
import os
import re
import subprocess
import sys
import time
import urllib.parse
import urllib.request
from http.cookiejar import CookieJar
from pathlib import Path


ROOT = Path(__file__).resolve().parent.parent
EVIDENCE_DIR = ROOT / ".sisyphus" / "evidence"
BASE_URL = os.environ.get("RECIPES_BASE_URL", "http://127.0.0.1:8000")


def run(command):
    return subprocess.run(command, cwd=ROOT, text=True, capture_output=True, check=True)


def db_query(sql):
    result = run(["php", ".sisyphus/db_query.php", sql])
    return json.loads(result.stdout)


def db_exec(sql):
    run(["php", ".sisyphus/db_query.php", sql])


def opener():
    return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))


def request(client, path, data=None):
    url = BASE_URL + path
    payload = None
    if data is not None:
        payload = urllib.parse.urlencode(data).encode("utf-8")
    req = urllib.request.Request(url, data=payload)
    with client.open(req) as response:
        body = response.read().decode("utf-8", errors="replace")
        return {"status": response.status, "url": response.geturl(), "body": body}


def require_contains(text, needle, label):
    if needle not in text:
        raise AssertionError(f"{label}: missing '{needle}'")


def first_row(sql):
    rows = db_query(sql)
    if not rows:
        raise AssertionError(f"No rows for query: {sql}")
    return rows[0]


def escape_sql(value):
    return value.replace("\\", "\\\\").replace("'", "\\'")


def main():
    EVIDENCE_DIR.mkdir(parents=True, exist_ok=True)
    report = {
        "base_url": BASE_URL,
        "started_at": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        "checks": [],
    }

    lint_targets = [
        "header.inc",
        "index.php",
        "ingredients.php",
        "showrecipe.php",
        "editrecipe.php",
        "editingredient.php",
        "editrecipeingredients.php",
        "pricereport.php",
        "server.php",
        "getdata.php",
        "odbctest.php",
        ".sisyphus/db_query.php",
    ]
    lint_output = []
    lint_ok = True
    for target in lint_targets:
        syntax = run(["php", "-l", target])
        lint_output.append(syntax.stdout.strip())
        lint_ok = lint_ok and ("No syntax errors detected" in syntax.stdout)
    (EVIDENCE_DIR / "task-1-smoke-baseline-lint.txt").write_text(
        "\n".join(lint_output) + "\n", encoding="utf-8"
    )
    report["checks"].append(
        {"name": "php_lint_suite", "ok": lint_ok, "targets": lint_targets}
    )

    client = opener()

    sample_recipe = first_row("SELECT id, name FROM recipes ORDER BY id LIMIT 1")
    sample_ingredient = first_row(
        "SELECT id, name FROM ingredients ORDER BY id LIMIT 1"
    )
    sample_category = first_row("SELECT id FROM categories ORDER BY id LIMIT 1")

    recipe_name = f"Smoke Recipe {int(time.time())}"
    ingredient_name = f"Smoke Ingredient {int(time.time())}"
    recipe_id = None
    ingredient_id = None

    try:
        index_page = request(client, "/index.php")
        require_contains(index_page["body"], "Recipe Search", "index page")
        report["checks"].append({"name": "index_page", "ok": True})

        ingredients_page = request(client, "/ingredients.php")
        require_contains(
            ingredients_page["body"], "Ingredient Search", "ingredients page"
        )
        report["checks"].append({"name": "ingredients_page", "ok": True})

        detail_page = request(client, f"/showrecipe.php?recipeid={sample_recipe['id']}")
        require_contains(detail_page["body"], "Recipe Details for:", "showrecipe page")
        require_contains(detail_page["body"], "Instructions", "showrecipe page")
        report["checks"].append({"name": "showrecipe_page", "ok": True})

        create_recipe = request(
            client,
            "/editrecipe.php",
            {
                "save": "Save",
                "recipeid": "0",
                "name": recipe_name,
                "time": "15",
                "category": str(sample_category["id"]),
                "servings": "2",
                "calories": "100",
                "ed": "1.0",
                "carbs": "10",
                "fat": "2",
                "protein": "5",
                "fiber": "1",
                "instructions": "<p>Smoke test instructions</p>",
            },
        )
        require_contains(
            create_recipe["body"], "Recipe saved successfully.", "create recipe"
        )
        recipe_id = first_row(
            f"SELECT id FROM recipes WHERE name = '{escape_sql(recipe_name)}' ORDER BY id DESC LIMIT 1"
        )["id"]
        report["checks"].append(
            {"name": "create_recipe", "ok": True, "recipe_id": recipe_id}
        )

        update_recipe = request(
            client,
            "/editrecipe.php",
            {
                "save": "Save",
                "recipeid": str(recipe_id),
                "name": recipe_name + " Updated",
                "time": "20",
                "category": str(sample_category["id"]),
                "servings": "4",
                "calories": "120",
                "ed": "1.2",
                "carbs": "11",
                "fat": "3",
                "protein": "6",
                "fiber": "2",
                "instructions": "<p>Updated smoke test instructions</p>",
            },
        )
        require_contains(
            update_recipe["body"], "Recipe saved successfully.", "update recipe"
        )
        report["checks"].append({"name": "update_recipe", "ok": True})

        create_ingredient = request(
            client,
            "/editingredient.php",
            {
                "save": "Save",
                "ingredientid": "0",
                "name": ingredient_name,
                "size": "8",
                "unit": "2",
                "cost": "4.25",
                "units": "1",
                "serving_size": "100",
                "ounces_cup": "8",
                "weight_select": "2",
                "volume_select": "6",
                "calories": "150",
                "carbs": "12",
                "fat": "7",
                "protein": "3",
                "fiber": "1",
                "recipe": "0",
            },
        )
        require_contains(
            create_ingredient["body"],
            "Ingredient saved successfully.",
            "create ingredient",
        )
        ingredient_id = first_row(
            f"SELECT id FROM ingredients WHERE name = '{escape_sql(ingredient_name)}' ORDER BY id DESC LIMIT 1"
        )["id"]
        report["checks"].append(
            {"name": "create_ingredient", "ok": True, "ingredient_id": ingredient_id}
        )

        update_ingredient = request(
            client,
            "/editingredient.php",
            {
                "save": "Save",
                "ingredientid": str(ingredient_id),
                "name": ingredient_name + " Updated",
                "size": "10",
                "unit": "2",
                "cost": "5.25",
                "units": "1",
                "serving_size": "100",
                "ounces_cup": "8",
                "weight_select": "2",
                "volume_select": "6",
                "calories": "175",
                "carbs": "14",
                "fat": "8",
                "protein": "4",
                "fiber": "2",
                "recipe": "0",
            },
        )
        require_contains(
            update_ingredient["body"],
            "Ingredient saved successfully.",
            "update ingredient",
        )
        ingredient_name = ingredient_name + " Updated"
        report["checks"].append({"name": "update_ingredient", "ok": True})

        count_before = first_row(
            f"SELECT COUNT(*) count FROM recipe_ingredients WHERE recipe = '{recipe_id}'"
        )["count"]
        add_ingredient = request(
            client,
            "/editrecipeingredients.php",
            {
                "addingredient": "Add Ingredient",
                "recipeid": str(recipe_id),
                "count": str(count_before),
                "amount": "1",
                "unit": "6",
                "searchterm": ingredient_name,
                "comment": "smoke comment",
            },
        )
        require_contains(
            add_ingredient["body"], ingredient_name, "add recipe ingredient"
        )
        require_contains(
            add_ingredient["body"], "smoke comment", "add recipe ingredient comment"
        )
        report["checks"].append({"name": "add_recipe_ingredient", "ok": True})

        remove_ingredient = request(
            client,
            "/editrecipeingredients.php",
            {
                "remingredient": "Remove",
                "recipeid": str(recipe_id),
                "ingredient": str(ingredient_id),
                "scrollto": "ingredient",
            },
        )
        require_contains(
            remove_ingredient["body"],
            "Recipe Ingredient Editor",
            "remove recipe ingredient",
        )
        if "smoke comment" in remove_ingredient["body"]:
            raise AssertionError(
                "remove recipe ingredient: removed row comment still present after removal"
            )
        report["checks"].append({"name": "remove_recipe_ingredient", "ok": True})

        delete_recipe_fail = request(
            client,
            "/editrecipe.php",
            {
                "delete": "Delete",
                "recipeid": str(recipe_id),
            },
        )
        require_contains(
            delete_recipe_fail["body"],
            "You must select 'Yes'",
            "recipe delete confirmation",
        )
        report["checks"].append({"name": "recipe_delete_confirmation", "ok": True})

        delete_ingredient_fail = request(
            client,
            "/editingredient.php",
            {
                "delete": "Delete",
                "ingredientid": str(ingredient_id),
            },
        )
        require_contains(
            delete_ingredient_fail["body"],
            "You must select 'Yes'",
            "ingredient delete confirmation",
        )
        report["checks"].append({"name": "ingredient_delete_confirmation", "ok": True})

        missing_recipe = request(client, "/editrecipeingredients.php")
        require_contains(
            missing_recipe["body"],
            "You must select a recipe first.",
            "missing recipe id",
        )
        report["checks"].append({"name": "missing_recipe_guard", "ok": True})

    finally:
        if recipe_id is not None:
            request(
                client,
                "/editrecipe.php",
                {
                    "delete": "Delete",
                    "recipeid": str(recipe_id),
                    "deleteconfirm": "2",
                },
            )
        if ingredient_id is not None:
            request(
                client,
                "/editingredient.php",
                {
                    "delete": "Delete",
                    "ingredientid": str(ingredient_id),
                    "deleteconfirm": "2",
                },
            )

    report["completed_at"] = time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime())
    report_path = EVIDENCE_DIR / "task-1-smoke-baseline.json"
    report_path.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, indent=2))


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        error_path = EVIDENCE_DIR / "task-1-smoke-baseline-error.txt"
        error_path.write_text(str(exc) + "\n", encoding="utf-8")
        print(str(exc), file=sys.stderr)
        sys.exit(1)
