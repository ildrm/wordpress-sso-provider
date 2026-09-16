# Architecture

The plugin is a modular monolith using clean/hexagonal boundaries. Domain and
application services have no WordPress dependency. WordPress controllers,
repositories, Site Health, activation hooks, and administration are outer
adapters. Protocol adapters must call shared identity, policy, session, claims,
replay, crypto, and audit services.

```text
Admin / REST / future protocol endpoints
                  |
        transport adapters
                  |
     application use cases
                  |
 identity · policy · session · claims · replay
                  |
 WordPress DB · WP users · cache · crypto libraries
```

The database is the correctness authority. Cache may accelerate immutable public
configuration but cannot authorize requests or consume one-time credentials. Site
ID is part of every security-sensitive lookup. See ADRs in `docs/adr/`.

