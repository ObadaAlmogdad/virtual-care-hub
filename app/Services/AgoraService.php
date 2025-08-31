<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Yasser\Agora\RtcTokenBuilder;
use Yasser\Agora\DynamicKey5;

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
     * Generate RTC token using the yasserbelhimer/agora-access-token-generator package
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
            
            // Use the installed package
            $role = RtcTokenBuilder::RolePublisher; // RolePublisher = 1
            
            $rtcToken = RtcTokenBuilder::buildTokenWithUid(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $uid,
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
     * Generate RTM token for real-time messaging
     */
    public function generateRtmToken(string $userAccount, int $expireSeconds): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('Agora not configured properly');
            return null;
        }

        try {
            $currentTimestamp = time();
            $privilegeExpiredTs = $currentTimestamp + $expireSeconds;
            
            $rtmToken = \Yasser\Agora\RtmTokenBuilder::buildToken(
                $this->appId,
                $this->appCertificate,
                $userAccount,
                \Yasser\Agora\RtmTokenBuilder::RoleRtmUser,
                $privilegeExpiredTs
            );
            
            if ($rtmToken) {
                Log::info("RTM token generated successfully for user: {$userAccount}");
                return $rtmToken;
            }
            
            return null;
            
        } catch (\Throwable $e) {
            Log::error('Agora RTM token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a token for a specific role (attendee, publisher, subscriber, admin)
     */
    public function generateRtcTokenWithRole(string $channelName, int $uid, int $role, int $expireSeconds): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('Agora not configured properly');
            return null;
        }

        try {
            $currentTimestamp = time();
            $privilegeExpiredTs = $currentTimestamp + $expireSeconds;
            
            $rtcToken = RtcTokenBuilder::buildTokenWithUid(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $uid,
                $role,
                $privilegeExpiredTs
            );
            
            if ($rtcToken) {
                Log::info("RTC token generated successfully for channel: {$channelName}, uid: {$uid}, role: {$role}");
                return $rtcToken;
            }
            
            return null;
            
        } catch (\Throwable $e) {
            Log::error('Agora token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a token for a user account (string) instead of numeric UID
     */
    public function generateRtcTokenWithUserAccount(string $channelName, string $userAccount, int $role, int $expireSeconds): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('Agora not configured properly');
            return null;
        }

        try {
            $currentTimestamp = time();
            $privilegeExpiredTs = $currentTimestamp + $expireSeconds;
            
            $rtcToken = RtcTokenBuilder::buildTokenWithUserAccount(
                $this->appId,
                $this->appCertificate,
                $channelName,
                $userAccount,
                $role,
                $privilegeExpiredTs
            );
            
            if ($rtcToken) {
                Log::info("RTC token generated successfully for channel: {$channelName}, user: {$userAccount}, role: {$role}");
                return $rtcToken;
            }
            
            return null;
            
        } catch (\Throwable $e) {
            Log::error('Agora token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available roles for RTC tokens
     */
    public function getAvailableRoles(): array
    {
        return [
            'attendee' => RtcTokenBuilder::RoleAttendee,
            'publisher' => RtcTokenBuilder::RolePublisher,
            'subscriber' => RtcTokenBuilder::RoleSubscriber,
            'admin' => RtcTokenBuilder::RoleAdmin,
        ];
    }

    /**
     * Get the Agora App ID
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Check if a token is expired
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            // Extract timestamp from token (this is a simplified check)
            // In production, you might want to decode and verify the token properly
            $parts = explode('.', $token);
            if (count($parts) >= 2) {
                $payload = base64_decode($parts[1]);
                $data = json_decode($payload, true);
                
                if (isset($data['exp'])) {
                    return time() > $data['exp'];
                }
            }
            
            return false;
        } catch (\Throwable $e) {
            Log::error('Token expiration check failed: ' . $e->getMessage());
            return true; // Assume expired if we can't check
        }
    }
}


