# Agora Video Call Setup

This project is already configured with a complete Agora video call system using the `yasserbelhimer/agora-access-token-generator` package.

## Configuration

The Agora configuration is already set up in your `.env` file:

```env
AGORA_APP_ID=bf626d1e784342408bd16f82b1f11be4
AGORA_APP_CERTIFICATE=4d71e940c0924b90a5ba162625652fcf
AGORA_TOKEN_TTL=3600
```

## Available Services

### AgoraService

The main service class located at `app/Services/AgoraService.php` provides the following methods:

#### RTC Token Generation
- `generateRtcToken(string $channelName, int $uid, int $expireSeconds)` - Generate RTC token for video calls
- `generateRtcTokenWithRole(string $channelName, int $uid, int $role, int $expireSeconds)` - Generate token with specific role
- `generateRtcTokenWithUserAccount(string $channelName, string $userAccount, int $role, int $expireSeconds)` - Generate token with string user account

#### RTM Token Generation
- `generateRtmToken(string $userAccount, int $expireSeconds)` - Generate RTM token for real-time messaging

#### Utility Methods
- `getAvailableRoles()` - Get available RTC roles (attendee, publisher, subscriber, admin)
- `getAppId()` - Get the Agora App ID
- `isConfigured()` - Check if Agora is properly configured

## Available Roles

- `RoleAttendee` (0) - Basic participant
- `RolePublisher` (1) - Can publish audio/video streams
- `RoleSubscriber` (2) - Can only receive streams
- `RoleAdmin` (101) - Full administrative privileges

## API Endpoints

The following video call endpoints are already configured:

### Video Call Management
- `POST /api/video-calls/start` - Start a new video call
- `POST /api/video-calls/accept` - Accept an incoming call
- `POST /api/video-calls/decline` - Decline an incoming call
- `POST /api/video-calls/end` - End an active call

### Token Generation
- `GET /api/agora/token` - Get RTC token for a video call
- `POST /api/agora/token/renew` - Renew an expired token

## Usage Examples

### Basic Token Generation

```php
use App\Services\AgoraService;

$agoraService = new AgoraService();

// Generate RTC token for video call
$token = $agoraService->generateRtcToken('channel-123', 456, 3600);

// Generate RTM token for messaging
$rtmToken = $agoraService->generateRtmToken('user-123', 3600);
```

### Token with Specific Role

```php
// Generate token for an attendee (viewer only)
$token = $agoraService->generateRtcTokenWithRole('channel-123', 456, 0, 3600);

// Generate token for a publisher (can stream)
$token = $agoraService->generateRtcTokenWithRole('channel-123', 456, 1, 3600);
```

### Get Available Roles

```php
$roles = $agoraService->getAvailableRoles();
// Returns: ['attendee' => 0, 'publisher' => 1, 'subscriber' => 2, 'admin' => 101]
```

## Frontend Integration

To use these tokens in your frontend application:

1. Call the `/api/agora/token` endpoint to get a token
2. Use the token with Agora SDK to join the video call
3. The token includes the App ID and channel information

## Testing

Run the Agora service tests to verify everything is working:

```bash
php artisan test tests/Unit/AgoraServiceTest.php
```

## Security Notes

- Tokens are generated server-side and should never be exposed in client-side code
- Tokens have expiration times (default: 1 hour)
- Use HTTPS in production to secure token transmission
- Regularly rotate your Agora App Certificate

## Troubleshooting

If you encounter issues:

1. Verify your Agora credentials in `.env`
2. Check that the `yasserbelhimer/agora-access-token-generator` package is installed
3. Ensure your PHP version is compatible (PHP 8.1+)
4. Check the Laravel logs for any error messages

## Additional Resources

- [Agora Documentation](https://docs.agora.io/)
- [Laravel Documentation](https://laravel.com/docs)
- [Package Repository](https://github.com/yasserbelhimer/agora-access-token-generator)
