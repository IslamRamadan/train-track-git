<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }

        .email-container {
            max-width: 600px;
            background: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-logo {
            max-width: 100px;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            margin: 8px;
            color: white;
            min-width: 180px;
            text-align: center;
            transition: background 0.3s ease-in-out, transform 0.2s;
        }

        .google-btn {
            background-color: #4285F4;
        }

        .apple-btn {
            background-color: #000;
        }

        .whatsapp-btn {
            background-color: #25D366;
        }

        .youtube-btn {
            background-color: #FF0000;
        }

        .button:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        .divider {
            width: 60%;
            margin: 30px auto;
            border: 1px solid #ddd;
        }

        .w-100 {
            width: 100%;
        }
        .color-white{
            color: white;
        }
    </style>
    <!-- Bootstrap CSS -->
    <link href="{{asset('email/bootstrap.min.css')}}" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="{{asset('email/all.min.css')}}">
    <link href="{{asset('email/bootstrap2.min.css')}}" rel="stylesheet" id="bootstrap-css">
    <script src="{{asset('email/bootstrap.min.js')}}"></script>
    <script src="{{asset('email/fontawesome.min.css')}}"></script>
</head>
<body>

<div class="email-container">
    <!-- Header Section -->
    <div class="text-center">
        <img src="{{ url('images/logos/logo.png') }}" alt="App Logo" class="header-logo">
    </div>

    <p>Dear Coach <strong>{{ $name }}</strong>,</p>

    <p>
        Welcome to <strong>TrainTrack Coach Application!</strong><br>
        This is a verification mail so please press the verify button to start using the application
    </p>
    <hr>
    <div class="text-center">
        <a href="{{route('coach.verify.email',Crypt::encrypt($user_id))}}"
           class="button apple-btn w-100 color-white">Verify You Email</a>
    </div>
    <hr>
    <p>Best regards,</p>
    <p><strong>Mohamed Ayman</strong><br>Your Support at TrainTrack Coach Application</p>

    <hr class="divider">
    <!-- Contact Section -->
    <h4 class="section-title">Stay Connected</h4>
    <p>Contact us on WhatsApp or follow our YouTube channel where you can find presentation for the app..</p>
    <a href="https://wa.me/+201140066441" target="_blank" class="button whatsapp-btn color-white">
        <i class="fab fa-whatsapp"></i> WhatsApp
    </a>
    <a href="https://www.youtube.com/@traintrackcoach?si=WziUydifGSq2pSmm" target="_blank" class="button youtube-btn color-white">
        <i class="fab fa-youtube "></i> YouTube
    </a>
    <hr class="divider">
    <!-- App Download Section -->
    <h4 class="section-title">Download Our App</h4>
    <p>Get the TrainTrack Coach App on your favorite platform.</p>
    <a href="https://play.google.com/store/apps/details?id=com.ar.train_track" target="_blank"
       class="button google-btn color-white">
        <i class="fab fa-google-play"></i> Google Play
    </a>
    <a href="https://apps.apple.com/eg/app/train-track-app/id6478457740" target="_blank" class="button apple-btn color-white">
        <i class="fab fa-apple"></i> App Store
    </a>


</div>

<script src="{{asset('email/jquery.min.js')}}"></script>
<script src="{{asset('email/popper.min.js')}}"></script>
<script src="{{asset('email/bootstrap2.min.js')}}"></script>
</body>
</html>
