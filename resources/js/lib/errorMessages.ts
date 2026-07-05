export interface ErrorMessage {
    title: string;
    description: string;
}

export const errorMessages: Record<number, ErrorMessage> = {
    400: {
        title: 'Bad Request',
        description: 'The server could not understand your request.',
    },
    401: {
        title: 'Unauthorised',
        description: 'You need to sign in to view this page.',
    },
    402: {
        title: 'Payment Required',
        description: 'Payment is required to access this resource.',
    },
    403: {
        title: 'Forbidden',
        description: 'You do not have permission to access this page.',
    },
    404: {
        title: 'Not Found',
        description: 'The page you are looking for could not be found.',
    },
    405: {
        title: 'Method Not Allowed',
        description: 'This request method is not supported for this page.',
    },
    406: {
        title: 'Not Acceptable',
        description:
            'The server cannot produce a response matching your request.',
    },
    407: {
        title: 'Proxy Authentication Required',
        description: 'Authentication with the proxy server is required.',
    },
    408: {
        title: 'Request Timeout',
        description: 'The server timed out waiting for your request.',
    },
    409: {
        title: 'Conflict',
        description:
            'This request conflicts with the current state of the resource.',
    },
    410: {
        title: 'Gone',
        description: 'This resource is no longer available.',
    },
    429: {
        title: 'Too Many Requests',
        description:
            'You have made too many requests. Please wait and try again.',
    },
    500: {
        title: 'Internal Server Error',
        description:
            'Something went wrong on our end. Please try again shortly.',
    },
    501: {
        title: 'Not Implemented',
        description: 'This functionality has not been implemented.',
    },
    502: {
        title: 'Bad Gateway',
        description:
            'The server received an invalid response from an upstream server.',
    },
    503: {
        title: 'Service Unavailable',
        description:
            'The service is temporarily unavailable. Please try again shortly.',
    },
    504: {
        title: 'Gateway Timeout',
        description: 'The upstream server did not respond in time.',
    },
    505: {
        title: 'HTTP Version Not Supported',
        description:
            'The server does not support the HTTP protocol version used in the request.',
    },
    508: {
        title: 'Loop Detected',
        description:
            'The server detected an infinite loop while processing your request.',
    },
};

export const defaultErrorMessage: ErrorMessage = {
    title: 'Unexpected Error',
    description: 'Something unexpected happened. Please try again.',
};
