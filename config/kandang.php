<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Durasi Masa Pemulihan Betina Pasca Melahirkan (Hari)
    |--------------------------------------------------------------------------
    |
    | Jumlah hari pemulihan bagi kambing betina setelah melahirkan sebelum
    | siap untuk dikawinkan kembali. Default: 60 hari.
    |
    */
    'masa_pemulihan_hari' => env('KANDANG_MASA_PEMULIHAN_HARI', 60),

    /*
    |--------------------------------------------------------------------------
    | Interval Rutin Semprot / Disinfeksi Kandang (Hari)
    |--------------------------------------------------------------------------
    |
    | Jumlah hari interval pengulangan otomatis untuk kegiatan penyemprotan
    | atau disinfeksi kandang. Default: 14 hari.
    |
    */
    'interval_semprot_kandang_hari' => env('KANDANG_INTERVAL_SEMPROT_HARI', 14),
];
