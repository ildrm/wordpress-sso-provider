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

Interactive application types require at least one registered redirect URI. Web
and SPA clients require HTTPS with an ASCII DNS host. Native and desktop clients
may also register reverse-domain private schemes such as
`com.example.app:/oauth2redirect/provider`, or literal IPv4/IPv6 loopback HTTP
redirects such as `http://127.0.0.1:10000/callback` and
`http://[::1]:10000/callback`. At request time, only the port of a registered
loopback URI may differ. `localhost`, remote HTTP, generic private schemes,
fragments, user information, wildcards, control characters, and malformed
percent encodings are rejected.

Native and desktop registrations are public clients and receive no client
secret. Confidential client secrets are returned only on creation and must be
stored in the client's secret manager. New applications remain in `draft`; this
release has no public OAuth authorization flow or activation workflow. These
redirect rules prepare application registration and do not enable mobile sign-in.

