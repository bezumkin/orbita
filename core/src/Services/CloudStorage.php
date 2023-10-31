<?php

namespace App\Services;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\FilesystemAdapter;
use Psr\Http\Message\StreamInterface;
use Vesp\Services\Filesystem;

class CloudStorage extends Filesystem
{
    protected S3Client $client;

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

        $this->client = new S3Client($params);

        return new AwsS3V3Adapter($this->client, getenv('S3_BUCKET'));
    }

    protected function getRoot(): string
    {
        return '/';
    }

    public function readRangeStream(string $path, int $start, int $end): StreamInterface
    {
        $options = [
            'Bucket' => getenv('S3_BUCKET'),
            'Key' => $path,
            'Range' => "bytes=$start-$end",
        ];
        $command = $this->client->getCommand('GetObject', $options);

        return $this->client
            ->execute($command)
            ->get('Body');
    }
}
