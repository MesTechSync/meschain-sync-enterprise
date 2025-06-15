# Troubleshooting Guide

This guide provides solutions to common issues you might encounter when using MesChain-Sync.

## Table of Contents

1. [Installation Issues](#installation-issues)
2. [API Connection Problems](#api-connection-problems)
3. [Theme and Display Issues](#theme-and-display-issues)
4. [Announcement System Problems](#announcement-system-problems)
5. [User Settings Issues](#user-settings-issues)
6. [Log Analysis](#log-analysis)
7. [Common Error Codes](#common-error-codes)

## Installation Issues

### Module Not Appearing in Extensions List

**Problem**: After installation, MesChain-Sync doesn't appear in the Extensions list.

**Solution**:
1. Check that all files were uploaded correctly
2. Refresh the modifications cache (Extensions > Modifications > Refresh)
3. Clear the OpenCart cache (Dashboard > Settings (gear icon) > Refresh)
4. Check the error logs for permission issues

### Database Error During Installation

**Problem**: Database error messages appear during installation.

**Solution**:
1. Verify your database user has sufficient privileges
2. Check the database tables prefix matches your OpenCart configuration
3. Ensure your MySQL version meets the requirements
4. Check the error logs for specific SQL errors

## API Connection Problems

### Cannot Connect to Marketplace API

**Problem**: Error messages when trying to connect to marketplace APIs.

**Solution**:
1. Verify your API credentials are entered correctly
2. Check that your server's IP is whitelisted in the marketplace developer portal
3. Ensure your server has outbound connections enabled
4. Verify SSL certificates are valid and up-to-date

### API Timeout Errors

**Problem**: API requests time out or take too long.

**Solution**:
1. Increase PHP timeout limits in your server configuration
2. Check your server's internet connection
3. Verify the marketplace API service status
4. Try reducing the batch size for product/order synchronization

## Theme and Display Issues

### Theme Not Applying Correctly

**Problem**: The MesChain-Sync theme doesn't apply or displays incorrectly.

**Solution**:
1. Clear your browser cache
2. Check for JavaScript errors in the browser console
3. Verify the theme CSS file is accessible
4. Check for conflicts with other installed extensions

### Responsive Design Problems

**Problem**: The interface doesn't display correctly on mobile devices.

**Solution**:
1. Update to the latest version of MesChain-Sync
2. Check for custom theme modifications that might affect responsiveness
3. Verify your browser is up-to-date
4. Check for CSS conflicts with your OpenCart theme

## Announcement System Problems

### Announcements Not Displaying

**Problem**: Created announcements don't appear for users.

**Solution**:
1. Verify the announcement is set to active
2. Check that the start date is not in the future
3. Ensure the expiration date has not passed
4. Confirm that the user's role is included in the target roles

### Announcement Attachments Not Working

**Problem**: Users cannot download or access announcement attachments.

**Solution**:
1. Check file permissions on the attachments directory
2. Verify the attachment was uploaded successfully
3. Ensure the file type is allowed
4. Check for path issues in your OpenCart configuration

## User Settings Issues

### User Settings Not Saving

**Problem**: User preferences and settings don't save or persist.

**Solution**:
1. Check database permissions
2. Verify the user has sufficient privileges
3. Clear browser cookies and cache
4. Check for JavaScript errors in the browser console

### Theme Selection Not Working

**Problem**: User cannot change or select themes.

**Solution**:
1. Verify the theme files exist and are accessible
2. Check user permissions for theme selection
3. Clear the theme cache
4. Ensure the theme is marked as active in the database

## Log Analysis

MesChain-Sync maintains several log files to help diagnose issues:

### Log Locations

- **Main Log**: `system/storage/logs/meschain.log`
- **Announcement Log**: `system/storage/logs/announcement.log`
- **User Settings Log**: `system/storage/logs/user_settings.log`
- **Trendyol API Log**: `system/storage/logs/trendyol_controller.log`
- **Error Log**: `system/storage/logs/error.log`

### Log Format

All logs follow this standard format:
```
[YYYY-MM-DD HH:MM:SS] [USERNAME/ROLE] [ACTION] [MESSAGE]
```

### Common Log Entries

Here are some common log entries and what they mean:

#### Installation Logs
```
[2023-11-15 10:15:22] [admin] [INSTALL] Module installation started
[2023-11-15 10:15:25] [admin] [INSTALL] Database tables created
[2023-11-15 10:15:26] [admin] [INSTALL] Module installation completed
```

#### Error Logs
```
[2023-11-15 14:22:33] [admin] [ERROR] API connection failed: Invalid credentials
[2023-11-15 15:45:12] [user] [ERROR] Permission denied: Cannot access settings
```

#### Action Logs
```
[2023-11-15 11:30:45] [admin] [ACTION] Created new announcement: "System Maintenance"
[2023-11-15 13:12:08] [user] [ACTION] Changed theme to "Default"
```

## Common Error Codes

### System Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| E001 | Database connection error | Check database credentials and connection |
| E002 | File permission error | Verify directory permissions (755 for folders, 644 for files) |
| E003 | Missing extension | Install required PHP extension |
| E004 | Cache error | Clear OpenCart and browser cache |

### API Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| A001 | Invalid API credentials | Check and update API keys |
| A002 | API rate limit exceeded | Reduce request frequency or increase delay between requests |
| A003 | API endpoint not found | Verify API URL and endpoint path |
| A004 | API timeout | Increase timeout settings or check network connection |

### User Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| U001 | Permission denied | Check user role and permissions |
| U002 | Invalid user settings | Reset user preferences |
| U003 | Theme not found | Verify theme exists and is active |
| U004 | Session expired | Log in again |

If you encounter an issue not covered in this guide, please contact our support team with your log files and a detailed description of the problem. 