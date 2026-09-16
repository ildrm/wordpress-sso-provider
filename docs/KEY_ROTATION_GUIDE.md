# Key rotation guide

Status: **Schema and encryption primitive only.** The key table models active,
retiring, and expiry timestamps; private material can be protected with
XChaCha20-Poly1305 and bound context. Key generation, JWKS publication, overlap
automation, certificate import/export, and admin rotation actions are unavailable.
Do not manually populate the table.

