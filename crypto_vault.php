<?php
// crypto_vault.php - Refactored Secure Patient Medical Records Protection
require_once 'db_config.php'; // Ensure DB and Env are loaded

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_payload = $_POST['payload'] ?? '';
    
    // 1. Solves Hidden Flaw G (Hardcoded Key):
    // We now retrieve the key from the secure external .env configuration
    // Note: In a real app, a package like vlucas/phpdotenv would load this.
    $secret_key = base64_decode($_ENV['MEDVAULT_MASTER_KEY'] ?? '');
    
    if (empty($secret_key) || strlen($secret_key) !== 32) {
        die(json_encode(["error" => "Cryptographic environment failure."]));
    }
    
    // 2. Solves Hidden Flaw F (ECB Mode Leakage):
    // Upgrading to AES-256-GCM requires a dynamic 12-byte IV for every single execution
    $iv = random_bytes(12);
    
    // 3. Authenticated Encryption with Associated Data (AEAD)
    // The $tag parameter is passed by reference and populated by the engine
    $ciphertext = openssl_encrypt(
        $medical_payload,
        'aes-256-gcm',
        $secret_key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    // 4. Strict Data-Flow Serialization (As diagrammed in Chapter 3)
    // Concatenating: [ 12-byte IV ] + [ 16-byte Tag ] + [ Ciphertext ]
    $serialized_payload = base64_encode($iv . $tag . $ciphertext);
    
    // Return the vaulted payload safely to the client
    echo json_encode(["status" => "vaulted", "data" => $serialized_payload]);
}
