<?php

return [
    /*
     * Lower number = higher rank. Used to stop an inviter assigning a
     * role that outranks their own when inviting a new organisation member.
     */
    'ranks' => [
        'Super Admin' => 0,
        'Admin' => 1,
        'Manager' => 2,
        'Moderator' => 3,
        'Editor' => 4,
        'Analyst' => 5,
        'Support' => 6,
        'Viewer' => 7,
        'User' => 8,
        'Guest' => 9,
    ],
];
