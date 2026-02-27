<?php
/**
 * Smarty function plugin: {csrf_field}
 *
 * Outputs a hidden input containing the CSRF token.
 * Use inside every Smarty form that performs a state change:
 *   {csrf_field}
 */
function smarty_function_csrf_field($params, $smarty) {
    return csrf_field();
}
