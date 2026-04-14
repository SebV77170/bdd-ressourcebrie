<?php

function getWebdavConfig(): array
{
    return [
        'base_url' => rtrim($_ENV['WEBDAV_BASE_URL'] ?? '', '/') . '/',
        'username' => $_ENV['WEBDAV_USERNAME'] ?? '',
        'password' => $_ENV['WEBDAV_PASSWORD'] ?? '',
    ];
}

function webdavRequest(string $method, string $url, ?string $body = null, array $headers = []): array
{
    $config = getWebdavConfig();

    if (empty($config['base_url']) || empty($config['username']) || empty($config['password'])) {
        throw new RuntimeException('Configuration WebDAV incomplète.');
    }

    $ch = curl_init($url);

    $defaultHeaders = array_merge([
        'Authorization: Basic ' . base64_encode($config['username'] . ':' . $config['password']),
    ], $headers);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $defaultHeaders,
        CURLOPT_HEADER         => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT        => 30,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Erreur cURL : ' . $error);
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    $rawHeaders = substr($response, 0, $headerSize);
    $rawBody    = substr($response, $headerSize);

    curl_close($ch);

    return [
        'status'  => $statusCode,
        'headers' => $rawHeaders,
        'body'    => $rawBody,
    ];
}

function webdavDownloadFile(string $remotePath): array
{
    $config = getWebdavConfig();

    $remotePath = ltrim($remotePath, '/');
    $url = $config['base_url'] . str_replace('%2F', '/', rawurlencode($remotePath));

    $response = webdavRequest('GET', $url);

    if ($response['status'] !== 200) {
        throw new RuntimeException('Échec téléchargement WebDAV. Code HTTP : ' . $response['status']);
    }

    return $response;
}