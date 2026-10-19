<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zelari | Website Under Maintenance</title>
    <meta name="description"
        content="Zelari is making improvements to provide you with a better experience. We'll be back shortly.">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1677ff;
            --dark: #0d1b36;
            --muted: #61749a;
            --light-blue: #eaf2ff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            min-height: 100vh;
            background:
                radial-gradient(circle at 95% 5%, #e7f0ff 0, transparent 24%),
                radial-gradient(circle at 0% 100%, #eaf2ff 0, transparent 20%),
                #fbfdff;
            overflow-x: hidden;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 35px 0 20px;
        }

        .container-custom {
            width: min(1320px, 90%);
            margin: auto;
        }

        /* Header */
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--dark);
            font-size: 43px;
            font-weight: 800;
            letter-spacing: -2px;
        }

        .brand-logo {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #42a5ff, #0860e9);
            color: white;
            display: grid;
            place-items: center;
            border-radius: 12px;
            font-size: 30px;
            font-weight: 800;
            transform: skew(-8deg);
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 65px 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #e7f0ff;
            color: var(--primary);
            padding: 12px 19px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 28px;
        }

        .main-title {
            font-size: clamp(38px, 4.4vw, 64px);
            font-weight: 800;
            letter-spacing: -2.7px;
            line-height: 1.12;
            margin-bottom: 24px;
        }

        .main-title span {
            color: var(--primary);
        }

        .description {
            color: var(--muted);
            font-size: 18px;
            line-height: 1.85;
            max-width: 540px;
            margin-bottom: 35px;
        }

        /* Benefits */
        .benefits {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin: 35px 0;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #435a80;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
        }

        .benefit i {
            color: #506b99;
            font-size: 25px;
        }

        .support-box {
            border-top: 1px solid #dce6f6;
            padding-top: 25px;
            margin-top: 35px;
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .support-icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            color: #506b99;
            font-size: 23px;
        }

        .support-label {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 4px;
        }

        .support-link {
            color: var(--primary);
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
        }

        .support-link:hover {
            color: #084fbd;
            text-decoration: underline;
        }

        /* CSS Laptop Illustration */
        .illustration {
            min-height: 450px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .illustration-bg {
            position: absolute;
            width: 95%;
            height: 72%;
            background: linear-gradient(140deg, #edf4ff, #dbe9ff);
            border-radius: 48% 52% 43% 57% / 48% 38% 62% 52%;
            transform: rotate(-7deg);
        }

        .gear {
            position: absolute;
            right: 5%;
            top: 22%;
            color: #91b2e9;
            font-size: 135px;
            z-index: 1;
        }

        .plant {
            position: absolute;
            left: 7%;
            top: 43%;
            color: #45b9a8;
            font-size: 90px;
            transform: rotate(-20deg);
            z-index: 1;
        }

        .laptop {
            width: 88%;
            max-width: 490px;
            position: relative;
            z-index: 2;
            margin-top: 30px;
        }

        .laptop-screen {
            min-height: 275px;
            background: linear-gradient(145deg, #263650, #101c32);
            border: 10px solid #243148;
            border-radius: 20px 20px 10px 10px;
            box-shadow: 0 20px 45px rgba(23, 53, 100, .18);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 25px;
            color: white;
        }

        .screen-brand {
            font-size: 35px;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 18px;
        }

        .screen-brand span {
            color: #49a0ff;
        }

        .screen-text {
            font-size: 15px;
            margin-bottom: 18px;
        }

        .progress-track {
            width: 150px;
            height: 9px;
            border-radius: 20px;
            background: #34435d;
            overflow: hidden;
        }

        .progress-bar-custom {
            width: 65%;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #1682ff, #58b3ff);
            animation: progress 2.5s ease-in-out infinite alternate;
        }

        @keyframes progress {
            from {
                width: 35%;
            }

            to {
                width: 75%;
            }
        }

        .laptop-base {
            width: 112%;
            height: 20px;
            margin-left: -6%;
            background: linear-gradient(180deg, #dce6f5, #899bb6);
            border-radius: 4px 4px 22px 22px;
            box-shadow: 0 15px 20px rgba(30, 60, 100, .16);
            position: relative;
        }

        .laptop-base::after {
            content: "";
            position: absolute;
            top: 0;
            left: 40%;
            width: 20%;
            height: 6px;
            background: #a8bad2;
            border-radius: 0 0 8px 8px;
        }

        .hard-hat {
            position: absolute;
            z-index: 3;
            right: 4%;
            bottom: 8%;
            color: #ffbf35;
            font-size: 105px;
            filter: drop-shadow(0 12px 8px rgba(70, 90, 120, .12));
        }

        .cone {
            position: absolute;
            z-index: 3;
            left: 4%;
            bottom: 12%;
            color: #ff963b;
            font-size: 65px;
            transform: rotate(5deg);
        }

        .spark {
            position: absolute;
            color: #328bff;
            font-size: 28px;
        }

        .spark-one {
            top: 20%;
            left: 18%;
            transform: rotate(-30deg);
        }

        .spark-two {
            top: 29%;
            left: 13%;
            transform: rotate(25deg);
        }

        /* Footer */
        .footer {
            padding: 20px 0;
            color: #8293b2;
            font-size: 12px;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .footer-brand {
            font-size: 20px;
            font-weight: 800;
            color: #8293b2;
            letter-spacing: -1px;
        }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .social-links {
            display: flex;
            gap: 17px;
        }

        .social-links a {
            color: #8293b2;
            font-size: 17px;
            text-decoration: none;
            transition: color .2s;
        }

        .social-links a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 991px) {
            .main-content {
                padding: 55px 0 30px;
            }

            .main-title {
                letter-spacing: -1.8px;
            }

            .illustration {
                min-height: 400px;
                margin-top: 30px;
            }

            .description {
                font-size: 16px;
            }
        }

        @media (max-width: 575px) {
            .page-wrapper {
                padding-top: 22px;
            }

            .brand {
                font-size: 32px;
            }

            .brand-logo {
                width: 36px;
                height: 36px;
                font-size: 24px;
            }

            .main-content {
                padding: 45px 0 20px;
            }

            .status-badge {
                font-size: 13px;
                padding: 10px 14px;
                margin-bottom: 22px;
            }

            .main-title {
                font-size: 39px;
                letter-spacing: -1.7px;
            }

            .description {
                font-size: 15px;
                line-height: 1.8;
            }

            .benefits {
                gap: 18px;
            }

            .benefit {
                font-size: 12px;
            }

            .benefit i {
                font-size: 21px;
            }

            .illustration {
                min-height: 300px;
            }

            .laptop {
                width: 82%;
            }

            .laptop-screen {
                min-height: 200px;
                border-width: 7px;
                padding: 15px;
            }

            .screen-brand {
                font-size: 27px;
            }

            .screen-text {
                font-size: 12px;
            }

            .gear {
                font-size: 90px;
            }

            .plant {
                font-size: 60px;
            }

            .hard-hat {
                font-size: 70px;
            }

            .cone {
                font-size: 45px;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }

            .footer-right {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrapper">

        <!-- Header -->
        <header class="container-custom">
            <a href="#" class="brand" aria-label="Zelari home">
                <span class="brand-logo">Z</span>
                <span>Zelari</span>
            </a>
        </header>

        <!-- Main -->
        <main class="main-content">
            <div class="container-custom">
                <div class="row align-items-center">

                    <!-- Left Content -->
                    <div class="col-lg-6">
                        <div class="status-badge">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            Website Under Maintenance
                        </div>

                        <h1 class="main-title">
                            We're currently
                            <span>working on something great!</span>
                        </h1>

                        <p class="description">
                            Our website is temporarily down for maintenance
                            to bring you a better, faster and more seamless
                            experience. We'll be back shortly!
                        </p>

                        <!-- Benefits -->
                        <div class="benefits">
                            <div class="benefit">
                                <i class="fa-solid fa-gear"></i>
                                <span>Better<br>Performance</span>
                            </div>

                            <div class="benefit">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Improved<br>Security</span>
                            </div>

                            <div class="benefit">
                                <i class="fa-regular fa-star"></i>
                                <span>Enhanced<br>Experience</span>
                            </div>
                        </div>

                        <!-- Support -->
                        <div class="support-box">
                            <div class="support-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </div>

                            <div>
                                <div class="support-label">
                                    Need immediate assistance?
                                </div>

                                <a href="mailto:support@zelari.com" class="support-link">
                                    support@zelari.com
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Illustration -->
                    <div class="col-lg-6">
                        <div class="illustration">

                            <div class="illustration-bg"></div>

                            <i class="fa-solid fa-gear gear"></i>
                            <i class="fa-solid fa-leaf plant"></i>

                            <i class="fa-solid fa-minus spark spark-one"></i>
                            <i class="fa-solid fa-minus spark spark-two"></i>

                            <div class="laptop">
                                <div class="laptop-screen">

                                    <div class="screen-brand">
                                        <span>Z</span> Zelari
                                    </div>

                                    <div class="screen-text">
                                        Under Maintenance
                                    </div>

                                    <div class="progress-track">
                                        <div class="progress-bar-custom"></div>
                                    </div>

                                </div>

                                <div class="laptop-base"></div>
                            </div>

                            <i class="fa-solid fa-helmet-safety hard-hat"></i>
                            <i class="fa-solid fa-traffic-cone cone"></i>

                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-custom">
                <div class="footer-inner">

                    <div class="d-flex align-items-center gap-3">
                        <span class="footer-brand">Z Zelari</span>
                        <span style="border-left: 1px solid #ccd8eb; height: 25px;"></span>
                        <span>Building a Smarter Tomorrow</span>
                    </div>

                    <div class="footer-right">
                        <div class="social-links">
                            <!-- Replace # with your actual social URLs -->
                            <a href="#" aria-label="Facebook">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                            <a href="#" aria-label="X">
                                <i class="fa-brands fa-x-twitter"></i>
                            </a>
                            <a href="#" aria-label="LinkedIn">
                                <i class="fa-brands fa-linkedin"></i>
                            </a>
                            <a href="#" aria-label="Instagram">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </div>
                        <span>&copy; {{ date('Y') }} Zelari. All rights reserved.</span>
                    </div>

                </div>
            </div>
        </footer>

    </div>

</body>

</html>