<?php
/**
 * Esportatore JDocManual - Versione "Clean Image & Full Width"
 * Sostituisce i tag <picture> con <img> locali e pulisce il layout
 */

// 1. CONFIGURAZIONE
$url = "https://jdocmanual.org/en/jdocmanual?article=help/weblinks/weblinks-options";
$nome_file = "weblinks-options.html";
$cartella_img = "img_manuale";

if (!is_dir($cartella_img)) {
    mkdir($cartella_img, 0755, true);
}

// 2. DOWNLOAD HTML
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch);
curl_close($ch);

if (!$html) die("Errore nel download della pagina.");

// 3. PARSING DOM
$doc = new DOMDocument();
libxml_use_internal_errors(true);
@$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
libxml_clear_errors();
$xpath = new DOMXPath($doc);

// Cerchiamo l'elemento principale (il tag <main> o il contenitore dell'H1)
$titolo = $xpath->query("//h1")->item(0);
if (!$titolo) die("Contenuto non trovato.");
$nodo = $titolo->parentNode;
// Risaliamo se necessario per prendere tutto l'articolo
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
        
        // Gestione URL per il download
        $srcFull = (strpos($src, 'http') !== 0) ? "https://jdocmanual.org/" . ltrim($src, '/') : $src;
        $nomeImg = basename(parse_url($srcFull, PHP_URL_PATH));
        
        if ($nomeImg) {
            $percorsoLocale = $cartella_img . "/" . $nomeImg;
            $datiImg = @file_get_contents($srcFull);
            
            if ($datiImg) {
                file_put_contents($percorsoLocale, $datiImg);
                
                // Creiamo un nuovo elemento IMG pulito da zero
                $newImg = $doc->createElement('img');
                $newImg->setAttribute('src', $percorsoLocale);
                $newImg->setAttribute('alt', $alt);
                $newImg->setAttribute('class', 'img-manuale border rounded');
                
                // Sostituiamo l'intero tag <picture> (e i suoi <source>) con il nostro <img>
                $pic->parentNode->replaceChild($newImg, $pic);
            }
        }
    }
}

// 5. PULIZIA ELEMENTI DI NAVIGAZIONE E MENU
// Rimuoviamo tutto ciò che non è il testo dell'articolo
$blacklist = [
    ".//div[@id='index-panel']",         // Menu laterale sinistro
    ".//div[contains(@class, 'col-sm-3')]", // Altra possibile sidebar
    ".//div[contains(@class, 'offcanvas')]", // Menu mobile
    ".//nav", ".//aside", ".//button", ".//footer",
    ".//div[contains(@class, 'container text-center')]", // Bottoni Next/Prev
    ".//div[contains(@class, 'document-title')]//button" // Bottone "Index" mobile
];

foreach ($blacklist as $query) {
    $elementi = $xpath->query($query, $nodo);
    foreach ($elementi as $e) {
        $e->parentNode->removeChild($e);
    }
}

// 6. GESTIONE LINK (Lasciamo quelli originali, ma li rendiamo assoluti se necessario)
$links = $nodo->getElementsByTagName('a');
foreach ($links as $link) {
    $href = $link->getAttribute('href');
    if ($href && strpos($href, 'http') !== 0 && strpos($href, '#') !== 0) {
        $link->setAttribute('href', "https://jdocmanual.org/" . ltrim($href, '/'));
        $link->setAttribute('target', '_blank'); // Apri in nuova scheda
    }
}

// 7. GENERAZIONE OUTPUT
$corpo_html = $doc->saveHTML($nodo);

$template = "
<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='UTF-8'>
    <title>Web Links Manual - Joomla</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; padding: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .article-container { 
            background: white; 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        /* Forza il contenuto a 100% resettando le classi Bootstrap originali */
        .col-md-6, .col-lg-9, .col-12, section { 
            width: 100% !important; 
            max-width: 100% !important; 
            flex: 0 0 100% !important; 
            padding: 0 !important;
            margin: 0 !important;
        }
        .row { margin: 0 !important; }
        .img-manuale { 
            max-width: 100%; 
            height: auto; 
            display: block; 
            margin: 25px auto; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 { color: #0d6efd; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { margin-top: 30px; color: #333; }
        a { color: #0d6efd; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='article-container'>
        $corpo_html
    </div>
</body>
</html>";

file_put_contents($nome_file, $template);
echo "<h3>✅ Operazione completata!</h3>";
echo "Il file è pronto: <a href='$nome_file' target='_blank'>$nome_file</a>";
?>