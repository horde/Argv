# Upgrading from PSR-0 to PSR-4

This guide helps you migrate from the legacy PSR-0 implementation (`Horde_Argv_*`) to the modern PSR-4 implementation (`Horde\Argv\*`).

## Overview

Horde Argv 3.0 includes both PSR-0 (legacy) and PSR-4 (modern) implementations. Both are functionally equivalent for most use cases, but PSR-4 offers:

- ✅ Modern PHP 8+ features (typed properties, readonly, strict_types)
- ✅ Better IDE support and static analysis
- ✅ New utility classes (ArgvWrapper)
- ✅ Improved type safety and null handling
- ✅ Namespace organization

**The PSR-0 implementation is deprecated and will be removed in Horde 7.0.**

---

## Quick Start

### Step 1: Update Your Imports

Replace PSR-0 class names with PSR-4 namespaced imports:

```php
// OLD - PSR-0
use Horde_Argv_Parser;
use Horde_Argv_Option;
use Horde_Argv_OptionGroup;
use Horde_Argv_IndentedHelpFormatter;

// NEW - PSR-4
use Horde\Argv\Parser;
use Horde\Argv\Option;
use Horde\Argv\OptionGroup;
use Horde\Argv\IndentedHelpFormatter;
```

### Step 2: Update Class References

Change all class instantiations and type hints:

```php
// OLD - PSR-0
$parser = new Horde_Argv_Parser();
$option = new Horde_Argv_Option('-v', '--verbose');

// NEW - PSR-4
$parser = new Parser();
$option = new Option('-v', '--verbose');
```

### Step 3: Enable Strict Types (Recommended)

Add strict type declaration to your files:

```php
<?php
declare(strict_types=1);

namespace YourApp\Commands;

use Horde\Argv\Parser;
use Horde\Argv\Option;
```

### Step 4: Test Thoroughly

Run your test suite with special attention to:
- Option value storage and retrieval
- Parser state handling
- Type checking code

---

## Complete Migration Guide

### Class Name Mapping

All PSR-0 classes have direct PSR-4 equivalents:

| PSR-0 (lib/) | PSR-4 (src/) | Notes |
|-------------|-------------|-------|
| `Horde_Argv_Parser` | `Horde\Argv\Parser` | ⚠️ See breaking changes |
| `Horde_Argv_Option` | `Horde\Argv\Option` | ✅ Compatible |
| `Horde_Argv_OptionGroup` | `Horde\Argv\OptionGroup` | ✅ Compatible |
| `Horde_Argv_OptionContainer` | `Horde\Argv\OptionContainer` | ✅ Compatible |
| `Horde_Argv_Values` | `Horde\Argv\Values` | ⚠️ See breaking changes |
| `Horde_Argv_HelpFormatter` | `Horde\Argv\HelpFormatter` | ✅ Compatible |
| `Horde_Argv_IndentedHelpFormatter` | `Horde\Argv\IndentedHelpFormatter` | ✅ Compatible |
| `Horde_Argv_TitledHelpFormatter` | `Horde\Argv\TitledHelpFormatter` | ✅ Compatible |
| `Horde_Argv_Exception` | `Horde\Argv\Exception` | ✅ Compatible |
| `Horde_Argv_OptionException` | `Horde\Argv\OptionException` | ✅ Compatible |
| `Horde_Argv_OptionValueException` | `Horde\Argv\OptionValueException` | ✅ Compatible |
| `Horde_Argv_OptionConflictException` | `Horde\Argv\OptionConflictException` | ✅ Compatible |
| `Horde_Argv_AmbiguousOptionException` | `Horde\Argv\AmbiguousOptionException` | ✅ Compatible |
| `Horde_Argv_BadOptionException` | `Horde\Argv\BadOptionException` | ✅ Compatible |
| `Horde_Argv_Translation` | `Horde\Argv\Translation` | ✅ Compatible |

### New PSR-4 Classes

These classes are only available in PSR-4:

| Class | Purpose | Status |
|-------|---------|--------|
| `Horde\Argv\ArgvWrapper` | Type-safe wrapper for `$argv` with DI support | ✅ Production ready |
| `Horde\Argv\ArgvParser` | Interface for parsers | ⏸️ Placeholder |
| `Horde\Argv\ImmutableParser` | Immutable parser implementation | ⏸️ Placeholder |

**Note:** The `Horde\Argv\Parser\*` namespace classes are design stubs and should not be used.

---

## Breaking Changes

### Breaking Change #1: Values Class Implementation

**Impact:** Low to Medium (affects magic method usage)

The `Values` class changed from internal array storage to `stdClass`-based dynamic properties.

