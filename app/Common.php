<?php

/**
 * active() — returns 'active' if current URL matches pattern
 */
function active(string $pattern): string
{
    $current = trim(uri_string(), '/');
    $pattern = trim($pattern, '/');
    if (str_ends_with($pattern, '*')) {
        return str_starts_with($current, rtrim($pattern, '*')) ? 'active' : '';
    }
    return $current === $pattern ? 'active' : '';
}

/**
 * time_elapsed_string() — "2 mins ago"
 */
function time_elapsed_string(string $datetime): string
{
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->h > 0) return $diff->h . 'h ' . $diff->i . 'm ago';
    if ($diff->i > 0) return $diff->i . ' min ago';
    return 'Just now';
}

/**
 * can_access() — view-layer twin of BaseController::can(). Reads the
 * effective permission set the AuthFilter publishes into the session so
 * nav items, buttons and tabs can hide what the role may not use.
 */
function can_access(string $permission): bool
{
    $perms = session('user_perms');
    if (!is_array($perms)) return true; // fail open for logged-out/super contexts
    return \App\Libraries\Permissions::allows($perms, $permission);
}
