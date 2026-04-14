<?php
if (!function_exists('redirectView')) {
    function redirectView(
        string $route = 'login',
        mixed $validation = null,
        ?array $flashMessages = null,
        ?array $last_data = null,
        ?string $last_action = null
    ) {
        $messages   = $flashMessages ?? [];
        $fieldErrors = null; // ✅ Array por campo para mostrar debajo de inputs

        if (!empty($validation)) {
            if (is_array($validation)) {
                $fieldErrors = $validation; // ✅ ['nombre_banco' => 'mensaje', ...]
                $errorText   = implode('<br>', $validation);
            } else {
                $errorText = (string) $validation;
            }

            array_unshift($messages, [$errorText, 'error', 'center']);
        }

        return redirect()->to($route)
            ->with('flashMessages',   !empty($messages) ? $messages : null)
            ->with('flashValidation', $fieldErrors)   // ✅ Array por campo
            ->with('last_data',       $last_data)
            ->with('last_action',     $last_action);
    }
}