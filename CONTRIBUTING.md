# Zoventra FactoryOS — Contributing

## Development Workflow

1. Create a feature branch from `main`
2. Write tests first (TDD where practical)
3. Implement the feature following module structure standards
4. Run Pest test suite
5. Run linting and formatting
6. Submit a pull request
7. Code review by maintainers
8. Merge to `main` after approval

## Module Structure Standard

Every module follows:
```
ModuleName/
├── Database/Migrations & Seeders
├── Models
├── Actions         # Individual business operations
├── Services        # Public domain contracts
├── DTOs            # Data transfer objects
├── Enums
├── Events          # Domain events
├── Listeners       # Event handlers
├── Jobs            # Queue jobs
├── Policies        # Authorisation
├── Http/Controllers, Requests, Resources
├── Routes/web.php, Routes/api.php
└── Tests
```

## Coding Standards

- PHP: PSR-12
- TypeScript: strict mode
- Commits: conventional commit format (`feat:`, `fix:`, `docs:`, `test:`)

## Testing

Run the full test suite before submitting PRs:
```bash
vendor/bin/pest
npm run test
```