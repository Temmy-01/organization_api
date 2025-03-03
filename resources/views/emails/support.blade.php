<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Email</title>
    </head>
    <body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        padding: 50px 30px">
        Dear <b>Admin,</b><br />

        {{-- <p>
            Your password has been successfully reset by an Admin. Kindly find your
            new login credentials below:<br />
        </p> --}}
        <p style="line-height: 1.5">
            {{$email_data['message']}}
        </p>

        Thanks,<br>
        {{ $email_data['name'] }}
    </body>
</html>

{{-- @endcomponent --}}