<?php

$db_config = array();
require __DIR__ . '/../config.local.php';

if($argc < 2)
{
   fwrite(STDERR, "Usage: php .sisyphus/db_query.php \"SELECT ...\"\n");
   exit(1);
}

$dbh = mysqli_connect(
   $db_config['host'],
   $db_config['username'],
   $db_config['password'],
   $db_config['database'],
   (int)$db_config['port']
);

if(!$dbh)
{
   fwrite(STDERR, mysqli_connect_error() . "\n");
   exit(1);
}

mysqli_set_charset($dbh, 'utf8mb4');

$sql = str_replace(
   array('"time"', '"size"', '"type"'),
   array('`time`', '`size`', '`type`'),
   $argv[1]
);

$result = mysqli_query($dbh, $sql);

if($result === false)
{
   fwrite(STDERR, mysqli_error($dbh) . "\n");
   exit(1);
}

$rows = array();
if($result === true)
{
   echo json_encode($rows, JSON_PRETTY_PRINT) . "\n";
   exit(0);
}

while(($row = mysqli_fetch_assoc($result)))
   $rows[] = $row;

echo json_encode($rows, JSON_PRETTY_PRINT) . "\n";
