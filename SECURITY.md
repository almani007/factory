# Brixel FactoryOS — Security Policy

## Supported Versions

| Version | Supported |
|---|---|
| 1.0.x | ✅ |

## Reporting a Vulnerability

Please report security vulnerabilities to security@brixel.example.

## Security Practices

- HTTPS everywhere
- Secure session management
- CSRF protection
- SQL injection protection (parameterized queries via Eloquent/PDO)
- Rate limiting on all API endpoints
- Secure file uploads (type validation, storage outside web root)
- Password hashing (bcrypt/argon2id)
- Multi-factor authentication for privileged roles
- Tenant isolation enforced at application + database level
- Audit trails for all critical actions
- Dependency security checks in CI

## Encryption

Sensitive credentials (API keys, database passwords for integrations) are encrypted at rest using Laravel's encryption facility.