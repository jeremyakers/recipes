<?php

function render_page_start($title, $heading, $eyebrow = '')
{
   echo '<!DOCTYPE html>';
   echo '<html><head><meta charset="utf-8">';
   echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
   echo '<title>' . h($title) . '</title>';
   echo '<style>
      :root {
         --bg: #f6f1e8;
         --paper: #fffdf9;
         --ink: #2b2218;
         --muted: #6f6253;
         --line: #d8c8b3;
         --accent: #8b4f2a;
         --accent-soft: #f3dfc9;
         --shadow: 0 18px 45px rgba(77, 47, 22, 0.12);
      }
      * { box-sizing: border-box; }
      body {
         margin: 0;
         font-family: Georgia, "Times New Roman", serif;
         color: var(--ink);
         background: linear-gradient(180deg, #efe4d4 0%, var(--bg) 28%, #fbf8f3 100%);
      }
      a { color: var(--accent); }
      .shell {
         width: min(1100px, calc(100% - 32px));
         margin: 0 auto;
         padding: 24px 0 48px;
      }
      .masthead {
         background: var(--paper);
         border: 1px solid rgba(139, 79, 42, 0.14);
         border-radius: 24px;
         box-shadow: var(--shadow);
         padding: 24px;
         margin-bottom: 24px;
      }
      .eyebrow {
         margin: 0 0 8px;
         color: var(--muted);
         font-size: 0.8rem;
         letter-spacing: 0.16em;
         text-transform: uppercase;
      }
      h1, h2, h3 { margin: 0 0 12px; }
      .masthead h1 { font-size: clamp(2rem, 4vw, 3.1rem); line-height: 1.05; }
      .masthead p { margin: 0; max-width: 60ch; color: var(--muted); }
      .topnav {
         display: flex;
         flex-wrap: wrap;
         gap: 10px;
         margin-top: 18px;
      }
      .topnav a, input[type=submit], button {
         appearance: none;
         border: 1px solid rgba(139, 79, 42, 0.25);
         background: var(--accent-soft);
         color: var(--ink);
         border-radius: 999px;
         padding: 10px 14px;
         text-decoration: none;
         font: inherit;
         cursor: pointer;
      }
      .card {
         background: var(--paper);
         border: 1px solid rgba(139, 79, 42, 0.12);
         border-radius: 22px;
         box-shadow: var(--shadow);
         padding: 20px;
         margin-bottom: 20px;
      }
      table {
         width: 100%;
         border-collapse: collapse;
      }
      th, td {
         padding: 10px 12px;
         border: 1px solid var(--line);
         vertical-align: top;
      }
      th {
         background: #f8efe4;
         text-align: left;
      }
      input[type=text], select, textarea {
         width: 100%;
         max-width: 100%;
         padding: 10px 12px;
         border-radius: 12px;
         border: 1px solid var(--line);
         background: #fff;
         font: inherit;
      }
      textarea { min-height: 260px; }
      .form-table td:first-child, .form-table th:first-child { width: 220px; }
      .table-wrap { overflow-x: auto; }
      .inline-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
      .plain-link { display: inline-block; margin-top: 8px; }
      @media (max-width: 640px) {
         .shell { width: min(100% - 20px, 1100px); padding-top: 16px; }
         .masthead, .card { border-radius: 18px; padding: 16px; }
         .form-table, .form-table tbody, .form-table tr, .form-table td { display: block; width: 100%; }
         .form-table td:first-child { width: auto; border-bottom: none; padding-bottom: 4px; }
         .form-table td:last-child { border-top: none; padding-top: 0; }
      }
   </style>';
   echo '</head><body><div class="shell">';
   echo '<header class="masthead">';
   if($eyebrow)
      echo '<p class="eyebrow">' . h($eyebrow) . '</p>';
   echo '<h1>' . h($heading) . '</h1>';
   echo '<p>Legacy recipe data, modernized enough to browse and manage comfortably.</p>';
   echo '<nav class="topnav">';
   echo '<a href="index.php">Recipes</a>';
   echo '<a href="ingredients.php">Ingredients</a>';
   echo '<a href="editrecipe.php">New Recipe</a>';
   echo '<a href="editingredient.php">New Ingredient</a>';
   echo '</nav></header>';
}

function render_page_end()
{
   echo '</div></body></html>';
}
