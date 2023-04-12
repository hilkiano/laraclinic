<?php

use Dbt\Blame\Observer;

return [
    /*
     * The observer that listens for model events. If you want to swap out the
     * observer implementation, you can replace this reference.
     */
    'observer' => Observer::class,

    /*
     * Add your authenticatable reference here. This is often \App\User::class.
     */
    'user' => [
        'model' => \App\Models\Users::class,
        'primary_key' => 'id',
        'default_id' => null,
    ],

    /*
     * Add each model you want observed to this array. The service provider will
     * automatically register the observer for you.
     */
    'models' => [
        \App\Models\Groups::class,
        \App\Models\Menus::class,
        \App\Models\Patients::class,
        \App\Models\Roles::class,
        \App\Models\Users::class,
        \App\Models\Appointments::class,
        \App\Models\AppointmentsDetail::class,
        \App\Models\PatientPotraits::class,
        \App\Models\MedicalRecord::class,
        \App\Models\Prescription::class,
        \App\Models\Medicine::class,
        \App\Models\Services::class,
        \App\Models\Transaction::class
    ],

    /*
     * If you need to change the column names, you can do that here. This
     * associative array lists $eventName => $columnName.
     */
    'columns' => [
        'creating' => 'created_by',
        'updating' => 'updated_by',
        'deleting' => 'deleted_by',
    ]
];
