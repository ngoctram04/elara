<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute phải được chấp nhận.',
    'active_url' => ':attribute không phải là URL hợp lệ.',
    'after' => ':attribute phải là ngày sau :date.',
    'after_or_equal' => ':attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':attribute chỉ được chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới.',
    'alpha_num' => ':attribute chỉ được chứa chữ cái và số.',
    'array' => ':attribute phải là một mảng.',

    'before' => ':attribute phải là ngày trước :date.',
    'before_or_equal' => ':attribute phải là ngày trước hoặc bằng :date.',

    'between' => [
        'numeric' => ':attribute phải nằm trong khoảng :min - :max.',
        'file' => ':attribute phải có dung lượng từ :min đến :max KB.',
        'string' => ':attribute phải có độ dài từ :min đến :max ký tự.',
        'array' => ':attribute phải có từ :min đến :max phần tử.',
    ],

    'boolean' => ':attribute phải là true hoặc false.',
    'confirmed' => ':attribute xác nhận không khớp.',
    'date' => ':attribute không phải là ngày hợp lệ.',
    'date_equals' => ':attribute phải bằng ngày :date.',
    'date_format' => ':attribute không đúng định dạng :format.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải có :digits chữ số.',
    'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
    'email' => ':attribute không đúng định dạng email.',
    'ends_with' => ':attribute phải kết thúc bằng một trong các giá trị: :values.',
    'exists' => ':attribute không tồn tại.',
    'file' => ':attribute phải là một tập tin.',
    'filled' => ':attribute không được để trống.',

    'gt' => [
        'numeric' => ':attribute phải lớn hơn :value.',
        'file' => ':attribute phải lớn hơn :value KB.',
        'string' => ':attribute phải có nhiều hơn :value ký tự.',
        'array' => ':attribute phải có nhiều hơn :value phần tử.',
    ],

    'gte' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value KB.',
        'string' => ':attribute phải có ít nhất :value ký tự.',
        'array' => ':attribute phải có ít nhất :value phần tử.',
    ],

    'image' => ':attribute phải là hình ảnh.',
    'in' => ':attribute không hợp lệ.',
    'integer' => ':attribute phải là số nguyên.',
    'ip' => ':attribute phải là địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là chuỗi JSON hợp lệ.',

    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'file' => ':attribute không được vượt quá :max KB.',
        'string' => ':attribute không được vượt quá :max ký tự.',
        'array' => ':attribute không được vượt quá :max phần tử.',
    ],

    'min' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
        'file' => ':attribute phải có dung lượng ít nhất :min KB.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
        'array' => ':attribute phải có ít nhất :min phần tử.',
    ],

    'not_in' => ':attribute không hợp lệ.',
    'numeric' => ':attribute phải là số.',
    'present' => ':attribute phải tồn tại.',
    'required' => ':attribute không được để trống.',
    'required_if' => ':attribute không được để trống khi :other là :value.',
    'required_unless' => ':attribute không được để trống trừ khi :other là :values.',
    'required_with' => ':attribute không được để trống khi có :values.',
    'required_with_all' => ':attribute không được để trống khi có tất cả :values.',
    'required_without' => ':attribute không được để trống khi không có :values.',
    'required_without_all' => ':attribute không được để trống khi không có tất cả :values.',
    'same' => ':attribute và :other phải giống nhau.',

    'size' => [
        'numeric' => ':attribute phải bằng :size.',
        'file' => ':attribute phải có dung lượng :size KB.',
        'string' => ':attribute phải có :size ký tự.',
        'array' => ':attribute phải có :size phần tử.',
    ],

    'string' => ':attribute phải là chuỗi.',
    'timezone' => ':attribute phải là múi giờ hợp lệ.',
    'unique' => ':attribute đã tồn tại.',
    'url' => ':attribute không đúng định dạng URL.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'Họ tên',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'phone' => 'Số điện thoại',
        'address' => 'Địa chỉ',
    ],

];