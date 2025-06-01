<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class SpacesHelper {
    private $client;
    private $config;

    public function __construct() {
        $this->config = require 'spaces-config.php';

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $this->config['region'],
            'endpoint' => $this->config['endpoint'],
            'credentials' => [
                'key' => $this->config['access_key'],
                'secret' => $this->config['secret_key'],
            ],
            'use_path_style_endpoint' => false,
        ]);
    }

    public function uploadFile($filePath, $fileName = null, $folder = '') {
        if ($fileName === null) {
            $fileName = basename($filePath);
        }

        try {
            // Generate a unique file name to avoid overwriting
            $uniqueFileName = time() . '_' . $fileName;

            // Add folder prefix if specified
            $objectKey = empty($folder) ? $uniqueFileName : trim($folder, '/') . '/' . $uniqueFileName;

            // Upload the file
            $result = $this->client->putObject([
                'Bucket' => $this->config['bucket'],
                'Key' => $objectKey,
                'SourceFile' => $filePath,
                'ACL' => 'public-read', // Make file publicly accessible
            ]);

            // Return the URL to the uploaded file
            return [
                'success' => true,
                'url' => $this->getFileUrl($objectKey),
                'key' => $objectKey
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteFile($objectKey) {
        try {
            $result = $this->client->deleteObject([
                'Bucket' => $this->config['bucket'],
                'Key' => $objectKey,
            ]);

            return [
                'success' => true
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // In your SpacesHelper class
    public function downloadFile($spacesPath, $localPath) {
        $url = $this->getFileUrl($spacesPath);

        $ch = curl_init($url);
        $fp = fopen($localPath, 'wb');

        if ($fp === false) {
            throw new Exception("Failed to open local file for writing: $localPath");
        }

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only if needed

        $success = curl_exec($ch);

        if ($success === false) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);
        fclose($fp);

        return true;
    }

    public function getFileUrl($objectKey) {
        // If CDN is enabled, use the CDN URL
        if (isset($this->config['cdn_endpoint'])) {
            return $this->config['cdn_endpoint'] . '/' . $objectKey;
        }

        // Otherwise, use the regular Spaces URL
        return 'https://' . $this->config['bucket'] . '.' . $this->config['region'] . '.digitaloceanspaces.com/' . $objectKey;
    }
}