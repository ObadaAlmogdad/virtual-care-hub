<?php

namespace Tests\Unit;

use App\Services\AgoraService;
use Tests\TestCase;

class AgoraServiceTest extends TestCase
{
    public function test_agora_service_can_generate_rtc_token()
    {
        $agoraService = new AgoraService();
        
        // Test if the service is configured
        $this->assertTrue($agoraService->isConfigured());
        
        // Test token generation
        $token = $agoraService->generateRtcToken('test-channel', 123, 3600);
        
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_agora_service_can_generate_rtm_token()
    {
        $agoraService = new AgoraService();
        
        // Test RTM token generation
        $token = $agoraService->generateRtmToken('test-user', 3600);
        
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_agora_service_can_generate_rtc_token_with_role()
    {
        $agoraService = new AgoraService();
        
        // Test token generation with specific role
        $token = $agoraService->generateRtcTokenWithRole('test-channel', 123, 0, 3600); // RoleAttendee
        
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_agora_service_can_generate_rtc_token_with_user_account()
    {
        $agoraService = new AgoraService();
        
        // Test token generation with user account
        $token = $agoraService->generateRtcTokenWithUserAccount('test-channel', 'test-user', 1, 3600); // RolePublisher
        
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_agora_service_returns_available_roles()
    {
        $agoraService = new AgoraService();
        
        $roles = $agoraService->getAvailableRoles();
        
        $this->assertIsArray($roles);
        $this->assertArrayHasKey('attendee', $roles);
        $this->assertArrayHasKey('publisher', $roles);
        $this->assertArrayHasKey('subscriber', $roles);
        $this->assertArrayHasKey('admin', $roles);
    }

    public function test_agora_service_returns_app_id()
    {
        $agoraService = new AgoraService();
        
        $appId = $agoraService->getAppId();
        
        $this->assertIsString($appId);
        $this->assertNotEmpty($appId);
    }
}