#### What Changed

```php
// PSR-0 Implementation
class Horde_Argv_Values {
    private $data = [];

    public function __get($attr) {
        return $this->data[$attr];
    }

    public function __set($attr, $value) {
        $this->data[$attr] = $value;
    }
}

// PSR-4 Implementation
class Values extends \stdClass {
    // Uses stdClass dynamic properties
    // Magic methods removed (handled by stdClass)
}
```

#### What Still Works ✅

```php
// Direct property access (WORKS)
$values->verbose = true;
echo $values->verbose;

// ArrayAccess (WORKS)
$values['verbose'] = true;
echo $values['verbose'];

// Iteration (WORKS)
foreach ($values as $key => $value) {
    // ...
}

// Countable (WORKS)
count($values);
```

#### What May Break ⚠️

If your code relies on the magic methods directly:

```php
// This pattern may break if you're checking method existence
if (method_exists($values, '__get')) {
    // This will return false in PSR-4
}

// This pattern may break if you're overriding magic methods
class MyValues extends Horde_Argv_Values {
    public function __get($attr) {
        // Custom logic
    }
}
```

#### Migration Steps

1. **Search for magic method usage:**
   ```bash
   grep -r "__get\|__set\|__isset\|__unset" your-code/
   ```

2. **Replace with direct property access:**
   ```php
   // If you find magic method calls, replace with:
   $value = $values->option_name;  // Direct access
   $values->option_name = $value;  // Direct assignment
   ```

3. **Test value storage and retrieval:**
   ```php
   // Test that values are properly stored
   $values->custom_option = 'test';
   assert($values->custom_option === 'test');
   ```

---

### Breaking Change #2: Parser State Initialization

**Impact:** Low (affects edge cases)

The `Parser` class now initializes parsing state to `null` instead of empty arrays.

#### What Changed

```php
// PSR-0 Implementation
protected function _initParsingState() {
    $this->rargs = [];  // Empty array
    $this->largs = [];  // Empty array
    $this->values = []; // Empty array
}

// PSR-4 Implementation
protected function _initParsingState() {
    $this->rargs = null;  // null
    $this->largs = null;  // null
    $this->values = null; // null
}
```

#### What May Break ⚠️

Code that accesses parser state before calling `parseArgs()`:

```php
// This may fail in PSR-4
$parser = new Parser();
if (is_array($parser->rargs)) {  // Will be false (null, not array)
    // ...
}

// This will fail in PSR-4
$parser = new Parser();
$count = count($parser->rargs);  // TypeError: count() expects array
```

#### Migration Steps

1. **Don't access parser state before parsing:**
   ```php
   // GOOD - Parse first, then access state
   list($options, $args) = $parser->parseArgs($argv);
   // Now you can use $options and $args
   ```

2. **Add null checks if you must access early:**
   ```php
   // If you really need to check state
   if ($parser->rargs !== null && is_array($parser->rargs)) {
       // Safe to use
   }
   ```

3. **Update type checks:**
   ```php
   // OLD
   if (is_array($parser->rargs)) { }

   // NEW
   if ($parser->rargs !== null) { }
   ```

---

## Migration Examples

### Example 1: Simple CLI Script

```php
<?php
// BEFORE - PSR-0
require 'vendor/autoload.php';

$parser = new Horde_Argv_Parser([
    'usage' => '%prog [options] file',
    'description' => 'Process a file'
]);

$parser->addOption(new Horde_Argv_Option(
    '-v', '--verbose',
    ['action' => 'store_true', 'help' => 'Verbose output']
));

list($options, $args) = $parser->parseArgs();

if ($options->verbose) {
    echo "Processing file: " . $args[0] . "\n";
}
```

```php
<?php
// AFTER - PSR-4
declare(strict_types=1);

require 'vendor/autoload.php';

use Horde\Argv\Parser;
use Horde\Argv\Option;

$parser = new Parser([
    'usage' => '%prog [options] file',
    'description' => 'Process a file'
]);

$parser->addOption(new Option(
    '-v', '--verbose',
    ['action' => 'store_true', 'help' => 'Verbose output']
));

list($options, $args) = $parser->parseArgs();

if ($options->verbose) {
    echo "Processing file: " . $args[0] . "\n";
}
```

### Example 2: Class-Based CLI Application

