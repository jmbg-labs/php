# JMBG PHP Library

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.1-blue)](https://www.php.net/)

A PHP library for validating and parsing Serbian unique master citizen numbers (JMBG - Jedinstveni Matični Broj Građana).

## Features

- ✅ Validate JMBG numbers with comprehensive checks
- ✅ Extract birth date, region, and gender information
- ✅ Support for all Serbian regions
- ✅ Calculate age from JMBG
- ✅ Type-safe with PHP 8.1+ features
- ✅ Fully tested with PHPUnit
- ✅ PSR-12 compliant code

## Installation

Install via Composer:

```bash
composer require jmbg-labs/jmbg
```

## Usage

### Basic Validation

```php
use JmbgLabs\Jmbg\Jmbg;

// Quick validation
if (Jmbg::valid('0101000710009')) {
    echo "Valid JMBG";
}

// Parse and validate
try {
    $jmbg = Jmbg::parse('0101000710009');
    echo "Valid JMBG: " . $jmbg->format();
} catch (JmbgException $e) {
    echo "Invalid JMBG: " . $e->getMessage();
}
```

### Extract Information

```php
use JmbgLabs\Jmbg\Jmbg;

$jmbg = new Jmbg('0101000710009');

// Get birth date
$date = $jmbg->getDate(); // DateTime object
echo $date->format('Y-m-d'); // 2000-01-01

// Get age
echo $jmbg->getAge(); // e.g., 26

// Check gender
if ($jmbg->isMale()) {
    echo "Male";
}

if ($jmbg->isFemale()) {
    echo "Female";
}

// Access individual parts
echo $jmbg->day;           // 1
echo $jmbg->month;         // 1
echo $jmbg->year;          // 2000
echo $jmbg->region;        // 71
echo $jmbg->region_text;   // "Belgrade"
echo $jmbg->country;       // "Serbia"
echo $jmbg->unique;        // 0
echo $jmbg->checksum;      // 9
```

### Using Magic Methods

```php
$jmbg = new Jmbg('1505995800002');

// String conversion
echo (string)$jmbg; // "1505995800002"
echo $jmbg->format(); // "1505995800002"

// Property access
echo $jmbg->original;        // "1505995800002"
echo $jmbg->day_original;    // "15"
echo $jmbg->month_original;  // "05"
echo $jmbg->year_original;   // "995"
echo $jmbg->region_original; // "80"
echo $jmbg->unique_original; // "000"

// Check property existence
if (isset($jmbg->region_text)) {
    echo $jmbg->region_text; // "Novi Sad"
}
```

## JMBG Format

JMBG consists of 13 digits: `DDMMYYYRRBBBC`

- **DD** - Day of birth (01-31)
- **MM** - Month of birth (01-12)
- **YYY** - Year of birth (last 3 digits)
- **RR** - Region code
- **BBB** - Unique number (000-499 for males, 500-999 for females)
- **C** - Checksum digit

### Supported Regions

The library supports all Serbian and ex-Yugoslav regions including (beware: ex-Yugoslav regions codes may have changed since the breakup):

- **Serbia** (71-79): Belgrade, Kragujevac, Niš, etc.
- **Serbia/Vojvodina** (80-89): Novi Sad, Subotica, Pančevo, etc.
- **Serbia/Kosovo** (91-96): Priština, Peć, Prizren, etc.
- **Bosnia and Herzegovina** (10-19)
- **Montenegro** (21-29)
- **Croatia** (30-39)
- **Macedonia** (41-49)
- **Slovenia** (50)

## Validation Rules

The library performs comprehensive validation:

1. **Length check** - Must be exactly 13 digits
2. **Format check** - Must contain only numeric characters
3. **Date validation** - Birth date must be valid (including leap year support)
4. **Region validation** - Region code must exist in the registry
5. **Checksum validation** - Modulo 11 algorithm verification

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

### Code Style

```bash
# Check code style
composer cs

# Fix code style issues
composer cs-fix
```

## Requirements

- PHP ^8.1
- No external dependencies (for production use)

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass (`composer test`)
2. Code follows PSR-12 standards (`composer cs`)
3. Add tests for new features
4. Update documentation as needed

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Developed by [JMBG Labs](https://github.com/jmbg-labs)

## Support

- 🐛 [Report Issues](https://github.com/jmbg-labs/php/issues)
- 📖 [Source Code](https://github.com/jmbg-labs/php)

## Examples

### Validate Multiple JMBGs

```php
$jmbgs = ['0710003730015', '1705978730032', 'invalid'];

foreach ($jmbgs as $jmbgString) {
    if (Jmbg::valid($jmbgString)) {
        $jmbg = Jmbg::parse($jmbgString);
        echo sprintf(
            "%s - Born: %s, Region: %s, Gender: %s\n",
            $jmbg->format(),
            $jmbg->getDate()->format('Y-m-d'),
            $jmbg->region_text,
            $jmbg->isMale() ? 'Male' : 'Female'
        );
    } else {
        echo "$jmbgString - Invalid\n";
    }
}
```

### Age Calculation

```php
$jmbg = new Jmbg('0710003730015');
$age = $jmbg->getAge();

if ($age >= 18) {
    echo "Adult";
} else {
    echo "Minor";
}
```

### Error Handling

```php
use JmbgLabs\Jmbg\Jmbg;
use JmbgLabs\Jmbg\JmbgException;

try {
    $jmbg = new Jmbg('1234567890123');
} catch (JmbgException $e) {
    // Handle specific validation errors
    echo "Validation failed: " . $e->getMessage();
    // Possible messages:
    // - "Input string must have 13 digits."
    // - "JMBG string must have 13 digits."
    // - "Date '...' is not valid."
    // - "Region '...' is not valid for Serbian JMBG."
    // - "Checksum is not valid."
}
```
