<?php
// Data to be sent
$plaintext = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eleifend vestibulum nunc sit amet mattis. Nulla at volutpat nulla. Pellentesque sodales vel ligula quis consequat. Suspendisse dapibus dolor nec viverra venenatis. Pellentesque blandit vehicula eleifend. Duis eget fermentum velit. Vivamus varius ut dui vel malesuada. Ut adipiscing est non magna posuere ullamcorper. Proin pretium nibh nec elementum tincidunt. Vestibulum leo urna, porttitor et aliquet id, ornare at nibh. Maecenas placerat justo nunc, varius condimentum diam fringilla sed. Donec auctor tellus vitae justo venenatis, sit amet vulputate felis accumsan. Aenean aliquet bibendum magna, ac adipiscing orci venenatis vitae.';
 
echo 'Plain text: ' . $plaintext;
// Compress the data to be sent
$plaintext = gzcompress($plaintext);
 
// Get the public Key of the recipient
$publicKey = openssl_pkey_get_public('file:///home/decae/Documents/sec/red/exploits/k4NC3r/pub_priv/public.key');
$a_key = openssl_pkey_get_details($publicKey);
 
// Encrypt the data in small chunks and then combine and send it.
$chunkSize = ceil($a_key['bits'] / 8) - 11;
$output = '';
 
while ($plaintext)
{
    $chunk = substr($plaintext, 0, $chunkSize);
    $plaintext = substr($plaintext, $chunkSize);
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
