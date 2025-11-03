# Contributing

Thank you for considering contributing to this project!

## Development Setup

1. Fork the repository
2. Clone your fork
3. Run `make install` to set up the development environment (or `composer install && npm install` if not using Make)
4. Create a new branch for your feature or bugfix using the naming convention: `feature/description-of-feature` or `fix/description-of-fix`

## Branch Naming & PR Workflow

We follow a **linear PR process** for repository hygiene and maintainability:

- **Branch naming**: Use descriptive names like `feature/`, `fix/`, `chore/`, or `docs/` prefixes (e.g., `feature/user-auth`, `fix/cache-bug`)
- **Single increment per PR**: Each PR should represent a single, cohesive change
- **Linear history**: PRs are merged in order to maintain a clean commit history
- **No generated artifacts**: Do not commit compiled CSS, minified JS, or other build artifacts

## Code Standards & Formatting

### PHP Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) for PHP
- Use `.editorconfig` settings for consistent code formatting
- Run `composer test` locally before pushing to check for violations:
  ```bash
  composer test
  ```

### JavaScript, CSS, JSON, and Markdown

- Use Prettier for consistent formatting across JS, CSS, JSON, and Markdown files
- Run format check before pushing:
  ```bash
  npm run format:check
  ```
- Auto-fix formatting issues:
  ```bash
  npm run format
  ```

## Pre-Push Checklist

Before pushing your changes:

1. Run `composer test` to validate PHP code standards
2. Run `npm run format:check` to validate all formatting
3. Ensure your code follows the project's coding standards
4. Commit messages should be clear and descriptive
5. Update documentation if your changes affect user-facing behavior

## Pull Request Process

1. Ensure your branch is up-to-date with main/develop
2. Run all local checks (`composer test` and `npm run format:check`)
3. Update the README.md or documentation with details of changes if applicable
4. Submit your pull request with a clear description of the changes
5. The CI pipeline will automatically validate:
   - Composer validation and dependencies
   - PHP code standards (via phpcs)
   - Code formatting (via Prettier)
   - Docker build

## CI/CD Pipeline

The GitHub Actions CI pipeline runs the following checks on every push and PR:

- **validate**: Composer validation, dependency installation, security audits, and PHP tests
- **code-quality**: PHP code quality checks via PHPCS
- **format-check**: JavaScript, CSS, JSON, and Markdown formatting checks via Prettier
- **docker**: Docker image build validation

All checks must pass before a PR can be merged.

## Code Review

All submissions require review before being merged. We will review your pull request as soon as possible and provide feedback.

## Reporting Issues

When reporting issues, please include:

- Clear description of the issue
- Steps to reproduce
- Expected behavior
- Actual behavior
- Environment details (OS, Docker version, PHP version, etc.)
