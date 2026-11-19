<?php

// Koordinat pusat wajib dikonfigurasi pada backend setelah dikonfirmasi pemilik.
// Kosong/tidak valid berarti endpoint absensi menolak pencatatan (fail closed).
$latitude = env('CAFE_LOCATION_LATITUDE');
$longitude = env('CAFE_LOCATION_LONGITUDE');

return [
    'geofence' => [
        'latitude' => is_numeric($latitude) ? (float) $latitude : null,
        'longitude' => is_numeric($longitude) ? (float) $longitude : null,
        'radius_meters' => 30.0,
    ],
];