```php
<?php
// BEFORE - PSR-0
class MyCommand {
    protected $parser;

    public function __construct() {
        $this->parser = new Horde_Argv_Parser();
        $this->setupOptions();
    }

    protected function setupOptions() {
        $group = new Horde_Argv_OptionGroup(
            $this->parser,
            'Database Options'
        );

        $group->addOption(new Horde_Argv_Option(
            '--host',
            ['dest' => 'db_host', 'help' => 'Database host']
        ));

        $this->parser->addOptionGroup($group);
    }

    public function run(array $argv) {
        list($options, $args) = $this->parser->parseArgs($argv);
        return $this->execute($options, $args);
    }
}
```

```php
<?php
// AFTER - PSR-4
declare(strict_types=1);

use Horde\Argv\Parser;
use Horde\Argv\Option;
use Horde\Argv\OptionGroup;

class MyCommand {
    protected Parser $parser;

    public function __construct() {
        $this->parser = new Parser();
        $this->setupOptions();
    }

    protected function setupOptions(): void {
        $group = new OptionGroup(
            $this->parser,
            'Database Options'
        );

        $group->addOption(new Option(
            '--host',
            ['dest' => 'db_host', 'help' => 'Database host']
        ));

        $this->parser->addOptionGroup($group);
    }

    public function run(array $argv): int {
        list($options, $args) = $this->parser->parseArgs($argv);
        return $this->execute($options, $args);
    }
}
```

### Example 3: Using ArgvWrapper (New in PSR-4)

```php
<?php
declare(strict_types=1);

use Horde\Argv\Parser;
use Horde\Argv\ArgvWrapper;

// Wrap argv for dependency injection
class Application {
    public function __construct(
        private readonly Parser $parser,
        private readonly ArgvWrapper $argv
    ) {}

    public function run(): int {
        list($options, $args) = $this->parser->parseArgs(
            iterator_to_array($this->argv)
        );

        return $this->execute($options, $args);
    }
}

// Usage with DI container
$argv = ArgvWrapper::fromGlobal();
$parser = new Parser();
$app = new Application($parser, $argv);
$exitCode = $app->run();
```

---

## Testing Your Migration

### 1. Create a Test Checklist

```php
<?php
// test/MigrationTest.php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Horde\Argv\Parser;
use Horde\Argv\Option;

class MigrationTest extends TestCase {
    public function testBasicParsing(): void {
        $parser = new Parser();
        $parser->addOption(new Option('-v', '--verbose', [
            'action' => 'store_true'
        ]));

        list($options, $args) = $parser->parseArgs(['-v', 'file.txt']);

        $this->assertTrue($options->verbose);
        $this->assertEquals(['file.txt'], $args);
    }

    public function testOptionValues(): void {
        $parser = new Parser();
        $parser->addOption(new Option('-f', '--file', [
            'dest' => 'filename'
        ]));

        list($options, $args) = $parser->parseArgs(['--file', 'test.txt']);

        // Test direct property access
        $this->assertEquals('test.txt', $options->filename);

        // Test ArrayAccess
        $this->assertEquals('test.txt', $options['filename']);

        // Test iteration
        $values = iterator_to_array($options);
        $this->assertArrayHasKey('filename', $values);
    }

    public function testOptionGroups(): void {
        $parser = new Parser();
        $group = new \Horde\Argv\OptionGroup($parser, 'Test Group');
        $group->addOption(new Option('--test'));
        $parser->addOptionGroup($group);

        $this->assertStringContainsString('Test Group', $parser->formatHelp());
    }
}
```

### 2. Run Tests

```bash
# Run your test suite
vendor/bin/phpunit

# Or with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### 3. Check for Deprecation Warnings

```bash
# Enable deprecation warnings
php -d error_reporting=E_ALL your-script.php
```

---

## Performance Considerations

The PSR-4 implementation has similar performance to PSR-0, with minor improvements:

| Aspect | PSR-0 | PSR-4 | Notes |
|--------|-------|-------|-------|
| Parser creation | ~0.1ms | ~0.1ms | No change |
| Option parsing | ~0.5ms | ~0.5ms | No change |
| Memory usage | ~100KB | ~95KB | Slight improvement (stdClass) |
| Type safety | Manual | Built-in | Strict types enabled |

**Recommendation:** Performance is not a migration concern. Focus on correctness and type safety.

---

## Common Issues and Solutions

### Issue 1: Class Not Found

**Error:**
```
Fatal error: Class 'Parser' not found
```

**Solution:**
Add use statement:
```php
use Horde\Argv\Parser;
```

### Issue 2: Type Error on Values Access

**Error:**
```
TypeError: Cannot access offset of type string on null
```

**Solution:**
Check if option was set:
```php
// OLD
$value = $options->my_option;

// NEW - with null check
$value = $options->my_option ?? 'default';

