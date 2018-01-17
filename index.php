<?php
ini_set('diplay_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';
use phpseclib\Net\SFTP;


$filename = 'cloudprint-test.pdf';
$key = 'az-test.pdf';
$downloadedFile = 'files/'.$key;

try {
    // Login SFTP
    $sftp = new SFTP(SFTP_HOST);
    $sftp->login(SFTP_USERNAME, SFTP_PASSWORD);

    $file = $sftp->get($filename, $downloadedFile);

    $s3 = Aws\S3\S3Client::factory(array(
            'region'  => 'eu-west-1',
            'version' => 'latest',
        )
    );

    $bucket = "boozt-test";

    if(!$s3->doesBucketExist($bucket)) {
        echo "Creating bucket named {$bucket}\n";
        $s3->createBucket(['Bucket' => $bucket]);
    }

    // Upload a file.
    $result = $s3->putObject(array(
            'Bucket'       => $bucket,
            'Key'          => $key,
            'SourceFile'   => $downloadedFile,
            'ContentType'  => 'text/plain',
            'ACL'          => 'public-read',
    ));

    return true;
} catch(Exception $e) {
    echo $e->getMessage();
    exit;
}
