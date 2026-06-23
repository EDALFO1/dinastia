<?php

if (!function_exists('empresaActiva')) {
    function empresaActiva()
    {
        return session('empresa_id');
    }
}