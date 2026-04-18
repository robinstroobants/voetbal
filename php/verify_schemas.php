<?php
$f = $_GET['file'] ?? '8v8_1gk_4x15_10sp.php';
$f_path = 'wisselschemas/' . basename($f);
if(file_exists($f_path)) {
    require $f_path;
} else {
    die("File not found");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Schema Verificatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border:none; border-radius: 12px; }
        .card-header { background-color: #fca311; color: white; font-weight: bold; border-radius: 12px 12px 0 0 !important; }
        .table-sm td, .table-sm th { padding: 0.5rem; text-align: center; }
        .highlight { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fa-solid fa-list-check"></i> Nieuwe Schema's (10000+) Testen</h1>
        <p class="text-muted">Analyse van posities & mid-kwartier shifts voor: <code><?php echo $f; ?></code></p>
        
        <div class="row">
        <?php foreach ($ws as $schema_id => $schema) {
            if ($schema_id < 10000) continue; 
            
            $playtimes = [];
            $playerPos = [];
            $subErrors = [];
            
            foreach ($schema as $idx => $part) {
                $dur = $part['duration'] / 60;
                foreach ($part['lineup'] as $pos => $pid) {
                    if (!isset($playtimes[$pid])) $playtimes[$pid] = 0;
                    $playtimes[$pid] += $dur;
                    if ($pid != 0) {
                        $playerPos[$pid][$pos] = true;
                    }
                }
                
                // Check if odd blocks (mid-quarter) have strictly 2 IN and 2 OUT elements
                if ($idx % 2 == 1) {
                    if (isset($part['subs'])) {
                        $inCount = count($part['subs']['in']);
                        $outCount = count($part['subs']['out']);
                        // Check logic correctly for 9sp OR 10sp rules
                        $expectedVars = strpos($f, '9sp') !== false ? 1 : 2;
                        if ($inCount != $expectedVars || $outCount != $expectedVars) {
                            $subErrors[] = "Blok $idx had $inCount/$outCount wissels (verwacht $expectedVars/$expectedVars)";
                        }
                    } else if (count($part['bench']) > 0) {
                        $subErrors[] = "Blok $idx mist subs block!";
                    }
                }
            }
            
            $allValidPos = true;
            foreach ($playerPos as $pid => $arr) {
                $c = count($arr);
                if ($c < 3 || $c > 4) $allValidPos = false;
            }

            // Group players by playtime
            $blocks = [];
            foreach ($playtimes as $pid => $time) {
                if (!isset($blocks["$time"])) {
                    $blocks["$time"] = [];
                }
                $blocks["$time"][] = $pid;
            }
            ksort($blocks);
            
            $hasSubError = count($subErrors) > 0;
            $cardClass = ($allValidPos && !$hasSubError) ? 'border-success' : 'border-danger';
        ?>
            <div class="col-md-4">
                <div class="card <?php echo $cardClass; ?>">
                    <div class="card-header <?php echo ($allValidPos && !$hasSubError) ? 'bg-success' : 'bg-danger'; ?>">
                        Schema ID #<?php echo $schema_id; ?>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm m-0">
                            <thead>
                                <tr>
                                    <th>Speeltijd</th>
                                    <th>Aantal Spelers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blocks as $time => $pids) { ?>
                                <tr>
                                    <td class="highlight"><?php echo $time; ?>'</td>
                                    <td>
                                        <?php 
                                            if ($time == 60 && in_array(0, $pids)) {
                                                echo "<span class='badge bg-warning text-dark'>Doelman</span>";
                                                $count = count(array_diff($pids, [0]));
                                                if($count > 0) echo " + $count VD";
                                            } else {
                                                echo count($pids) . " VD";
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr class="table-<?php echo $allValidPos ? 'success' : 'danger'; ?>">
                                    <td colspan="2">
                                        <strong>Pos Limieten (3-4):</strong><br>
                                        <?php
                                            $out = [];
                                            foreach($playerPos as $pid => $arr) {
                                                $out[] = "P$pid: ".count($arr);
                                            }
                                            echo implode(" | ", $out);
                                        ?>
                                    </td>
                                </tr>
                                <?php if ($hasSubError) { ?>
                                <tr class="table-danger">
                                    <td colspan="2">
                                        <strong>Foute Mid-Kwartier Shift:</strong><br>
                                        <?php echo implode("<br>", $subErrors); ?>
                                    </td>
                                </tr>
                                <?php } else { ?>
                                <tr class="table-success">
                                    <td colspan="2">
                                        <strong>Wissel Logica:</strong> Perfect (2/2)
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
    </div>
</body>
</html>
