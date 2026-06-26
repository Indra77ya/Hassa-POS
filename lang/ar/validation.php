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

    'accepted' => 'يجب الموافقة على :attribute',
    'accepted_if' => 'يجب قبول :attribute في حالة :other يساوي :value.',
    'active_url' => 'الحقل attribute: ليست عنوان URL صالحًا.',
    'after' => 'يجب أن يكون الحقل attribute: تاريخًا بعد: date',
    'after_or_equal' => 'يجب أن يكون تاريخ :attribute بعد أو مساوياً لـ:date.',
    'alpha' => 'يجب أن يحتوي الحقل attribute: على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي :attribute فقط على الحروف، الأرقام، الشرطات، والشرطات السفلية.',
    'alpha_num' => 'يجب أن يحتوي الحقل attribute: على أرقام وحروف فقط.',
    'array' => 'يجب أن يكون الحقل attribute: مصفوفة.',
    'ascii' => 'يجب أن يحتوي :attribute فقط على أحرف أبجدية أرقام ورموز.',
    'before' => 'يجب أن يكون الحقل attribute: تاريخًا قبل: date',
    'before_or_equal' => 'يجب أن يكون الحقل attribute: تاريخًا أقدم من أو يساوي: date',
    'between' => [
        'array' => 'يجب أن يحتوي حقل :attribute على عدد من العناصر بين :min و :max.',
        'file' => 'يجب أن يكون حجم ملف attribute: بين min: و max: كيلوبايت .',
        'numeric' => 'يجب أن تتراوح قيمة attribute: بين: min و: max',
        'string' => 'يجب أن يحتوي النص بين: min و: max حرف.',
    ],
    'boolean' => 'يجب أن تكون خاصية الحقل attribute: صح أو خطأ.',
    'confirmed' => 'حقل التأكيد attribute: لا يتطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'الحقل attribute: ليس تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون :attribute مطابقاً للتاريخ :date.',
    'date_format' => 'لا يتطابق الحقل attribute: مع التنسيق: format',
    'decimal' => 'يجب أن يحتوي :attribute على :كسور عشرية في الأماكن العشرية.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض:attribute في حالة :other يساوي :value.',
    'different' => 'الحقل: attribute و: other يجب أن تكون مختلفة.',
    'digits' => 'يجب أن يحتوي الحقل  على: digits رقم(أرقام).',
    'digits_between' => 'يجب أن يحتوي الحقل  بين: min و: max رقم(أرقام).',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'يحتوي الحقل: attribute على قيمة مكررة.',
    'doesnt_end_with' => 'الحقل :attribute يجب ألّا ينتهي بأحد القيم التالية: :values.',
    'doesnt_start_with' => 'الحقل :attribute يجب ألّا يبدأ بأحد القيم التالية: values.',
    'email' => 'يجب أن يكون الحقل attribute: عنوان بريد إلكتروني صالحًا.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum' => 'الحقل attribute: المحدد غير صالح.',
    'exists' => 'الحقل attribute: المحدد غير صالح.',
    'file' => 'يجب أن يكون الحقل attribute: ملفًا.',
    'filled' => 'يجب أن يحتوي الحقل attribute: على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر/عنصر.',
        'file' => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'string' => 'يجب أن يكون طول نّص حقل :attribute أكثر من :value حروفٍ/حرفًا.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي :attribute على الأقل على :value عُنصرًا/عناصر.',
        'file' => ':attribute يجب أن يكون أكبر أو يساوي :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أكبر من أو يساوي: value.',
        'string' => ':attribute يجب أن يكون أكبر من أو يساوي :value احرف.',
    ],
    'image' => 'يجب أن يكون الحقل attribute: صورة.',
    'in' => 'الحقل: attribute غير صالح.',
    'in_array' => 'الحقل: attribute غير موجود في: other',
    'integer' => 'يجب أن يكون الحقل: attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون الحقل attribute: عنوان IP صالح.',
    'ipv4' => 'يجب أن يكون الحقل attribute: عنوان IPv4 صالح.',
    'ipv6' => 'يجب أن يكون الحقل attribute: عنوان IPv6 صالح.',
    'json' => 'يجب أن يكون الحقل attribute: مستند JSON صالحً.',
    'lowercase' => 'يجب أن يكون الحقل attribute: حروف إنجليزية صغيرة.',
    'lt' => [
        'array' => ':attribute يجب أن يتكون من أقل من :value عناصر.',
        'file' => 'يجب أن يكون حجم الملف :attribute أصغر من :value كيلوبايت.',
        'numeric' => ':attribute يجب أن يكون أقل من :value.',
        'string' => ':attribute يجب أن يكون أقل من :value حروف/أرقام.',
    ],
    'lte' => [
        'array' => 'يجب أن لا يحتوي حقل :attribute على أكثر من :value عناصر/عنصر.',
        'file' => ':attribute يجب أن يكون أصغر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :value.',
        'string' => ':attribute يجب أن يكون أصغر من أو يساوي :value احرف.',
    ],
    'mac_address' => 'الحقل :attribute يجب أن يكون عنوان MAC صالحاً.',
    'max' => [
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'numeric' => 'لا يمكن أن تكون قيمة attribute: أكبر من max:.',
        'string' => 'لا يمكن أن يحتوي النص attribute: على أكثر من max: حرف (أحرف).',
    ],
    'max_digits' => 'يجب أن لا يكون :attribute أكثر من :max رقم/أرقام.',
    'mimes' => 'يجب أن يكون الحقل attribute: ملفًا من نوع values:.',
    'mimetypes' => 'يجب أن يكون الحقل attribute: ملفًا من نوع values:.',
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عُنصرًا/عناصر',
        'file' => 'يجب أن يكون :attribute على الأقل :min كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة attribute: أكبر من أو تساوي min:.',
        'string' => 'يجب أن يحتوي النص  على الأقل min: حرف (أحرف).',
    ],
    'min_digits' => 'يجب أن يحتوي الحقل :attribute على الأقل :min رقم/أرقام.',
    'multiple_of' => 'حقل :attribute يجب أن يكون من مضاعفات :value.',
    'not_in' => 'الحقل attribute: المحدد غير صالح.',
    'not_regex' => 'نسق الحقل attribute: غير صالح.',
    'numeric' => 'يجب أن يحتوي الحقل attribute: على رقم',
    'password' => [
        'letters' => ':attribute يجب أن يحتوي على الأقل حرف واحد.',
        'mixed' => 'يجب أن يحتوي حقل :attribute على حرف إنجليزي كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي حقل :attribute على رقمٍ واحدٍ على الأقل.',
        'symbols' => ':attribute يجب أن يحتوي على الأقل رمز واحد.',
        'uncompromised' => 'حقل :attribute ظهر في بيانات مُسربة. الرجاء اختيار :attribute مختلف.',
    ],
    'present' => 'يجب أن يكون الحقل attribute: موجود.',
    'prohibited' => 'الحقل :attribute ممنوع.',
    'prohibited_if' => 'الحقل :attribute ممنوع عندما :other يكون :value.',
    'prohibited_unless' => 'الحقل :attribute غير مسموح به ما لم يكن :other في :values.',
    'prohibits' => 'الحقل :attribute يمنع :other من أن تكون موجودة.',
    'regex' => 'نسق الحقل attribute: غير صالح.',
    'required' => ':attribute مطلوب.',
    'required_array_keys' => 'الحقل :attribute يجب أن يحتوي على مدخلات لـ: :values.',
    'required_if' => 'الحقل attribute: إلزامي عندما تكون قيمة other: هي value:.',
    'required_if_accepted' => 'الحقل :attribute مطلوب عند قبول الحقل :other.',
    'required_unless' => 'الحقل attribute: إلزامي ما لم other: هو values:.',
    'required_with' => 'الحقل attribute: إلزامي عندما تكون values: موجودة.',
    'required_with_all' => 'الحقل attribute: إلزامي عندما تكون values: موجودة',
    'required_without' => 'الحقل attribute: إلزامي عندما تكون values: غير موجودة',
    'required_without_all' => 'الحقل attribute: إلزامي عندما لاتوجد أي من القيم التالية: values',
    'same' => 'الحقول: attribute و: other يجب أن تكون هي نفسها.',
    'size' => [
        'array' => 'يجب أن يحتوي :attribute على :size عنصر / عناصر.',
        'file' => ':attribute يجب أن يكون  :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة: attribute هي: size',
        'string' => 'يجب أن يحتوي النص attribute: على size: حرف (أحرف).',
    ],
    'starts_with' => ':attribute يجب أن يبدأ بأحد التالي: values',
    'string' => 'يجب أن يكون حقل :attribute نصًا.',
    'timezone' => 'يجب أن يكون الحقل attribute: منطقة زمنية صالحة.',
    'unique' => 'قيمة الحقل attribute: مستخدمة بالفعل.',
    'uploaded' => 'فشل التحميل :attribute .',
    'uppercase' => 'يجب أن يكون الحقل attribute: حروف إنجليزية كبيرة.',
    'url' => 'يجب أن يكون :attribute رابط URL صالحًا.',
    'ulid' => ':attribute يجب أن يكون بصيغة UUID سليمة.',
    'uuid' => ':attribute يجب أن يكون بصيغة UUID سليمة.',
    'indisposable' => 'هذا البريد الإلكتروني غير مسموح به.',

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
            'rule-name' => 'رسالة مخصصة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

     'attributes' => [
        'name' => 'الإسم',
        'username' => 'إسم المستخدم',
        'email' => 'عنوان البريد الالكتروني',
        'first_name' => 'الإسم',
        'last_name' => 'الإسم الاخير',
        'password' => 'كلمة السر',
        'password_confirmation' => 'تأكيد كلمة السر',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'address' => 'العنوان',
        'phone' => 'الهاتف',
        'mobile' => 'الموبايل',
        'age' => 'السن',
        'sex' => 'الجنس',
        'gender' => 'النوع',
        'day' => 'يوم',
        'month' => 'شهر',
        'year' => 'عام',
        'hour' => 'ساعة',
        'minute' => 'دقيقة',
        'second' => 'ثانية',
        'title' => 'العنوان',
        'content' => 'المحتوى',
        'description' => 'الوصف',
        'excerpt' => 'مقتطف',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'available' => 'متاح',
        'size' => 'الحجم',
    ],

    'custom-messages' => [
        'quantity_not_available' => 'الكمية :qty :unit متاحة',
        'this_field_is_required' => 'هذا الحقل مطلوب',
    ],

];
