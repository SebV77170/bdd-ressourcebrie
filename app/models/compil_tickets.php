<?php

// On part du principe que ce fichier est dans app/models/compil_tickets.php
// Racine projet = app/.. (../.. depuis ici)
require __DIR__ . '/../../config.php';
require __DIR__ . '/../../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

class compil_tickets {

    private string $date;
    private string $remoteFolder;
    private string $outputFile;

    public function __construct(string $date_compil)
    {
        // date_compil = "YYYY-MM-DD"
        $this->date = $date_compil;
        [$y, $m, $d] = explode('-', $date_compil);

        // Chemin WebDAV du dossier du jour
        $this->remoteFolder = rtrim(WEBDAV_TICKETS_BASEPATH, '/') . "/$y/$m/$d/";

        // Fichier de sortie côté serveur (comme avant)
        $this->outputFile = __DIR__ . '/../../tickets/compilations/compil_' . $date_compil . '.pdf';
    }

    /**
     * Liste les fichiers PDF présents dans le dossier WebDAV du jour.
     */
    private function listRemotePdfs(): array
{
    $url = rtrim(WEBDAV_BASE_URL, '/') . $this->remoteFolder;

    // Pour debug : tu peux décommenter ça pour vérifier l’URL appelée
    // error_log("PROPFIND sur : " . $url);
    error_log("PROPFIND sur URL : " . $url . " (user=" . WEBDAV_USER . ")");

    $xmlBody = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
    <d:displayname />
  </d:prop>
</d:propfind>
XML;

   $ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => 'PROPFIND',
    CURLOPT_USERPWD        => WEBDAV_USER . ':' . WEBDAV_PASS,
    CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
    CURLOPT_POSTFIELDS     => $xmlBody,
    CURLOPT_HTTPHEADER     => [
        'Depth: 1',
        'Content-Type: text/xml; charset="utf-8"'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
CURLOPT_SSL_VERIFYHOST => 2,
CURLOPT_CAINFO => __DIR__ . '/../../cacert.pem',

    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$errNo = curl_errno($ch);
$errMsg = curl_error($ch);
curl_close($ch);

error_log("PROPFIND HTTP code = $code ; cURL errno = $errNo ; msg = $errMsg");

if ($code >= 400 || $response === false) {
    error_log("PROPFIND a échoué pour $url");
    return [];
}

    // --- Parsing XML robuste avec SimpleXML + namespace DAV ---

    $xml = @simplexml_load_string($response);
    if ($xml === false) {
        // Pour debug éventuel :
        // file_put_contents('/tmp/last_webdav_response.xml', $response);
        return [];
    }

    // Le namespace DAV est en général "DAV:" ; on l’enregistre sous le préfixe "d"
    $xml->registerXPathNamespace('d', 'DAV:');

    // On récupère tous les <d:href> (chemins WebDAV)
    $nodes = $xml->xpath('//d:href');
    if ($nodes === false) {
        return [];
    }

    $files = [];

    foreach ($nodes as $node) {
        $href = (string)$node;

        // On récupère juste la partie chemin (au cas où l’URL est absolue)
        $path = parse_url($href, PHP_URL_PATH);
        if ($path === null) {
            $path = $href;
        }

        $basename = basename(urldecode($path));

        // On saute le dossier lui-même, qui apparaît aussi dans la liste
        if ($basename === '' || $basename === '.' || $basename === '..') {
            continue;
        }

        // On garde uniquement les .pdf (sans str_ends_with pour compat PHP 7)
        $lower = strtolower($basename);
        if (substr($lower, -4) === '.pdf') {
            $files[] = $basename;
        }
    }

    // On nettoie et on renvoie la liste unique
    $files = array_values(array_unique($files));

    // Pour debug : voir combien de PDF trouvés
    // error_log("WebDAV : " . count($files) . " PDF trouvés dans " . $this->remoteFolder);
    error_log("PDF trouvés dans " . $this->remoteFolder . " : " . count($files));

    return $files;
}


    /**
     * Télécharge un PDF WebDAV dans un fichier temporaire local.
     */
    private function downloadPdf(string $fileName): ?string
    {
        $remoteUrl = rtrim(WEBDAV_BASE_URL, '/') . $this->remoteFolder . $fileName;
        $tmp = tempnam(sys_get_temp_dir(), 'ticket_');

        if ($tmp === false) {
            return null;
        }

        $ch = curl_init($remoteUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => WEBDAV_USER . ':' . WEBDAV_PASS,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_SSL_VERIFYPEER => true,
CURLOPT_SSL_VERIFYHOST => 2,
CURLOPT_CAINFO => __DIR__ . '/../../cacert.pem',

    CURLOPT_TIMEOUT        => 15,
        ]);

        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400 || $data === false || $data === '') {
            @unlink($tmp);
            // error_log("DOWNLOAD FAIL $remoteUrl ($code)");
            return null;
        }

