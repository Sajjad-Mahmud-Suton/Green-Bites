# 🔒 Green Bites Security Documentation

## Security Features Implemented

### 1. Authentication Security

#### Brute Force Protection
- **Max 5 failed login attempts** before 15-minute lockout
- **Rate limiting**: 10 login attempts per minute per IP
- Failed attempts are logged with IP and timestamp
- Automatic lockout release after timeout

#### Session Security
- **Secure session cookies** (HttpOnly, SameSite=Strict)
- **Session timeout**: 30 minutes of inactivity
- **Session regeneration** on login (prevents session fixation)
- **IP consistency check** (session invalidated if IP changes)
- Session hijacking detection and logging

#### Password Security
- **Bcrypt hashing** (PASSWORD_BCRYPT)
- Password requirements:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
  - At least one special character (recommended)

### 2. CSRF Protection
- All forms require CSRF token
- Tokens are validated using `hash_equals()` (timing-safe comparison)
- Tokens expire after 1 hour
- Token regeneration on each session

### 3. SQL Injection Prevention
- **All queries use prepared statements**
- `mysqli_prepare()` and `mysqli_stmt_bind_param()` throughout
- No raw user input in SQL queries
- Strict SQL mode enabled

### 4. XSS Prevention
- Output encoding with `htmlspecialchars()`
- `ENT_QUOTES | ENT_HTML5` flags used
- UTF-8 encoding enforced
- Content-Type headers set properly

### 5. Security Headers
```
X-Frame-Options: DENY (prevents clickjacking)
X-Content-Type-Options: nosniff (prevents MIME sniffing)
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

### 6. File Upload Security
- MIME type validation using `finfo` (not trusting client)
- File size limits (5MB max)
- Random filename generation (prevents path traversal)
- PHP code detection in uploaded files
- Image dimension validation
- `.htaccess` blocks PHP execution in uploads folder

### 7. Directory Protection
| Directory | Protection |
|-----------|------------|
| `/config/` | Blocks all direct access |
| `/logs/` | Blocks all direct access |
| `/uploads/` | Blocks PHP execution, only allows images |
| `/database/` | Should be blocked in production |

### 8. Rate Limiting
- **API endpoints**: 60 requests/minute
- **Login attempts**: 10/minute
- **Complaints**: 5/hour
- File-based rate limiting (no database required)

### 9. Logging & Audit
- Security events logged to `logs/security/`
- Login attempts tracked (success/failure)
- File uploads logged
- Session hijack attempts logged
- Log rotation by date

---

## File Structure

```
config/
├── security.php     # Main security configuration
├── email.php        # Email settings
├── .htaccess        # Block direct access

logs/
├── security/        # Security event logs
├── rate_limits/     # Rate limiting data
├── login_attempts/  # Failed login tracking
├── .htaccess        # Block all access

uploads/
├── complaints/      # Complaint images
├── .htaccess        # Block PHP, allow images only
```

---

## Security Checklist for Production

### Before Deployment
- [ ] Set `PRODUCTION_MODE = true` in `config/security.php`
- [ ] Change default database password
- [ ] Use HTTPS (enable HSTS header)
- [ ] Set secure database credentials
- [ ] Remove test files (`test.php`, `add_bill_number.php`)
- [ ] Enable error logging to file (not display)
- [ ] Block `/database/` folder access

### Regular Maintenance
- [ ] Review security logs weekly
- [ ] Clear old rate limit files monthly
- [ ] Update PHP and MySQL regularly
- [ ] Backup database regularly
- [ ] Monitor for unusual activity

### Environment Variables (Recommended)
```php
// In production, use environment variables:
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'green_bites');
```

---

## Security Functions Reference

### `config/security.php` Functions

| Function | Purpose |
|----------|---------|
| `initSecurity()` | Initialize all security features |
| `initSecureSession()` | Start secure session with proper settings |
| `setSecurityHeaders()` | Set all security HTTP headers |
| `generateCSRFToken()` | Generate or return CSRF token |
| `validateCSRFToken($token)` | Validate CSRF token |
| `sanitizeInput($data, $type)` | Sanitize user input |
| `validateEmail($email)` | Validate email format |
| `validatePassword($password)` | Check password strength |
| `checkRateLimit($id, $max, $window)` | Rate limiting |
| `recordLoginAttempt($ip, $success)` | Track login attempts |
| `isLoginLocked($ip)` | Check if IP is locked |
| `validateFileUpload($file, $types, $size)` | Secure file upload validation |
| `generateSecureFilename($name)` | Generate random filename |
| `securityLog($type, $message, $data)` | Log security events |
| `getClientIP()` | Get real client IP |
| `blockIP($ip, $duration)` | Block an IP address |

---

## Common Attack Protections

| Attack Type | Protection |
|-------------|------------|
| SQL Injection | Prepared statements |
| XSS | Output encoding, CSP headers |
| CSRF | Token validation |
| Session Hijacking | IP check, session regeneration |
| Brute Force | Rate limiting, lockouts |
| Clickjacking | X-Frame-Options header |
| File Upload Attacks | MIME validation, PHP detection |
| Directory Traversal | Random filenames, path validation |

---

## Emergency Response

### If Suspicious Activity Detected
1. Check `logs/security/` for details
2. Block suspicious IPs: `blockIP('x.x.x.x', 86400)`
3. Force logout all sessions if needed
4. Review and change admin passwords

### If Breach Suspected
1. Take site offline temporarily
2. Change all database credentials
3. Invalidate all sessions
4. Review logs for scope
5. Reset affected user passwords
6. Enable enhanced logging

---

**Last Updated**: December 20, 2025
