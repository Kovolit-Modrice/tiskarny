<?php

echo "<h1>Seznam tiskáren Kovolit, a.s.</h1>"; // ← Tady doplněn hlavní nadpis


function ping($ip) {
    exec("ping -n 1 -w 1000 $ip", $output, $status); // Windows ping
    return $status === 0 ? "online" : "offline";
}

// Načtení tiskáren z CSV
$printers = [];
if (($handle = fopen("tiskarny.csv", "r")) !== FALSE) {
    // Předpokládáme, že první řádek je hlavička
    fgetcsv($handle, 1000, ";");

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $printers[] = [
            "brand" => trim($data[0]),
            "model" => trim($data[1]),
            "ip" => trim($data[2]),
            "location" => trim($data[3])
        ];
    }
    fclose($handle);
}

// Třídění podle značky a modelu
usort($printers, function($a, $b) {
    return [$a['brand'], $a['model']] <=> [$b['brand'], $b['model']];
});

// Rozdělení do struktury značky → model → tiskárny
$brands = [];
foreach ($printers as $printer) {
    $brands[$printer['brand']][$printer['model']][] = $printer;
}

// Výpis
foreach ($brands as $brand => $models) {
    echo "<div class='brand'><h2>$brand</h2>";
    foreach ($models as $model => $printers) {
        echo "<div class='model'><h3>$model</h3><ul>";
        foreach ($printers as $printer) {
            $status = ping($printer['ip']);
            echo "<li>
                    <div class='status $status'></div>
                    <div class='printer-info'><span>Tiskárna:</span> {$printer['brand']} - {$printer['model']}</div>
                    <div class='printer-info'><span>IP adresa:</span> <a href='http://{$printer['ip']}' target='_blank' class='ip-link'>{$printer['ip']}</a></div>
                    <div class='printer-info'><span>Lokalita:</span> {$printer['location']}</div>
                  </li>";
        }
        echo "</ul></div>";
    }
    echo "</div>";
}
?>
