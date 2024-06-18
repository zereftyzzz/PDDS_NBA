<?php
require_once 'autoload.php';

$client = new MongoDB\Client();
$collection = $client->pdds_proyek->player;

$term = isset($_GET['term']) ? $_GET['term'] : '';

$regex = new MongoDB\BSON\Regex('^' . preg_quote($term), 'i'); // case-insensitive prefix search

$cursor = $collection->distinct('player', ['player' => $regex]);

echo json_encode($cursor);
?>