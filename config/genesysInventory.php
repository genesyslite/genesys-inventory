<?php

return [
    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `GenesysLite\GenesysFact\Models\User` contract.
         */

        'user' => GenesysLite\GenesysFact\Models\User::class,


    ],
];
