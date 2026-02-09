# Theme Bundle

A Symfony bundle that combines and serves CSS/JS files as single bundled assets with intelligent HTTP caching and source map support.

## Features

- **File combining** — merge multiple CSS or JS files into a single response
- **Namespace paths** — organize assets with `@namespace/file.css` references
- **HTTP caching** — automatic ETag, Last-Modified, and 304 Not Modified responses
- **Source maps** — v3 source map generation for combined files (configurable)
- **Twig integration** — `themeAsset()` function for easy template usage
- **Performance profiling** — optional Stopwatch integration

## Requirements

- PHP 8.4+
- Symfony 8.0+
- Twig 3.0+ or 4.0+

## Installation

```bash
composer require psychob/theme-bundle
```

The bundle auto-registers via Symfony Flex.

## Configuration

Create or edit `config/packages/theme.yaml`:

```yaml
theme:
    # Enable source map generation (useful in dev)
    sourcemaps: '%kernel.debug%'

    # Map namespace aliases to filesystem directories
    paths:
        '%kernel.project_dir%/assets/css': app
        '%kernel.project_dir%/vendor/some-lib/styles': lib

    # Define combined file bundles
    files:
        app.css:
            - '@app/reset.css'
            - '@app/layout.css'
            - '@lib/components.css'
            - '@app/main.css'
        app.js:
            - '@app/utils.js'
            - '@app/main.js'
```

### Options

| Option | Type | Default | Description |
|---|---|---|---|
| `sourcemaps` | `bool` | `false` | Enable source map generation |
| `paths` | `map` | `{}` | Maps filesystem directories to namespace aliases |
| `files` | `map` | `{}` | Defines combined output files and their source files |

## Usage

### Twig

Use `themeAsset()` to generate URLs to combined files:

```twig
<link rel="stylesheet" href="{{ themeAsset('app.css') }}">
<script src="{{ themeAsset('app.js') }}"></script>
```

This generates URLs like `/_/theme/app.css` and `/_/theme/app.js`.

### HTTP Caching

The bundle serves combined files at `/_/theme/{file}` with:

- **ETag** header for cache validation
- **Last-Modified** header as fallback
- **304 Not Modified** responses when content hasn't changed
- **Cache-Control: public, must-revalidate**

Source maps are served at `/_/theme/{hash}.{ext}.map` with immutable, long-lived cache headers (1 year).

### Namespaces

The `@namespace/` prefix in file paths resolves to the directory mapped in `paths`:

```yaml
theme:
    paths:
        '%kernel.project_dir%/assets/css': app

    files:
        app.css:
            - '@app/reset.css'   # resolves to %kernel.project_dir%/assets/css/reset.css
```

## Development

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Static analysis
composer psalm

# Check code style
composer lint

# Fix code style
composer format
```

## License

[MPL-2.0](https://www.mozilla.org/en-US/MPL/2.0/)
