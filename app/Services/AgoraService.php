<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AgoraService
{
    private string $appId;
    private string $appCertificate;

    public function __construct()
    {
        $this->appId = (string) config('services.agora.app_id');
        $this->appCertificate = (string) config('services.agora.app_certificate');
    }

    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->appCertificate);
    }

    /**
     * Generate RTC token using internal implementation
     */
    public function generateRtcToken(string $channelName, int $uid, int $expireSeconds): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('Agora not configured properly');
            return null;
        }

        try {
            $expireAt = time() + $expireSeconds;
            $role = 1; // PUBLISHER role
            
            // Build token payload
            $token = $this->buildToken($channelName, $uid, $role, $expireAt);
            
            if ($token) {
                Log::info("Agora token generated successfully for channel: {$channelName}, uid: {$uid}");
                return $token;
            }
            
            return null;
        } catch (\Throwable $e) {
            Log::error('Agora token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build Agora RTC token using correct algorithm
     */
    private function buildToken(string $channelName, int $uid, int $role, int $expireAt): string
    {
        // Agora token version 006
        $version = '006';
        
        // Build message string (without signature)
        $message = $version . $this->appId . $channelName . $uid . $role . $expireAt;
        
        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $message, $this->appCertificate, true);
        
        // Encode signature to base64
        $signatureBase64 = base64_encode($signature);
        
        // Build final token: message + signature
        $token = $message . $signatureBase64;
        
        return $token;
    }
}


