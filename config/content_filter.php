<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Từ bị chặn hoàn toàn
    |--------------------------------------------------------------------------
    | Gồm chửi tục, xúc phạm nặng, spam vô nghĩa.
    | Có từ nào quá gắt dễ chặn nhầm thì bỏ bớt sau.
    */

    'blocked_words' => [
        'dit',
        'dit me',
        'ditmemay',
        'dm',
        'dm',
        'dcm',
        'dmm',
        'dme',
        'đm',
        'đcm',
        'đmm',
        'đme',
        'deo',
        'dech',
        'vl',
        'vcl',
        'cl',
        'cc',
        'cac',
        'lon',
        'buoi',


        'địt',
        'địt mẹ',
        'địt mẹ mày',
        'đéo',
        'đếch',
        'vãi lồn',
        'cặc',
        'lồn',

        // xúc phạm cá nhân / chửi shop
        'ngu',
        'óc chó',
        'oc cho',
        'chó ngu',
        'cho ngu',
        'con chó',
        'con cho',
        'khùng',
        'đần',
        'ngu ngốc',
        'mất dạy',
        'mat day',
        'vô học',
        'vo hoc',
        'rác rưởi',
        'rac ruoi',
        'xàm l',
        'xam l',
        'tào lao',
        'tao lao',
        'cút',
        'cut',
        'biến',
        'bien',

        // tiếng Anh
        'fuck',
        'fck',
        'shit',
        'bitch',
        'asshole',
        'idiot',
        'stupid',
        'motherfucker',
        'mf',

        // spam / nội dung nhảm rất thường gặp
        'aaaaaaaa',
        'bbbbbbbb',
        'cccccccc',
        '111111',
        '123456',
        'test',
        'spam',
        'okokok',
        'kkkkkk',
        'hahahaahah',
    ],

    /*
    |--------------------------------------------------------------------------
    | Từ nhạy cảm / nghi vấn
    |--------------------------------------------------------------------------
    | Không chặn. Chỉ đánh dấu để admin chú ý.
    */

    'flag_words' => [
        'scam',
        'lua dao',
        'lừa đảo',
        'shop lua',
        'shop lừa',
        'shop lua dao',
        'shop lừa đảo',
        'hang gia',
        'hàng giả',
        'ban hang gia',
        'bán hàng giả',
        'fake',
        'gia mao',
        'giả mạo',
        'nhai',
        'kem chat luong',
        'kém chất lượng',
        'chat luong kem',
        'chất lượng kém',
        'khong chinh hang',
        'không chính hãng',
        'khong giong mo ta',
        'không giống mô tả',
        'treo dau de ban thit cho',
        'treo đầu dê bán thịt chó',
    ],
];