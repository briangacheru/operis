<?php
declare(strict_types=1);

/**
 * Validator — stateless input validation helpers.
 *
 * Each method returns true/false.  Use ::errors() after a batch call to
 * collect all failure messages rather than aborting on the first one.
 *
 * Usage:
 *   $v = new Validator($_POST);
 *   $v->required(['name','email'])->email('email')->minLength('password', 8);
 *   if (!$v->passes()) { sendJsonResponse(['errors' => $v->errors()], 422); }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // -----------------------------------------------------------------------
    // Fluent rule methods
    // -----------------------------------------------------------------------

    public function required(array $fields): static
    {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
                $this->errors[$field][] = "$field is required.";
            }
        }
        return $this;
    }

    public function email(string $field): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "$field must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $min): static
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) > 0 && strlen($value) < $min) {
            $this->errors[$field][] = "$field must be at least $min characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): static
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) > $max) {
            $this->errors[$field][] = "$field must not exceed $max characters.";
        }
        return $this;
    }

    public function numeric(string $field): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = "$field must be a number.";
        }
        return $this;
    }

    public function positiveNumber(string $field): static
    {
        $this->numeric($field);
        $value = $this->data[$field] ?? '';
        if (is_numeric($value) && (float) $value <= 0) {
            $this->errors[$field][] = "$field must be greater than zero.";
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d'): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '') {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field][] = "$field must be a valid date ($format).";
            }
        }
        return $this;
    }

    public function futureDate(string $field): static
    {
        $this->date($field);
        $value = $this->data[$field] ?? '';
        if ($value !== '' && strtotime($value) !== false && strtotime($value) < time()) {
            $this->errors[$field][] = "$field must be a future date.";
        }
        return $this;
    }

    public function in(string $field, array $allowed): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field][] = "$field must be one of: " . implode(', ', $allowed) . '.';
        }
        return $this;
    }

    public function url(string $field): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "$field must be a valid URL.";
        }
        return $this;
    }

    public function integer(string $field): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->errors[$field][] = "$field must be an integer.";
        }
        return $this;
    }

    public function regex(string $field, string $pattern, string $message = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !preg_match($pattern, $value)) {
            $this->errors[$field][] = $message ?: "$field has an invalid format.";
        }
        return $this;
    }

    public function confirmed(string $field, string $confirmField = ''): static
    {
        $confirmField = $confirmField ?: $field . '_confirmation';
        $a = $this->data[$field] ?? '';
        $b = $this->data[$confirmField] ?? '';
        if ($a !== $b) {
            $this->errors[$field][] = "$field and $confirmField do not match.";
        }
        return $this;
    }

    // -----------------------------------------------------------------------
    // Result inspection
    // -----------------------------------------------------------------------

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** First error message for a given field, or null. */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** Flat list of all error messages. */
    public function allErrors(): array
    {
        return array_merge(...array_values($this->errors));
    }

    // -----------------------------------------------------------------------
    // Static convenience helpers (no fluent chain, just a quick bool)
    // -----------------------------------------------------------------------

    public static function isEmail(string $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function isPositiveInt(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0;
    }

    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function sanitizeInt(mixed $value, int $default = 0): int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered !== false ? (int) $filtered : $default;
    }

    public static function sanitizeFloat(mixed $value, float $default = 0.0): float
    {
        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? (float) $filtered : $default;
    }
}
