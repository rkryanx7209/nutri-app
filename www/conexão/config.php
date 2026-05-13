<?php

if (!defined('APP_DB_HOST')) {
    define('APP_DB_HOST', getenv('NUTRI_DB_HOST') ?: 'localhost');
}

if (!defined('APP_DB_NAME')) {
    define('APP_DB_NAME', getenv('NUTRI_DB_NAME') ?: 'db_dani');
}

if (!defined('APP_DB_USER')) {
    define('APP_DB_USER', getenv('NUTRI_DB_USER') ?: 'root');
}

if (!defined('APP_DB_PASS')) {
    define('APP_DB_PASS', getenv('NUTRI_DB_PASS') ?: '');
}

if (!defined('APP_RESEND_API_KEY')) {
    define('APP_RESEND_API_KEY', getenv('NUTRI_RESEND_API_KEY') ?: 're_e2pJnoTd_88THTtgyHXpRvy9BkSMdRvJf');
}

if (!defined('APP_NUTRI_WHATSAPP')) {
    define('APP_NUTRI_WHATSAPP', getenv('NUTRI_WHATSAPP') ?: '5512982155477');
}

if (!defined('APP_CLINIC_CEP')) {
    define('APP_CLINIC_CEP', getenv('NUTRI_CLINIC_CEP') ?: '12442150');
}

if (!defined('APP_CLINIC_STREET')) {
    define('APP_CLINIC_STREET', getenv('NUTRI_CLINIC_STREET') ?: 'Rua Antonio Alves Diniz, 37');
}

if (!defined('APP_CLINIC_NEIGHBORHOOD')) {
    define('APP_CLINIC_NEIGHBORHOOD', getenv('NUTRI_CLINIC_NEIGHBORHOOD') ?: 'Residencial Vista Alegre');
}

if (!defined('APP_CLINIC_CITY')) {
    define('APP_CLINIC_CITY', getenv('NUTRI_CLINIC_CITY') ?: 'Pindamonhangaba');
}
