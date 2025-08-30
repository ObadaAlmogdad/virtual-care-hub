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
     * Generate RTC token using the same logic as yasserbelhimer/agora-access-token-generator
     */
    public function generateRtcToken(string $channelName, int $uid, int $expireSeconds): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('Agora not configured properly');
            return null;
        }

        try {
            $currentTimestamp = time();
            $privilegeExpiredTs = $currentTimestamp + $expireSeconds;
            
            // Use the same logic as the package
            $role = 1; // RolePublisher = 1
            $userAccount = (string) $uid;
            
            $rtcToken = $this->buildTokenWithUserAccount(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $userAccount,
                $role,
                $privilegeExpiredTs
            );
            
            if ($rtcToken) {
                Log::info("RTC token generated successfully for channel: {$channelName}, uid: {$uid}");
                return $rtcToken;
            }
            
            return null;
            
        } catch (\Throwable $e) {
            Log::error('Agora token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build token with user account (same as yasserbelhimer package)
     */
    private function buildTokenWithUserAccount(
        string $appID,
        string $appCertificate,
        string $channelName,
        string $userAccount,
        int $role,
        int $privilegeExpiredTs
    ): string {
        $token = $this->buildToken(
            $appID,
            $appCertificate,
            $channelName,
            $userAccount,
            $role,
            $privilegeExpiredTs
        );
        
        return $token;
    }

    /**
     * Build token (same as yasserbelhimer package)
     */
    private function buildToken(
        string $appID,
        string $appCertificate,
        string $channelName,
        string $userAccount,
        int $role,
        int $privilegeExpiredTs
    ): string {
        $version = '007';
        
        $message = $version . $channelName . $userAccount . $role . $privilegeExpiredTs;
        
        // Generate signature using HMAC-SHA256
        $signature = hash_hmac('sha256', $message, $appCertificate, true);
        $signatureBase64 = base64_encode($signature);
        
        // Build final token
        $token = $message . $signatureBase64;
        
        return $token;
    }
}


