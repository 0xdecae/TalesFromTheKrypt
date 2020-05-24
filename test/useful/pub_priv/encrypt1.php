<?php
// Data to be sent
$data = file_get_contents( getcwd() . '/test1.txt' );
echo 'Plain text: ' . $data;
// Compress the data to be sent
$plaintext = gzcompress($data);
 
// Get the public Key of the recipient
$publicKey = openssl_pkey_get_public('file://' . getcwd() . '/public.key' );
$pubkeyinfo = '';
openssl_pkey_export('file://' . getcwd() . '/public.key', $pubkeyinfo);
echo 'key: ' . $pubkeyinfo;
$a_key = openssl_pkey_get_details($publicKey);
 
// Encrypt the data in small chunks and then combine and send it.
$chunkSize = ceil($a_key['bits'] / 8) - 11;
$output = '';
 
while ($plaintext)
{
    $chunk = substr($data, 0, $chunkSize);
    $plaintext = substr($data, $chunkSize);
    $encrypted = '';
    if (!openssl_public_encrypt($chunk, $encrypted, $publicKey))
    {
        die('Failed to encrypt data');
    }
    $output .= $encrypted;
}
openssl_free_key($publicKey);
 
// This is the final encrypted data to be sent to the recipient
$encrypted = $output;

echo 'Output: ' . $output;
