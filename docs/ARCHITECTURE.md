# ZauberCMS Architecture

ZauberCMS is designed around four layers:

1. **Core** — bootstrapping, configuration, database, routing, authentication, permissions and shared services.
2. **Modules** — optional features such as blog, forms, products, booking or reviews.
3. **Themes** — presentation layer for customer-specific frontend design.
4. **Customer extensions** — project-specific functionality that must not modify the core.

## Design goals

- Keep the core stable and updateable.
- Avoid customer-specific changes in core files.
- Make modules independently installable and disableable.
- Keep themes separate from application logic.
- Store secrets outside version control.
- Support future automated updates and commercial license validation.

## Planned top-level structure

```text
app/
config/
database/
docs/
installer/
modules/
public/
storage/
themes/
tests/
```

The structure will evolve through issue-driven development and documented migrations.
