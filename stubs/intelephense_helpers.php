<?php
// Intelephense helper stubs to silence undefined function warnings.
// These are only for static analysis and should not affect runtime.

if (!function_exists('env')) {
    /**
     * Get an environment variable or default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null) {}
}
