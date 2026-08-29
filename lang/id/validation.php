<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, dan tanda strip.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa sebuah array.',
    'before' => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus bernilai antara :min dan :max.',
        'file' => ':attribute harus berukuran antara :min dan :max kilobita.',
        'string' => ':attribute harus berisi antara :min dan :max karakter.',
        'array' => ':attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean' => 'Bidang :attribute harus bernilai true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus berupa :digits digit.',
    'digits_between' => ':attribute harus berukuran antara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Bidang :attribute memiliki nilai yang duplikat.',
    'email' => ':attribute harus berupa alamat surel yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'file' => ':attribute harus berupa sebuah berkas.',
    'filled' => 'Bidang :attribute harus memiliki nilai.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => 'Bidang :attribute tidak ada di :other.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa string JSON yang valid.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'file' => ':attribute tidak boleh lebih besar dari :max kilobita.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes' => ':attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'numeric' => ':attribute harus minimal :min.',
        'file' => ':attribute harus minimal :min kilobita.',
        'string' => ':attribute harus minimal :min karakter.',
        'array' => ':attribute harus memiliki minimal :min item.',
    ],
    'not_in' => ':attribute yang dipilih tidak valid.',
    'numeric' => ':attribute harus berupa angka.',
    'present' => 'Bidang :attribute harus ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Bidang :attribute wajib diisi.',
    'required_if' => 'Bidang :attribute wajib diisi bila :other adalah :value.',
    'required_unless' => 'Bidang :attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with' => 'Bidang :attribute wajib diisi bila terdapat :values.',
    'required_with_all' => 'Bidang :attribute wajib diisi bila terdapat :values.',
    'required_without' => 'Bidang :attribute wajib diisi bila tidak terdapat :values.',
    'required_without_all' => 'Bidang :attribute wajib diisi bila tidak terdapat satupun :values.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'numeric' => ':attribute harus berukuran :size.',
        'file' => ':attribute harus berukuran :size kilobita.',
        'string' => ':attribute harus berisi :size karakter.',
        'array' => ':attribute harus mengandung :size item.',
    ],
    'string' => ':attribute harus berupa string.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah ada sebelumnya.',
    'uploaded' => ':attribute gagal diunggah.',
    'url' => 'Format :attribute tidak valid.',
    'indisposable' => 'Email ini tidak diizinkan.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],
    'custom-messages' => [
        'quantity_not_available' => 'Hanya tersedia :qty :unit',
        'this_field_is_required' => 'Bidang ini wajib diisi',
    ],



    'accepted_if' => 'Field :attribute harus diterima ketika :other adalah :value.',
    'ascii' => 'Field :attribute hanya boleh berisi karakter alfanumerik dan simbol single-byte.',
    'current_password' => 'Kata sandi salah.',
    'date_equals' => 'Field :attribute harus berupa tanggal yang sama dengan :date.',
    'decimal' => 'Field :attribute harus memiliki :decimal tempat desimal.',
    'declined' => 'Field :attribute harus ditolak.',
    'declined_if' => 'Field :attribute harus ditolak ketika :other adalah :value.',
    'doesnt_end_with' => 'Field :attribute tidak boleh diakhiri dengan salah satu dari berikut ini: :values.',
    'doesnt_start_with' => 'Field :attribute tidak boleh diawali dengan salah satu dari berikut ini: :values.',
    'ends_with' => 'Field :attribute harus diakhiri dengan salah satu dari berikut ini: :values.',
    'enum' => 'Field :attribute yang dipilih tidak valid.',
    'gt' => [
        'numeric' => 'Field :attribute harus lebih besar dari :value.',
        'file' => 'Field :attribute harus lebih besar dari :value kilobita.',
        'string' => 'Field :attribute harus lebih besar dari :value karakter.',
        'array' => 'Field :attribute harus memiliki lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => 'Field :attribute harus lebih besar dari atau sama dengan :value.',
        'file' => 'Field :attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'string' => 'Field :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array' => 'Field :attribute harus memiliki :value item atau lebih.',
    ],
    'lowercase' => 'Field :attribute harus berupa huruf kecil.',
    'lt' => [
        'numeric' => 'Field :attribute harus kurang dari :value.',
        'file' => 'Field :attribute harus kurang dari :value kilobita.',
        'string' => 'Field :attribute harus kurang dari :value karakter.',
        'array' => 'Field :attribute harus memiliki kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => 'Field :attribute harus kurang dari atau sama dengan :value.',
        'file' => 'Field :attribute harus kurang dari atau sama dengan :value kilobita.',
        'string' => 'Field :attribute harus kurang dari atau sama dengan :value karakter.',
        'array' => 'Field :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'mac_address' => 'Field :attribute harus berupa alamat MAC yang valid.',
    'max_digits' => 'Field :attribute tidak boleh memiliki lebih dari :max digit.',
    'min_digits' => 'Field :attribute harus memiliki setidaknya :min digit.',
    'multiple_of' => 'Field :attribute harus merupakan kelipatan dari :value.',
    'not_regex' => 'Format :attribute tidak valid.',
    'password' => [
        'letters' => 'Field :attribute harus mengandung setidaknya satu huruf.',
        'mixed' => 'Field :attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Field :attribute harus mengandung setidaknya satu angka.',
        'symbols' => 'Field :attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => 'Field :attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
    ],
    'prohibited' => 'Field :attribute dilarang.',
    'prohibited_if' => 'Field :attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => 'Field :attribute dilarang kecuali :other ada di dalam :values.',
    'prohibits' => 'Field :attribute melarang :other untuk hadir.',
    'required_array_keys' => 'Field :attribute harus berisi entri untuk: :values.',
    'required_if_accepted' => 'Field :attribute diperlukan bila :other diterima.',
    'starts_with' => 'Field :attribute harus diawali dengan salah satu dari berikut ini: :values.',
    'ulid' => 'Field :attribute harus berupa ULID yang valid.',
    'uppercase' => 'Field :attribute harus berupa huruf besar.',
    'uuid' => 'Field :attribute harus berupa UUID yang valid.',
];
