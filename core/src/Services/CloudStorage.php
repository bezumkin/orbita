<?php

namespace App\Services;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\FilesystemAdapter;
use Vesp\Services\Filesystem;

class CloudStorage extends Filesystem
{
    protected function getAdapter(): FilesystemAdapter
    {
        $params = [
            'endpoint' => getenv('S3_ENDPOINT'),
            'region' => getenv('S3_REGION'),
            'credentials' => [
                'key' => getenv('S3_KEY'),
                'secret' => getenv('S3_SECRET'),
            ],
        ];
        if (($options = getenv('S3_OPTIONS')) && $options = json_decode($options, true)) {
            $params = array_merge($options, $params);
        }

        $client = new S3Client($params);

        return new AwsS3V3Adapter($client, getenv('S3_BUCKET'));
    }

    protected function getRoot(): string
    {
        return '/';
    }
}
