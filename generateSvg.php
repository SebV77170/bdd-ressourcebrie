<?php
$directory = 'C:\Users\sebas\OneDrive\Documentos\sites\bdd-ressource-brie';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$links = [];
$ignoreFile = 'C:\Users\sebas\OneDrive\Documentos\sites\bdd-ressource-brie\actions\db.php';

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        preg_match_all('/\b(include|require|include_once|require_once)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*;/', $content, $matches, PREG_SET_ORDER);
        preg_match_all('/header\s*\(\s*[\'"]Location:\s*([^\'"]+)[\'"]\s*\)\s*;/', $content, $headerMatches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $linkedFile = realpath(dirname($file->getPathname()) . DIRECTORY_SEPARATOR . $match[2]);
            if ($linkedFile !== realpath($ignoreFile)) {
                $links[$file->getPathname()][] = $match[2];
            }
        }
        foreach ($headerMatches as $headerMatch) {
            $links[$file->getPathname()][] = $headerMatch[1];
        }
    }
}

$dotFileContent = "digraph G {\n";
foreach ($links as $file => $linkedFiles) {
    foreach ($linkedFiles as $linkedFile) {
        $dotFileContent .= "  \"" . addslashes($file) . "\" -> \"" . addslashes($linkedFile) . "\";\n";
    }
}
$dotFileContent .= "}\n";

file_put_contents('links.dot', $dotFileContent);

echo "DOT file generated: links.dot\n";


