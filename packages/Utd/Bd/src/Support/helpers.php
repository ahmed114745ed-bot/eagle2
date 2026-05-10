<?php

if (!function_exists('bd_url')) {
    function bd_url($path = '', $parameters = [], $secure = null)
    {
        if (\Illuminate\Support\Facades\URL::isValidUrl($path)) {
            return $path;
        }

        $base = trim(config('bd.route.prefix', 'bd'), '/');

        $secure = $secure ?? (config('bd.https') || config('bd.secure'));

        if (app()->environment(['production', 'Production'])) {
            return secure_url($base . '/' . trim($path, '/'), $parameters);
        }

        return url($base . '/' . trim($path, '/'), $parameters, $secure);
    }
}
