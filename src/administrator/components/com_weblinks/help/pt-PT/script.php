<?php
/**
 * Esportatore JDocManual - Versione Bulk (5 file in un colpo solo)
 * Elabora una lista di URL e genera i relativi file HTML locali
 */

// 1. CONFIGURAZIONE MULTI-FILE
$pagine = [
    "https://jdocmanual.org/pt/jdocmanual?article=help/weblinks/weblinks-categories-edit" => "weblinks-categories-edit.html",
    "https://jdocmanual.org/pt/jdocmanual?article=help/weblinks/weblinks-links"           => "weblinks-links.html",
    "https://jdocmanual.org/pt/jdocmanual?article=help/weblinks/weblinks-links-edit"      => "weblinks-links-edit.html",
    "https://jdocmanual.org/pt/jdocmanual?article=help/weblinks/weblinks-categories"      => "weblinks-categories.html",
    "https://jdocmanual.org/pt/jdocmanual?article=help/weblinks/weblinks-options"         => "weblinks-options.html"
];

$cartella_img = "images";
if (!is_dir($cartella_img)) {
    mkdir($cartella_img, 0755, true);
}

echo "<h2>🚀 Inizio elaborazione batch...</h2>";

// Inizializziamo cURL una volta sola per performance
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

foreach ($pagine as $url => $nome_file) {
    echo "Sto elaborando: <strong>$nome_file</strong>... ";

    // 2. DOWNLOAD HTML
    curl_setopt($ch, CURLOPT_URL, $url);
    $html = curl_exec($ch);

    if (!$html) {
        echo "<span style='color:red;'>Errore nel download.</span><br>";
        continue;
    }

    // 3. PARSING DOM
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);

    $titolo = $xpath->query("//h1")->item(0);
    if (!$titolo) {
        echo "<span style='color:orange;'>H1 non trovato, salto.</span><br>";
        continue;
    }

    $nodo = $titolo->parentNode;
    if ($nodo->getAttribute('class') == 'page-header' || $nodo->nodeName == 'header') {
        $nodo = $nodo->parentNode;
    }

    // 4. TRATTAMENTO IMMAGINI (Sostituzione Picture -> Img)
    $pictures = $nodo->getElementsByTagName('picture');
    $targets = [];
    foreach ($pictures as $p) { $targets[] = $p; }

    foreach ($targets as $pic) {
        $imgInside = $pic->getElementsByTagName('img')->item(0);
        if ($imgInside) {
            $src = $imgInside->getAttribute('src');
            $alt = $imgInside->getAttribute('alt');
            
            $srcFull = (strpos($src, 'http') !== 0) ? "https://jdocmanual.org/" . ltrim($src, '/') : $src;
            $nomeImg = basename(parse_url($srcFull, PHP_URL_PATH));
            
            if ($nomeImg) {
                $percorsoLocale = $cartella_img . "/" . $nomeImg;
                
                // Scarica l'immagine solo se non esiste già
                if (!file_exists($percorsoLocale)) {
                    $datiImg = @file_get_contents($srcFull);
                    if ($datiImg) file_put_contents($percorsoLocale, $datiImg);
                }
                
                $newImg = $doc->createElement('img');
                $newImg->setAttribute('src', $percorsoLocale);
                $newImg->setAttribute('alt', $alt);
                $newImg->setAttribute('class', 'img-manuale border rounded');
                
                if ($pic->parentNode) {
                    $pic->parentNode->replaceChild($newImg, $pic);
                }
            }
        }
    }

    // 5. PULIZIA ELEMENTI
    $blacklist = [
        ".//div[@id='index-panel']",
        ".//div[contains(@class, 'col-sm-3')]",
        ".//div[contains(@class, 'offcanvas')]",
        ".//nav", ".//aside", ".//button", ".//footer",
        ".//div[contains(@class, 'container text-center')]",
        ".//div[contains(@class, 'document-title')]//button"
    ];

    foreach ($blacklist as $query) {
        $elementi = $xpath->query($query, $nodo);
        foreach ($elementi as $e) {
            $e->parentNode->removeChild($e);
        }
    }

    // 6. GESTIONE LINK
    $links = $nodo->getElementsByTagName('a');
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if ($href && strpos($href, 'http') !== 0 && strpos($href, '#') !== 0) {
            $link->setAttribute('href', "https://jdocmanual.org/" . ltrim($href, '/'));
            $link->setAttribute('target', '_blank');
        }
    }

    // 7. GENERAZIONE OUTPUT
    $corpo_html = $doc->saveHTML($nodo);

    $template = "
    <!DOCTYPE html>
    <html lang='it'>
    <head>
        <meta charset='UTF-8'>
        <title>Manuale - $nome_file</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background: #f8f9fa; padding: 30px; font-family: sans-serif; }
            .article-container { background: white; max-width: 1000px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .col-md-6, .col-lg-9, .col-12, section { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; padding: 0 !important; margin: 0 !important; }
            .img-manuale { max-width: 100%; height: auto; display: block; margin: 25px auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            h1 { color: #0d6efd; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class='article-container'>
            $corpo_html
        </div>
    </body>
    </html>";

    file_put_contents($nome_file, $template);
    echo "<span style='color:green;'>Fatto!</span><br>";
}

curl_close($ch);
echo "<h3>✅ Tutti i file sono stati generati con successo!</h3>";
?>
