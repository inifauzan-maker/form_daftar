# Social Media Integration Setup Guide

## Overview
This guide explains how to set up Instagram and TikTok API integrations for the Content Marketing Management System.

## Prerequisites
- Laravel application running
- Valid Instagram Business/Creator account
- TikTok Business account
- SSL certificate (HTTPS required for OAuth callbacks)

## Instagram Integration Setup

### 1. Create Facebook App
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Click "My Apps" → "Create App"
3. Select "Business" as app type
4. Fill in app details and create

### 2. Add Instagram Basic Display Product
1. In your Facebook app dashboard
2. Go to "Add Products"
3. Find "Instagram Basic Display" and click "Set Up"
4. Click "Create New App" in Instagram Basic Display settings

### 3. Configure Instagram Basic Display
1. Go to Instagram Basic Display → Basic Display
2. Add OAuth Redirect URIs:
   ```
   https://yourdomain.com/social/instagram/callback
   ```
3. Add Deauthorize Callback URL:
   ```
   https://yourdomain.com/social/instagram/deauthorize
   ```
4. Add Data Deletion Request URL:
   ```
   https://yourdomain.com/social/instagram/delete
   ```

### 4. Get App Credentials
1. In Instagram Basic Display → Basic Display
2. Copy "Instagram App ID" and "Instagram App Secret"
3. Add these to your `.env` file:
   ```env
   INSTAGRAM_APP_ID=your_instagram_app_id
   INSTAGRAM_APP_SECRET=your_instagram_app_secret
   ```

### 5. Test Users (Development)
1. Go to Instagram Basic Display → Basic Display
2. Add Instagram Testers
3. Accept the invitation in Instagram app

## TikTok Integration Setup

### 1. Create TikTok Developer Account
1. Go to [TikTok Developers](https://developers.tiktok.com/)
2. Sign up with your TikTok Business account
3. Complete verification process

### 2. Create TikTok App
1. Go to "Manage Apps" → "Create an app"
2. Fill in app information:
   - App name: Your Content Management System
   - Category: Social Media
   - Description: Content marketing management platform

### 3. Configure OAuth Settings
1. In your TikTok app dashboard
2. Go to "Login Kit" settings
3. Add Redirect URI:
   ```
   https://yourdomain.com/social/tiktok/callback
   ```
4. Select required scopes:
   - `user.info.basic`
   - `video.publish`

### 4. Get App Credentials
1. Copy "Client Key" and "Client Secret"
2. Add these to your `.env` file:
   ```env
   TIKTOK_CLIENT_KEY=your_client_key
   TIKTOK_CLIENT_SECRET=your_client_secret
   ```

## Environment Configuration

### Complete .env Setup
Copy `.env.example` to `.env` and configure:

```env
# Instagram API Configuration
INSTAGRAM_ACCESS_TOKEN=
INSTAGRAM_USER_ID=
INSTAGRAM_APP_ID=your_instagram_app_id
INSTAGRAM_APP_SECRET=your_instagram_app_secret

# TikTok API Configuration
TIKTOK_ACCESS_TOKEN=
TIKTOK_CLIENT_KEY=your_tiktok_client_key
TIKTOK_CLIENT_SECRET=your_tiktok_client_secret
```

**Note:** `INSTAGRAM_ACCESS_TOKEN`, `INSTAGRAM_USER_ID`, and `TIKTOK_ACCESS_TOKEN` will be automatically filled when users connect their accounts through the OAuth flow.

## Testing the Integration

### 1. Start Your Application
```bash
php artisan serve
```

### 2. Access Social Media Integration
1. Navigate to `/social-integration` in your browser
2. Click "Connect Instagram Account" or "Connect TikTok Account"
3. Complete OAuth authorization
4. Verify account connection appears

### 3. Test Content Posting
1. Go to Content Calendar
2. Create content with media upload
3. Use the posting functionality to share to connected accounts

## Troubleshooting

### Common Issues

#### Instagram Issues
- **Invalid Redirect URI**: Ensure your redirect URI in Facebook app matches exactly
- **Invalid Scopes**: Make sure you're requesting appropriate permissions
- **SSL Required**: Instagram requires HTTPS for production callbacks

#### TikTok Issues
- **Client Key Invalid**: Verify your Client Key is correct and app is approved
- **Scope Denied**: Ensure your app has proper permissions for video publishing
- **Rate Limiting**: TikTok has strict rate limits, implement proper error handling

### Error Handling
The application includes comprehensive error handling for:
- OAuth failures
- API rate limiting
- Network timeouts
- Invalid credentials

### Development vs Production
- **Development**: Use test users and sandbox environments
- **Production**: Submit apps for review and approval before public use

## Security Considerations

1. **Environment Variables**: Never commit `.env` file to version control
2. **HTTPS**: Always use HTTPS in production for OAuth callbacks
3. **Token Storage**: Access tokens are stored securely in encrypted sessions
4. **Error Logging**: Monitor logs for unauthorized access attempts

## Support

For additional help:
- Instagram: [Instagram Basic Display API Documentation](https://developers.facebook.com/docs/instagram-basic-display-api)
- TikTok: [TikTok Developer Documentation](https://developers.tiktok.com/doc/)
- Laravel: [OAuth Implementation Guide](https://laravel.com/docs/socialite)