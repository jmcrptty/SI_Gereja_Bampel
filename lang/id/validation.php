<?php

/**
 * Pesan validasi Bahasa Indonesia untuk aturan yang dipakai di dashboard admin.
 * Aturan lain yang tidak ada di sini memakai pesan bawaan (Inggris).
 */
return [
    'array' => ':Attribute harus berupa daftar.',
    'between' => [
        'numeric' => ':Attribute harus antara :min dan :max.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus berformat :format.',
    'dimensions' => 'Resolusi :attribute terlalu besar. Maksimal 6000 × 6000 piksel; perkecil foto terlebih dahulu.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'max' => [
        'array' => ':Attribute maksimal berisi :max item.',
        'file' => ':Attribute maksimal :max kilobyte.',
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa file bertipe: :values.',
    'min' => [
        'array' => ':Attribute minimal berisi :min item.',
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'required' => ':Attribute wajib diisi.',
    'required_if' => ':Attribute wajib diisi jika :other adalah :value.',
    'url' => ':Attribute harus berupa link yang valid.',

    'attributes' => [
        'nama_gereja' => 'nama paroki',
        'foto_hero' => 'foto hero',
        'foto_gereja' => 'foto gereja',
        'foto_pelindung' => 'foto pelindung',
        'kisah_pelindung' => 'kisah pelindung',
        'sejarah_singkat' => 'sejarah singkat',
        'link_maps' => 'link Google Maps',
        'jam_sekretariat' => 'jam sekretariat',
        'nama_perayaan' => 'nama perayaan',
        'file_pdf' => 'file PDF',
        'jumlah_kk' => 'jumlah KK',
        'jadwal_doa' => 'jadwal doa',
        'cakupan_jalan' => 'cakupan jalan',
        'fotos' => 'foto',
        'fotos.*' => 'foto',
    ],
];
