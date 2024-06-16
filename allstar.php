<?php
include 'navbar2.php';
require_once 'autoload.php';

$client = new MongoDB\Client();

$collection = $client->pdds_proyek->player;
$teamCollection = $client->pdds_proyek->team;

$tempWin = 0;
$allStar = 0.0;

$filter = [];

$players = [];
$topPlayers = [];
$averageStats = [
    'trb_per_game' => 0.0,
    'ast_per_game' => 0.0,
    'stl_per_game' => 0.0,
    'blk_per_game' => 0.0,
    'pts_per_game' => 0.0,
];

$check = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['name']) || !empty($_GET['position']) || !empty($_GET['year'])) {
        $check = true;

        if (!empty($_GET['name'])) {
            $filter['player'] = ['$regex' => $_GET['name'], '$options' => 'i'];
        }
        if (!empty($_GET['position'])) {
            $filter['pos'] = $_GET['position'];
        }
        if (!empty($_GET['year'])) {
            $filter['season'] = (int)$_GET['year'];
        }

        $playersCursor = $collection->find($filter);
        $players = $playersCursor->toArray();

        if (!empty($_GET['year'])) {
            $topFilter = [
                'season' => (int)$_GET['year'],
            ];

            $topPlayersCursor = $collection->find($topFilter, [
                'sort' => ['pts_per_game' => -1],
                'limit' => 10
            ]);

            $topPlayers = $topPlayersCursor->toArray();

            $totalPlayers = count($topPlayers);
            foreach ($topPlayers as $player) {
                $averageStats['trb_per_game'] += (float)$player['trb_per_game'];
                $averageStats['ast_per_game'] += (float)$player['ast_per_game'];
                $averageStats['stl_per_game'] += (float)$player['stl_per_game'];
                $averageStats['blk_per_game'] += (float)$player['blk_per_game'];
                $averageStats['pts_per_game'] += (float)$player['pts_per_game'];
            }

            if ($totalPlayers > 0) {
                $averageStats['trb_per_game'] /= (float)$totalPlayers;
                $averageStats['ast_per_game'] /= (float)$totalPlayers;
                $averageStats['stl_per_game'] /= (float)$totalPlayers;
                $averageStats['blk_per_game'] /= (float)$totalPlayers;
                $averageStats['pts_per_game'] /= (float)$totalPlayers;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            margin-right: 10px;
        }

        input,
        select {
            margin-right: 10px;
        }

        button {
            padding: 5px 10px;
        }

        .highlight-green {
            background-color: #d4edda;
        }

        .highlight-red {
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <form method="GET" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="">

        <label for="position">Position:</label>
        <select id="position" name="position">
            <option value="">All</option>
            <option value="PG">PG</option>
            <option value="SG">SG</option>
            <option value="SF">SF</option>
            <option value="PF">PF</option>
            <option value="C">C</option>
        </select>

        <label for="year">Year:</label>
        <select id="year" name="year">
            <option value="">All</option>
            <?php for ($year = 1947; $year <= 2024; $year++) : ?>
                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Search</button>
    </form>

    <!-- Jika tombol search sudah di klik -->
    <?php if ($check) : ?>

        <!-- Jika output data player yang ditemukan hanya 1 -->
        <?php if (count($players) === 1) : ?>
            <?php $player = $players[0]; ?>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $teamDetails = $teamCollection->findOne(['abbreviation' => $player['tm']]);
                    $teamName = $teamDetails ? $teamDetails['team'] : 'Unknown Team';
                    $teamWinPercentage = $teamDetails ? round(($teamDetails['w'] / ($teamDetails['w'] + $teamDetails['l'])) * 100, 2) : 'N/A';
                    $highlightRebound = $player['trb_per_game'] > $averageStats['trb_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightAssist = $player['ast_per_game'] > $averageStats['ast_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightSteal = $player['stl_per_game'] > $averageStats['stl_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightBlock = $player['blk_per_game'] > $averageStats['blk_per_game'] ? 'highlight-green' : 'highlight-red';
                    $highlightPoints = $player['pts_per_game'] > $averageStats['pts_per_game'] ? 'highlight-green' : 'highlight-red';

                    $tempWin += ($player['trb_per_game'] > $averageStats['trb_per_game']) ? 1 : 0;
                    $tempWin += ($player['ast_per_game'] > $averageStats['ast_per_game']) ? 1 : 0;
                    $tempWin += ($player['stl_per_game'] > $averageStats['stl_per_game']) ? 1 : 0;
                    $tempWin += ($player['blk_per_game'] > $averageStats['blk_per_game']) ? 1 : 0;
                    $tempWin += ($player['pts_per_game'] > $averageStats['pts_per_game']) ? 1 : 0;
                    $allStar = round((float)($tempWin / 5) * $teamWinPercentage, 2)
                    ?>
                    <tr>
                        <td><?php echo $player['player']; ?></td>
                        <td><?php echo $player['pos']; ?></td>
                        <td><?php echo $player['age']; ?></td>
                        <td><?php echo $player['experience']; ?></td>
                        <td><?php echo $player['season']; ?></td>
                        <td><?php echo $player['tm']; ?></td>
                        <td class="<?php echo $highlightRebound; ?>"><?php echo $player['trb_per_game']; ?></td>
                        <td class="<?php echo $highlightAssist; ?>"><?php echo $player['ast_per_game']; ?></td>
                        <td class="<?php echo $highlightSteal; ?>"><?php echo $player['stl_per_game']; ?></td>
                        <td class="<?php echo $highlightBlock; ?>"><?php echo $player['blk_per_game']; ?></td>
                        <td class="<?php echo $highlightPoints; ?>"><?php echo $player['pts_per_game']; ?></td>
                    </tr>
                </tbody>
            </table>
            <p><strong>Team:</strong> <?php echo $teamName; ?></p>
            <p><strong>Team Win:</strong> <?php echo $teamWinPercentage; ?>%</p>
            <p><strong>All-Star Probability:</strong> <?php echo $allStar; ?>%</p>

            <!-- Jika output data player yang ditemukan lebih dari 1 -->
        <?php elseif (count($players) > 1) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $player) : ?>
                        <tr>
                            <td><?php echo $player['player']; ?></td>
                            <td><?php echo $player['pos']; ?></td>
                            <td><?php echo $player['age']; ?></td>
                            <td><?php echo $player['experience']; ?></td>
                            <td><?php echo $player['season']; ?></td>
                            <td><?php echo $player['tm']; ?></td>
                            <td><?php echo $player['trb_per_game']; ?></td>
                            <td><?php echo $player['ast_per_game']; ?></td>
                            <td><?php echo $player['stl_per_game']; ?></td>
                            <td><?php echo $player['blk_per_game']; ?></td>
                            <td><?php echo $player['pts_per_game']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Jika top player tidak kosong -->
        <?php if (!empty($topPlayers)) : ?>
            <h2>Top 10 Players in <?php echo $_GET['year']; ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Player Name</th>
                        <th>Position</th>
                        <th>Age</th>
                        <th>Experience</th>
                        <th>Season</th>
                        <th>Team</< /th>
                        <th>Rebound</th>
                        <th>Assist</th>
                        <th>Steal</th>
                        <th>Block</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topPlayers as $player) : ?>
                        <tr>
                            <td><?php echo $player['player']; ?></td>
                            <td><?php echo $player['pos']; ?></td>
                            <td><?php echo $player['age']; ?></td>
                            <td><?php echo $player['experience']; ?></td>
                            <td><?php echo $player['season']; ?></td>
                            <td><?php echo $player['tm']; ?></td>
                            <td><?php echo $player['trb_per_game']; ?></td>
                            <td><?php echo $player['ast_per_game']; ?></td>
                            <td><?php echo $player['stl_per_game']; ?></td>
                            <td><?php echo $player['blk_per_game']; ?></td>
                            <td><?php echo $player['pts_per_game']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Top 10 Players Stats in <?php echo $_GET['year']; ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Stats</th>
                        <th>Value</< /th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rebound</td>
                        <td><?php echo round($averageStats['trb_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Assist</td>
                        <td><?php echo round($averageStats['ast_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Steal</td>
                        <td><?php echo round($averageStats['stl_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Block</td>
                        <td><?php echo round($averageStats['blk_per_game'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Points</td>
                        <td><?php echo round($averageStats['pts_per_game'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Jika tombol search belum di klik -->
    <?php else : ?>
    <?php endif; ?>
</body>

</html>