<?php
use PHPUnit\Framework\TestCase;
use Exception;

class AeadAuthenticationException extends Exception {}

class CryptographicLifecycleTest extends TestCase {
    
    private function encryptGCM(string $plaintext, string $key): string {
        $iv = random_bytes(12);
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    private function decryptGCM(string $payload, string $key): string {
        $decoded = base64_decode($payload);
        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $ciphertext = substr($decoded, 28);
        
        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($decrypted === false) {
            throw new AeadAuthenticationException("AEAD Authentication Tag Mismatch");
        }
        
        return $decrypted;
    }

    public function test_untampered_payload_decryption(): void {
        $key = random_bytes(32); // Simulated .env key
        $original = "DIAGNOSIS: Acute Type-2 Diabetes.";
        
        $encrypted = $this->encryptGCM($original, $key);
        $decrypted = $this->decryptGCM($encrypted, $key);
        
        $this->assertEquals($original, $decrypted);
    }

    public function test_tampered_payload_throws_aead_exception(): void {
        $key = random_bytes(32);
        $original = "DOSAGE: 10mg";
        
        $encrypted = $this->encryptGCM($original, $key);
        
        // Active Payload Manipulation: Corrupting the base64 string to simulate integrity breach
        $encrypted[strlen($encrypted) - 1] = 'X';
        
        $this->expectException(AeadAuthenticationException::class);
        $this->expectExceptionMessage("AEAD Authentication Tag Mismatch");
        
        $this->decryptGCM($encrypted, $key);
    }

    public function test_credential_hash_integrity_matches(): void {
        $mockplaintext = "testkey123";
        $hash = password_hash($mockplaintext, PASSWORD_ARGON2ID);
        
        $this->assertTrue(password_verify($mockplaintext, $hash));
    }
}


