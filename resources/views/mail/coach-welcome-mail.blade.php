<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome, {{ $coachName }}</title>
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
    </style>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="email-container">
    <!-- Header Section -->
    <div class="text-center">
        <img src="{{ url('images/logo.png') }}" alt="App Logo" class="header-logo">
    </div>

    <p>Dear Coach <strong>{{ $coachName }}</strong>,</p>

    <p>
        Welcome to <strong>TrainTrack Coach Application!</strong><br>
        We are thrilled to have you onboard as part of our coaching community.
        My name is Mohamed Ayman, and I am here to support you every step of the way.
    </p>

    <p>
        If you have any questions or need assistance, feel free to reach out.
        We’re excited to make your journey smoother and more enjoyable!
    </p>

    <p>Best regards,</p>
    <p><strong>Mohamed Ayman</strong><br>Your Support at TrainTrack Coach Application</p>

    <hr class="divider">
    <!-- Contact Section -->
    <h4 class="section-title">Stay Connected</h4>
    <p>Contact us on WhatsApp or follow our YouTube channel where you can find presentation for the app..</p>
    <a href="https://wa.me/+201140066441" target="_blank" class="button whatsapp-btn">
        <i class="fab fa-whatsapp"></i> WhatsApp
    </a>
    <a href="https://www.youtube.com/@traintrackcoach?si=WziUydifGSq2pSmm" target="_blank" class="button youtube-btn">
        <i class="fab fa-youtube"></i> YouTube
    </a>
    <hr class="divider">
    <!-- App Download Section -->
    <h4 class="section-title">Download Our App</h4>
    <p>Get the TrainTrack Coach App on your favorite platform.</p>
    <a href="https://play.google.com/store/apps/details?id=com.ar.train_track" target="_blank"
       class="button google-btn">
        <i class="fab fa-google-play"></i> Google Play
    </a>
    <a href="https://apps.apple.com/eg/app/train-track-app/id6478457740" target="_blank" class="button apple-btn">
        <i class="fab fa-apple"></i> App Store
    </a>


</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
