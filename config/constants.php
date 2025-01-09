<?php

return [
    'reason'    => [
        'doctor'     => 'DOCTOR',
        'pharmacy'   => 'PHARMACY',
    ],
    'status'    => [
        'doctor_waiting'     => 'DOC_WAITING',
        'doctor_assigned'    => 'DOC_ASSIGNED',
        'pharmacy_waiting'   => 'PHAR_WAITING',
        'pharmacy_assigned'  => 'PHAR_ASSIGNED',
        'payment_waiting'    => 'PAYMENT_WAITING',
        'payment_assigned'   => 'IN_PAYMENT',
        'completed'          => 'COMPLETED',
        'canceled'           => 'CANCELED'
    ],
    'group'     => [
        'superadmin'    => 1,
        'receptionist'  => 2,
        'doctor'        => 3,
        'pharmacy'      => 4,
        'cashier'       => 5,
        'olshop'        => 6,
        'owner'         => 7,
        'doctor-stock' => 8,
        'olshop-stock' => 9,
        'cashier-stock' => 10
    ]
];
