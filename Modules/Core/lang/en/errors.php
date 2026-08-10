<?php

return [

    'statuses' => [
        'new' => 'New',
        'reviewed' => 'Reviewed',
        'resolved' => 'Resolved',
    ],

    'status' => [
        '401' => [
            'title' => 'Sign in required',
            'message' => 'You need to sign in before you can view this page.',
        ],
        '403' => [
            'title' => 'Access denied',
            'message' => 'You do not have permission to view this page.',
        ],
        '404' => [
            'title' => 'Page not found',
            'message' => 'The page you are looking for does not exist or has been moved.',
        ],
        '419' => [
            'title' => 'Session expired',
            'message' => 'Your session expired for security reasons. Please refresh and try again.',
        ],
        '429' => [
            'title' => 'Too many requests',
            'message' => 'You have made too many requests. Please wait a moment and try again.',
        ],
        '500' => [
            'title' => 'Something went wrong',
            'message' => 'A problem occurred while completing the operation. Try again, and contact the administration if it continues.',
        ],
        '503' => [
            'title' => 'Service unavailable',
            'message' => 'The system is temporarily unavailable for maintenance. Please try again shortly.',
        ],
    ],

    'actions' => [
        'home' => 'Back to home',
        'back' => 'Go back',
    ],

    'admin' => [
        'title' => 'System Errors',
        'subtitle' => 'Application errors recorded for review.',
        'updated' => 'Error status updated.',
        'empty' => 'No errors recorded. Everything looks healthy.',
        'search' => 'Search errors',
        'details' => 'Error details',
        'request' => 'Request information',
        'user' => 'User information',
        'exception' => 'Exception',
        'trace' => 'Stack trace',
        'traceHidden' => 'You do not have permission to view stack traces.',
        'occurrences' => 'Occurrences',
        'firstSeen' => 'First seen',
        'lastSeen' => 'Last seen',
        'markResolved' => 'Mark as resolved',
        'markReviewed' => 'Mark as reviewed',
        'markNew' => 'Reopen',
        'resolvedBy' => 'Resolved by',
        'guest' => 'Guest',
        'fields' => [
            'status' => 'Status',
            'level' => 'Level',
            'error' => 'Error',
            'route' => 'Route',
            'method' => 'Method',
            'user' => 'User',
            'occurredAt' => 'Occurred at',
            'requestId' => 'Request ID',
            'url' => 'URL',
            'ip' => 'IP address',
            'userAgent' => 'User agent',
            'file' => 'File',
            'line' => 'Line',
            'errorCode' => 'Error code',
        ],
    ],

];
