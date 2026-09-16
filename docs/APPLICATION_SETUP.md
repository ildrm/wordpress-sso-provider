# Application setup guide

The management API route is `/wp-json/wp-sso-provider/v1/applications` and requires
the `manage_sso_provider` capability plus normal WordPress REST authentication.

Example request body:

```json
{
  "name": "Payroll",
  "type": "web",
  "redirect_uris": ["https://payroll.example/callback"],
  "allowed_scopes": ["openid", "profile", "email"]
}
```

Interactive application types require at least one exact HTTPS redirect URI.
Loopback HTTP is accepted only for localhost/loopback native development. URI
fragments, user information, wildcards, control characters, and remote HTTP are
rejected. The returned client secret must be copied immediately and stored in the
client's secret manager.

