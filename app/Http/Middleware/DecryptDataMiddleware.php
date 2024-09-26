<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecryptDataMiddleware
{

    protected $privateKey;
    public function __construct()
    {
        $this->privateKey =file_get_contents(public_path('PrivetKey.txt'));
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next)
     {
         if ($request->has('encrypted_data')) {
             $encryptedHex = $request->encrypted_data;
 
             // Validate that the encrypted_data is a valid hex string
             if (!ctype_xdigit($encryptedHex)) {
                 return response()->json(['error' => 'Invalid hexadecimal string'], 400);
             }
 
             // Convert the hex string to binary
             $encryptedBinary = hex2bin($encryptedHex);
 
             // Decrypt the data using the private key
             $decryptedData = $this->privateDecrypt2048($encryptedBinary, $this->privateKey);
 
             if ($decryptedData) {
                 // Merge decrypted data into the request
                 $request->merge($decryptedData);
             } else {
                 return response()->json(['error' => 'Decryption failed'], 400);
             }
         }
 
         return $next($request);
     }
 
     protected function privateDecrypt2048($encrypted = '', $privateKey)
     {
         if (!is_string($encrypted)) {
             return false;
         }
 
         extension_loaded('openssl') or die('PHP requires OpenSSL extension support');
 
         $private_key = "-----BEGIN PRIVATE KEY-----\n" . $privateKey . "\n-----END PRIVATE KEY-----";
         $key = openssl_pkey_get_private($private_key);
 
         $decrypted = "";
         $enArray = str_split($encrypted, 2048 / 8);
 
         foreach ($enArray as $va) {
             $decryptedTemp = "";
             $ciphertext = $va;
 
             $return_de = openssl_private_decrypt($ciphertext, $decryptedTemp, $key, OPENSSL_PKCS1_PADDING);
             if (!$return_de) {
                 return false;
             }
             $decrypted .= $decryptedTemp;
         }
 
         return json_decode($decrypted, true);
     }
 }