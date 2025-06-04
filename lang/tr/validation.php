<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Validation Language Lines - Türkçe
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki satırlar validation kuralları için kullanılan hata mesajlarını
    | içerir. Bazı kuralların birden fazla versiyonu vardır.
    |
    */

	'accepted'             => ':attribute kabul edilmelidir.',
	'accepted_if'          => ':other :value olduğunda :attribute kabul edilmelidir.',
	'active_url'           => ':attribute geçerli bir URL değil.',
	'after'                => ':attribute :date tarihinden sonra olmalıdır.',
	'after_or_equal'       => ':attribute :date tarihinden sonra veya aynı tarih olmalıdır.',
	'alpha'                => ':attribute sadece harflerden oluşmalıdır.',
	'alpha_dash'           => ':attribute sadece harfler, rakamlar ve tirelerden oluşmalıdır.',
	'alpha_num'            => ':attribute sadece harfler ve rakamlardan oluşmalıdır.',
	'array'                => ':attribute bir dizi olmalıdır.',
	'before'               => ':attribute :date tarihinden önce olmalıdır.',
	'before_or_equal'      => ':attribute :date tarihinden önce veya aynı tarih olmalıdır.',
	'between'              => [
		'numeric' => ':attribute :min - :max arasında olmalıdır.',
		'file'    => ':attribute :min - :max KB arasında olmalıdır.',
		'string'  => ':attribute :min - :max karakter arasında olmalıdır.',
		'array'   => ':attribute :min - :max adet öğe içermelidir.',
	],
	'boolean'              => ':attribute sadece doğru veya yanlış olabilir.',
	'confirmed'            => ':attribute onayı eşleşmiyor.',
	'current_password'     => 'Şifre yanlış.',
	'date'                 => ':attribute geçerli bir tarih değil.',
	'date_equals'          => ':attribute :date tarihine eşit olmalıdır.',
	'date_format'          => ':attribute :format formatı ile eşleşmiyor.',
	'different'            => ':attribute ile :other farklı olmalıdır.',
	'digits'               => ':attribute :digits rakamından oluşmalıdır.',
	'digits_between'       => ':attribute :min ile :max rakamları arasında olmalıdır.',
	'dimensions'           => ':attribute geçersiz resim boyutlarına sahip.',
	'distinct'             => ':attribute alanında mükerrer değer var.',
	'email'                => ':attribute geçerli bir email adresi olmalıdır.',
	'ends_with'            => ':attribute şunlardan biriyle bitmelidir: :values.',
	'exists'               => 'Seçili :attribute geçersiz.',
	'file'                 => ':attribute bir dosya olmalıdır.',
	'filled'               => ':attribute alanında değer olmalıdır.',
	'gt'                   => [
		'numeric' => ':attribute :value değerinden büyük olmalıdır.',
		'file'    => ':attribute :value KB\'den büyük olmalıdır.',
		'string'  => ':attribute :value karakterden fazla olmalıdır.',
		'array'   => ':attribute :value adetten fazla öğe içermelidir.',
	],
	'gte'                  => [
		'numeric' => ':attribute :value değerinden büyük veya eşit olmalıdır.',
		'file'    => ':attribute :value KB\'den büyük veya eşit olmalıdır.',
		'string'  => ':attribute :value karakterden fazla veya eşit olmalıdır.',
		'array'   => ':attribute :value adetten fazla veya eşit öğe içermelidir.',
	],
	'image'                => ':attribute bir resim olmalıdır.',
	'in'                   => 'Seçili :attribute geçersiz.',
	'in_array'             => ':attribute alanı :other içinde mevcut değil.',
	'integer'              => ':attribute tam sayı olmalıdır.',
	'ip'                   => ':attribute geçerli bir IP adresi olmalıdır.',
	'ipv4'                 => ':attribute geçerli bir IPv4 adresi olmalıdır.',
	'ipv6'                 => ':attribute geçerli bir IPv6 adresi olmalıdır.',
	'json'                 => ':attribute geçerli bir JSON metni olmalıdır.',
	'lt'                   => [
		'numeric' => ':attribute :value değerinden küçük olmalıdır.',
		'file'    => ':attribute :value KB\'den küçük olmalıdır.',
		'string'  => ':attribute :value karakterden az olmalıdır.',
		'array'   => ':attribute :value adetten az öğe içermelidir.',
	],
	'lte'                  => [
		'numeric' => ':attribute :value değerinden küçük veya eşit olmalıdır.',
		'file'    => ':attribute :value KB\'den küçük veya eşit olmalıdır.',
		'string'  => ':attribute :value karakterden az veya eşit olmalıdır.',
		'array'   => ':attribute :value adetten az veya eşit öğe içermelidir.',
	],
	'max'                  => [
		'numeric' => ':attribute :max değerinden büyük olmamalıdır.',
		'file'    => ':attribute :max KB\'den büyük olmamalıdır.',
		'string'  => ':attribute :max karakterden fazla olmamalıdır.',
		'array'   => ':attribute :max adetten fazla öğe içermemelidir.',
	],
	'mimes'                => ':attribute şu dosya tiplerinden biri olmalıdır: :values.',
	'mimetypes'            => ':attribute şu dosya tiplerinden biri olmalıdır: :values.',
	'min'                  => [
		'numeric' => ':attribute en az :min olmalıdır.',
		'file'    => ':attribute en az :min KB olmalıdır.',
		'string'  => ':attribute en az :min karakter olmalıdır.',
		'array'   => ':attribute en az :min öğe içermelidir.',
	],
	'multiple_of'          => ':attribute :value değerinin katı olmalıdır.',
	'not_in'               => 'Seçili :attribute geçersiz.',
	'not_regex'            => ':attribute formatı geçersiz.',
	'numeric'              => ':attribute sayı olmalıdır.',
	'password'             => 'Şifre yanlış.',
	'present'              => ':attribute alanı mevcut olmalıdır.',
	'regex'                => ':attribute formatı geçersiz.',
	'required'             => ':attribute alanı gereklidir.',
	'required_if'          => ':other :value olduğunda :attribute alanı gereklidir.',
	'required_unless'      => ':other :values değerlerinden biri olmadığında :attribute alanı gereklidir.',
	'required_with'        => ':values mevcut olduğunda :attribute alanı gereklidir.',
	'required_with_all'    => ':values değerlerinin tümü mevcut olduğunda :attribute alanı gereklidir.',
	'required_without'     => ':values mevcut olmadığında :attribute alanı gereklidir.',
	'required_without_all' => ':values değerlerinin hiçbiri mevcut olmadığında :attribute alanı gereklidir.',
	'same'                 => ':attribute ile :other eşleşmelidir.',
	'size'                 => [
		'numeric' => ':attribute :size olmalıdır.',
		'file'    => ':attribute :size KB olmalıdır.',
		'string'  => ':attribute :size karakter olmalıdır.',
		'array'   => ':attribute :size öğe içermelidir.',
	],
	'starts_with'          => ':attribute şunlardan biriyle başlamalıdır: :values.',
	'string'               => ':attribute metin olmalıdır.',
	'timezone'             => ':attribute geçerli bir saat dilimi olmalıdır.',
	'unique'               => ':attribute daha önce alınmış.',
	'uploaded'             => ':attribute yüklenemedi.',
	'url'                  => ':attribute geçerli bir URL olmalıdır.',
	'uuid'                 => ':attribute geçerli bir UUID olmalıdır.',

	/*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Bu bölümde özel validation mesajları tanımlayabilirsiniz.
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
    | Aşağıdaki satırlar validation mesajlarında kullanılan alan adlarını
    | daha okunabilir hale getirmek için kullanılır.
    |
    */

	'attributes' => [
		'title'             => 'başlık',
		'content'           => 'içerik',
		'image'             => 'görsel',
		'slug'              => 'URL kısa adı',
		'status'            => 'durum',
		'summary'           => 'özet',
		'author'            => 'yazar',
		'tags'              => 'etiketler',
		'published_at'      => 'yayın tarihi',
		'email'             => 'e-posta',
		'password'          => 'şifre',
		'name'              => 'ad',
		'first_name'        => 'ad',
		'last_name'         => 'soyad',
		'phone'             => 'telefon',
		'address'           => 'adres',
		'city'              => 'şehir',
		'country'           => 'ülke',
		'date'              => 'tarih',
		'time'              => 'saat',
		'available'         => 'mevcut',
		'size'              => 'boyut',
	],
];
