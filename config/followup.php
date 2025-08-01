<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Follow-Up Stages & Intervals (days)
    |--------------------------------------------------------------------------
    |
    | Each key is a reminder “stage” number. The value is how many days after
    | the previous date (initially date_received) the next follow-up should run.
    |
    */
    'stages' => [
        1 => 2,   // first reminder: 2 days after date_received
        2 => 5,   // second reminder: 5 days after the first
        3 => 7,   // third reminder: 7 days later
        // add more stages if you wish
    ],

    /*
    |--------------------------------------------------------------------------
    | Follow-Up Message Template
    |--------------------------------------------------------------------------
    |
    | Placeholders will be replaced at runtime:
    | :stage          → the stage number (1, 2, 3…)
    | :type           → “Carpet” or “Laundry”
    | :uniqueid       → the record’s unique identifier
    | :date_received  → YYYY-MM-DD when the item was brought in
    | :outstanding    → the outstanding balance
    | :client_phone   → the customer’s phone number
    | :link           → a URL back to the admin edit page
    |
    */
    'message' =>
        "Reminder #:stage for :type #:uniqueid\n" .
        "Received: :date_received\n" .
        "Outstanding: :outstanding\n" .
        "Client phone: :client_phone\n" .
        "Action: :link",
];
