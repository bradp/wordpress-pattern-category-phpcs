# WordPressPatternCategory

PHPCS sniffs for enforcing categories in WordPress block pattern files.

## Installation

```bash
composer require --dev bradp/wordpress-pattern-category-phpcs
```

## Usage

Add the rule to your project's `phpcs.xml` or `.phpcs.xml.dist`:

```xml
<rule ref="PatternCategory.Patterns.PatternCategory">
    <properties>
        <property name="base_categories" value="your-category, another-category" />
    </properties>
</rule>
```

The legacy single-category property still works:

```xml
<rule ref="PatternCategory.Patterns.PatternCategory">
    <properties>
        <property name="base_category" value="your-category" />
    </properties>
</rule>
```

## What it checks

The sniff validates that WordPress block pattern files contain a file-level docblock with the correct metadata:

1. **Missing docblock** — The pattern file must have a `/** ... */` docblock near the top.
2. **Missing Categories** — The docblock must include a `Categories:` line.
3. **Missing required category** — If `base_category` or `base_categories` is configured, the pattern must include at least one configured category in the comma-separated list.

### Example of a valid pattern file

```php
<?php
/**
 * Title: Hero Banner
 * Slug: my-theme/hero-banner
 * Categories: my-theme, banner
 */
?>
<!-- pattern markup -->
```
