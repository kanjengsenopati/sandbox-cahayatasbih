<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Terms and Conditions</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-top: 0;
        }

        p {
            margin: 0 0 1em;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 16px;
            }

            .container {
                padding: 0 5px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        {!! $data->terms_and_conditions ?? '' !!}
    </div>
</body>

</html>