        file_put_contents($tmp, $data);
        return $tmp;
    }

    /**
     * Compile tous les tickets du jour en un seul PDF (2 tickets / page).
     */
    public function compile(): void
{
    $files = $this->listRemotePdfs();
    if (empty($files)) {
        throw new \RuntimeException("Aucun ticket PDF trouvé pour la date " . $this->date);
    }

    // Création du PDF de compilation
    $pdf = new Fpdi('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(false);

    // ----- PARAMÈTRES DE MISE EN PAGE -----
    $pageW = 210;   // largeur A4 en mm
    $pageH = 297;   // hauteur A4 en mm

    $marginX = 10;  // marge gauche/droite
    $marginY = 10;  // marge haut/bas

    // On veut 4 tickets par page : 2 colonnes x 2 lignes
    $cols = 2;
    $rows = 3;
    $slotsPerPage = $cols * $rows; // = 6

    // Espace intérieur utile (sans les marges)
    $innerW = $pageW - 2 * $marginX;
    $innerH = $pageH - 2 * $marginY;

    // Taille d'une "case" (slot) pour un ticket
    $cellW = $innerW / $cols;
    $cellH = $innerH / $rows;

    $index = 0; // compteur de slots utilisés

    foreach ($files as $fileName) {
        $tmpPdf = $this->downloadPdf($fileName);
        if ($tmpPdf === null) {
            continue;
        }

        $pageCount = $pdf->setSourceFile($tmpPdf);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

            // Si c'est le premier ticket de la page, on ajoute une nouvelle page
            if ($index % $slotsPerPage === 0) {
                $pdf->AddPage();
            }

            $tplId = $pdf->importPage($pageNo);
            $size  = $pdf->getTemplateSize($tplId); // $size['width'], $size['height']

            // Slot courant (0,1,2,3)
            $slotIndex = $index % $slotsPerPage;

            // Calcul de la ligne et de la colonne du slot
            $col = $slotIndex % $cols;           // 0 ou 1
            $row = intdiv($slotIndex, $cols);    // 0 ou 1

            // Coordonnées du coin haut-gauche de la "case"
            $slotX = $marginX + $col * $cellW;
            $slotY = $marginY + $row * $cellH;

            // On ajuste le ticket pour qu'il tienne dans cellW x cellH
            $maxW = $cellW;
            $maxH = $cellH;

            $scale = min(
                $maxW / $size['width'],
                $maxH / $size['height']
            );

            $w = $size['width'] * $scale;
            $h = $size['height'] * $scale;

            // On centre le ticket dans sa case
            $x = $slotX + ($cellW - $w) / 2;
            $y = $slotY + ($cellH - $h) / 2;

            $pdf->useTemplate($tplId, $x, $y, $w, $h);

            $index++;
        }

        @unlink($tmpPdf);
    }

    // Création du dossier de sortie si besoin
    $dir = dirname($this->outputFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Sauvegarde sur le serveur + envoi au navigateur
    $pdf->Output('F', $this->outputFile);
    $pdf->Output('D', basename($this->outputFile));
}

}
