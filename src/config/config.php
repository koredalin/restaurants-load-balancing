<?php

return [
    'restaurants' => [
        [9, 'Хепи Бъкстон', '42.667122', '23.281657'],
        [10, 'Хепи Виктория', '42.688600', '23.308027'],
        [11, 'Хепи Саут Парк', '42.670071', '23.313399'],
        [12, 'Хепи Будапеща', '42.692017', '23.326259'],
        [13, 'Хепи Мол София', '42.6982608', '23.3078595'],
        [14, 'Хепи Младост', '42.6481687', '23.3793724'],
        [15, 'Хепи Света Неделя', '42.696606', '23.3204766'],
        [18, 'Хепи Люлин', '42.713895', '23.264476'],
        [28, 'Хепи Парадайс', '42.6570524', '23.3142243'],
        [30, 'Хепи Банкя', '42.7073002', '23.1418126'],
        [109, 'Happy Изток', '42.673136', '23.348732'],
    ],
    'minDriversPerRestaurant' => 0,
    'maxDriversPerRestaurant' => 7,
    'minOrdersPerRestaurant' => 0,
    'maxOrdersPerRestaurant' => 20,
    'driverMaxTransfers' => 1,
    'maxLoadCascades' => 3,
    'driverMaxTransferDistanceInMeters' => 6000,
    /**
     * If we found that there is useless transfers (A transfer from restaurant in need.) on the first estimations block..
     * These restaurants are skipped from the estimations.
     * But the estimations and transfers continue.
     * So, at later moment there could have better possible driver transfers.
     * If set a number less then 2 - only one global iteration will be made.
     */
    'restaurantsWithExcessDriversМaxGlobalIterations' => 1,
];