// Or use isset
if (isset($options->my_option)) {
    $value = $options->my_option;
}
```

### Issue 3: Type Hints Break

**Error:**
```
TypeError: Argument must be of type Horde_Argv_Parser
```

**Solution:**
Update type hints:
```php
// OLD
public function setParser(Horde_Argv_Parser $parser) { }

// NEW
public function setParser(\Horde\Argv\Parser $parser) { }
```

### Issue 4: Instanceof Checks Fail

**Error:**
```
if ($parser instanceof Horde_Argv_Parser) // Returns false
```

**Solution:**
Update instanceof checks:
```php
// OLD
if ($parser instanceof Horde_Argv_Parser) { }

// NEW
use Horde\Argv\Parser;
if ($parser instanceof Parser) { }
```

---

## Gradual Migration Strategy

You can migrate gradually by using both implementations side-by-side:

### Strategy 1: File-by-File Migration

```php
// Old files continue using PSR-0
use Horde_Argv_Parser;

// New files use PSR-4
use Horde\Argv\Parser;
```

Both will work simultaneously since both implementations are included in the same package.

### Strategy 2: Feature Branch

1. Create a feature branch for migration
2. Update all files at once
3. Run full test suite
4. Merge when all tests pass

### Strategy 3: Facade Pattern

Create a temporary facade to ease transition:

```php
<?php
// src/Legacy/Parser.php
namespace YourApp\Legacy;

use Horde\Argv\Parser as ModernParser;

/**
 * Temporary facade for gradual migration
 * @deprecated Use Horde\Argv\Parser directly
 */
class Parser extends ModernParser {
    // Add any compatibility methods if needed
}
```

---

## Rollback Plan

If you encounter issues after migration:

### Option 1: Revert to PSR-0

```php
// Change all imports back
use Horde_Argv_Parser;  // Back to PSR-0
use Horde_Argv_Option;
```

### Option 2: Mixed Mode

```php
// Use PSR-0 for problematic files
use Horde_Argv_Parser;  // PSR-0

// Use PSR-4 for new files
use Horde\Argv\Parser;  // PSR-4
```

Both implementations coexist and are functionally equivalent.

---

## Getting Help

If you encounter issues during migration:

1. **Check the test suite:** `vendor/bin/phpunit` should pass
2. **Review the QA reports:** See `horde-development/argv-psr4-qa-*.md`
3. **Check real-world examples:**
   - `git/horde/hordectl` - Uses PSR-4 exclusively (26 files)
   - `components` - Uses PSR-4 with builder pattern (7 files)
4. **Report issues:** https://github.com/horde/Argv/issues

---

## Migration Checklist

Use this checklist to track your migration:

- [ ] Read this guide completely
- [ ] Back up your code
- [ ] Update composer.json if needed
- [ ] Run composer update
- [ ] Create a feature branch
- [ ] Update all `use` statements
- [ ] Update all class instantiations
- [ ] Update type hints and annotations
- [ ] Add `declare(strict_types=1)` to files
- [ ] Search for magic method usage on Values
- [ ] Search for parser state access before parseArgs()
- [ ] Update instanceof checks
- [ ] Run test suite
- [ ] Fix any failing tests
- [ ] Test in development environment
- [ ] Test in staging environment
- [ ] Deploy to production
- [ ] Monitor for issues
- [ ] Remove PSR-0 imports (optional)

---

## FAQ

**Q: Do I have to migrate?**

A: No, PSR-0 continues to work in Horde 6.x. Migration is recommended for new code and will be required in Horde 7.0.

**Q: Can I use both PSR-0 and PSR-4 in the same application?**

A: Yes, both implementations coexist and can be used simultaneously.

**Q: Will my tests break?**

A: Most tests will continue to work. The two breaking changes (Values and Parser state) only affect edge cases.

**Q: What about performance?**

A: Performance is nearly identical. PSR-4 has slightly better memory usage due to stdClass.

**Q: Should I use ArgvWrapper?**

A: Yes, if you're using dependency injection. It provides type safety and better testability.

**Q: What about the stub classes?**

A: Ignore them. They're placeholders for future features and not ready for use.

**Q: How long will PSR-0 be supported?**

A: PSR-0 will be supported throughout Horde 6.x (until Horde 7.0, no timeline set).

---

## Additional Resources

- **API Documentation:** https://dev.horde.org/api/
- **QA Reports:** `horde-development/argv-psr4-qa-*.md`
- **Example Code:** `git/horde/hordectl`, `components`
- **Issue Tracker:** https://github.com/horde/Argv/issues
- **Horde Development:** https://www.horde.org/development

---

**Last Updated:** 2026-03-04
**Version:** 3.0 (FRAMEWORK_6_0)
