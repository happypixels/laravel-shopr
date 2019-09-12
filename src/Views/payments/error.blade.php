<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ __('Payment Confirmation') }} - {{ config('app.name', 'Laravel') }}</title>

    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="font-sans text-gray-600 bg-gray-200 leading-normal p-4 h-full">
    <div id="app" class="h-full md:flex md:justify-center md:items-center">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-lg shadow-xl p-4 sm:py-6 sm:px-10 mb-5">
                
                <h1 class="text-xl mt-2 mb-4 text-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="flex-shrink-0 w-6 h-6 mr-2">
                        <path class="fill-current text-red-300" d="M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20z"/>
                        <path class="fill-current text-red-500" d="M12 18a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm1-5.9c-.13 1.2-1.88 1.2-2 0l-.5-5a1 1 0 0 1 1-1.1h1a1 1 0 0 1 1 1.1l-.5 5z"/>
                    </svg>

                    {{ trans('shopr::checkout.payment_failed') }}
                </h1>

                <p class="mb-6">{{ $message }}</p>

                <a href="{{ url('/') }}" class="inline-block w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 text-center text-gray-700 rounded-lg">
                    {{ __('Return to home page') }}
                </a>
            </div>

            <p class="text-center text-gray-500 text-sm">
                © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            </p>
        </div>
    </div>
</body>
</html>