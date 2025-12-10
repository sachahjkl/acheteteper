<?php

namespace Acheteteper;

/**
 * Simple validation helper.
 * 
 * @package Acheteteper
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * @param array $data Data to validate.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Check if a field is required and not empty.
     * 
     * @param string $field Field name.
     * @param string $message Error message.
     * @return self
     */
    public function required(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') {
            $this->errors[$field] = $message ?? "Field '$field' is required";
        }
        return $this;
    }

    /**
     * Check if a field is a valid email.
     * 
     * @param string $field Field name.
     * @param string $message Error message.
     * @return self
     */
    public function email(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Field '$field' must be a valid email";
        }
        return $this;
    }

    /**
     * Check if a field has minimum length.
     * 
     * @param string $field Field name.
     * @param int $min Minimum length.
     * @param string $message Error message.
     * @return self
     */
    public function minLength(string $field, int $min, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) < $min) {
            $this->errors[$field] = $message ?? "Field '$field' must be at least $min characters";
        }
        return $this;
    }

    /**
     * Check if a field has maximum length.
     * 
     * @param string $field Field name.
     * @param int $max Maximum length.
     * @param string $message Error message.
     * @return self
     */
    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) > $max) {
            $this->errors[$field] = $message ?? "Field '$field' must be at most $max characters";
        }
        return $this;
    }

    /**
     * Check if a field matches a pattern.
     * 
     * @param string $field Field name.
     * @param string $pattern Regex pattern.
     * @param string $message Error message.
     * @return self
     */
    public function pattern(string $field, string $pattern, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !preg_match($pattern, $value)) {
            $this->errors[$field] = $message ?? "Field '$field' format is invalid";
        }
        return $this;
    }

    /**
     * Check if a field equals another field.
     * 
     * @param string $field Field name.
     * @param string $otherField Other field name to compare.
     * @param string $message Error message.
     * @return self
     */
    public function equals(string $field, string $otherField, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;
        $otherValue = $this->data[$otherField] ?? null;
        if ($value !== $otherValue) {
            $this->errors[$field] = $message ?? "Field '$field' must match '$otherField'";
        }
        return $this;
    }

    /**
     * Add a custom validation error.
     * 
     * @param string $field Field name.
     * @param string $message Error message.
     * @return self
     */
    public function error(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Check if validation passed (no errors).
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all validation errors.
     * 
     * @return array<string, string> Associative array of field => error message.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get error for a specific field.
     * 
     * @param string $field Field name.
     * @return string|null Error message or null if no error.
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
}
