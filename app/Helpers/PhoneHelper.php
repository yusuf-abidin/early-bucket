<?php

function normalize_indo_phone(?string $phone): ?string
{
    if (!$phone) return null;

    $phone = preg_replace('/\D/', '', $phone);

    if (!$phone) return null;

    if (str_starts_with($phone, '62')) {
        return $phone;
    }

    if (str_starts_with($phone, '0')) {
        return '62' . substr($phone, 1);
    }

    if (str_starts_with($phone, '8')) {
        return '62' . $phone;
    }

    return $phone;
}
