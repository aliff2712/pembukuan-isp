<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DHS Finance')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>

        :root{
            --navy-dark:#020617;
            --navy-main:#0f172a;
            --navy-soft:#1e293b;
            --navy-light:#334155;
            --blue-accent:#3b82f6;
        }

        /* BODY BACKGROUND */
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background: linear-gradient(
                135deg,
                var(--navy-dark),
                var(--navy-main),
                var(--navy-soft),
                var(--navy-light)
            );
            overflow-x:hidden;
        }

        /* LOGIN WRAPPER */
        .login-wrapper{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        /* LOGIN CARD */
        .login-card{
            width:100%;
            max-width:420px;
            border-radius:20px;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(18px);
            box-shadow: 0 25px 60px rgba(0,0,0,.35);
            border:none;
        }

        /* BUTTON PRIMARY */
        .btn-primary{
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            border:none;
        }

        .btn-primary:hover{
            transform: translateY(-2px);
            box-shadow:0 10px 20px rgba(59,130,246,.3);
        }

        /* ===== PAGE LOADER NAVY ===== */

        #pageLoader{
            position:fixed;
            inset:0;
            background: linear-gradient(
                135deg,
                var(--navy-dark),
                var(--navy-main),
                var(--navy-soft)
            );
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            z-index:99999;
            transition: opacity .5s ease, visibility .5s ease;
        }

        .loader-logo{
            width:70px;
            height:70px;
            border-radius:16px;
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            display:flex;
            justify-content:center;
            align-items:center;
            color:white;
            font-size:28px;
            margin-bottom:20px;
            box-shadow:0 0 25px rgba(59,130,246,.4);
            animation: glow 1.5s infinite alternate;
        }

        @keyframes glow{
            from{
                box-shadow:0 0 10px rgba(59,130,246,.3);
            }
            to{
                box-shadow:0 0 30px rgba(59,130,246,.8);
            }
        }

        .loader-spinner{
            width:45px;
            height:45px;
            border-radius:50%;
            border:4px solid rgba(255,255,255,.2);
            border-top:4px solid var(--blue-accent);
            animation: spin 1s linear infinite;
        }

        @keyframes spin{
            100%{
                transform:rotate(360deg);
            }
        }

        .loader-text{
            color:#cbd5e1;
            font-size:14px;
            margin-top:15px;
            letter-spacing:1px;
        }

        /* FADE PAGE */
        .fade-in{
            opacity:0;
            animation: fadeInPage .6s ease forwards;
        }

        @keyframes fadeInPage{
            to{
                opacity:1;
            }
        }

    </style>

    @stack('styles')

</head>
<body>

    <!-- NAVY LOADER -->
    <div id="pageLoader">

        <div class="loader-logo">
            <i class="fas fa-chart-line"></i>
        </div>

        <div class="loader-spinner"></div>

        <div class="loader-text">
            Memuat DHS Finance...
        </div>

    </div>


    <!-- CONTENT -->
    <div class="fade-in">
        @yield('content')
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')


    <!-- LOADER SCRIPT -->
    <script>

    window.addEventListener("load", function(){

        const loader=document.getElementById("pageLoader");

        if(loader){

            loader.style.opacity="0";
            loader.style.visibility="hidden";

            setTimeout(()=>{
                loader.remove();
            },500);

        }

    });

    </script>


</body>
</html>