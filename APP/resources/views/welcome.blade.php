<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous"> 
    <link href="{{asset('./css/home.css')}}" rel="sytlesheet">
    <title>Home</title>
    <style>
        body {
            background-color: #f8f9fa; 
            text-align: center;
        }

        .welcome-heading {
            font-size: 4rem; 
            margin-top: 20vh; 
            animation: moveRight 3s infinite alternate; 
        }

        @keyframes moveRight {
            0% {
                transform: translateX(0); /* Start position */
            }
            100% {
                transform: translateX(30vw); /* End position (move right by 30% of viewport width) */
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light" style="background-color: rgba(0, 123, 255, 0.7);">
            <div class="container-fluid">
                <!-- Custom Logo -->
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('./download.png') }}" alt="Logo" width="50" class="rounded-circle">
                </a>
        
                <!-- Toggle Button -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
        
                <!-- Navigation Links -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Home</a>
                        </li>
                        
                    </ul>
        
                    <!-- Sign Up and Sign In Buttons -->
                    <form class="d-flex" action="/login">
                        <a href="{{asset('/signup')}}" class="btn btn-outline-danger me-2">Sign Up</a>
                        <button class="btn btn-outline-success" type="submit">Sign In</button>
                    </form>
                </div>
            </div>
        </nav>
        
    </header>
        
    <h1 class="welcome-heading">WELCOME TO MY LARAVEL APP !!</h1>
  
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

</body>
</html>