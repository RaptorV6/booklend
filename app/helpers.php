<?php

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════

/**
 * Escape HTML output (XSS protection)
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate SEO-friendly slug
 */
function generateSlug(string $title): string {
    // Transliterate
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title);

    // Lowercase
    $slug = strtolower($slug);

    // Remove special chars
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    // Remove multiple dashes
    $slug = preg_replace('/-+/', '-', $slug);

    // Trim
    return trim($slug, '-');
}

/**
 * Validate form data
 */
function validate(array $data, array $rules): array {
    $errors = [];

    foreach ($rules as $field => $ruleString) {
        $rules = explode('|', $ruleString);
        $value = $data[$field] ?? null;

        foreach ($rules as $rule) {
            if ($rule === 'required' && empty($value)) {
                $errors[$field][] = "Pole {$field} je povinné";
            }

            if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = "Neplatný email";
            }

            if (strpos($rule, 'min:') === 0) {
                $min = (int) substr($rule, 4);
                if (strlen($value) < $min) {
                    $errors[$field][] = "Minimálně {$min} znaků";
                }
            }

            if (strpos($rule, 'max:') === 0) {
                $max = (int) substr($rule, 4);
                if (strlen($value) > $max) {
                    $errors[$field][] = "Maximálně {$max} znaků";
                }
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;
        return [];
    }

    return $data;
}

/**
 * Get old input value
 */
function old(string $key, string $default = ''): string {
    $value = $_SESSION['old'][$key] ?? $default;
    return e($value);
}

/**
 * Get validation error
 */
function error(string $key): ?string {
    $errors = $_SESSION['errors'][$key] ?? null;
    return $errors ? $errors[0] : null;
}

/**
 * Clear flash data
 */
function clearFlash(): void {
    unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);
}

/**
 * Redirect
 */
function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Get JSON input from request body
 */
function jsonInput(): ?array {
    $input = file_get_contents('php://input');
    return $input ? json_decode($input, true) : null;
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
