<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trung tâm đào tạo ngoại ngữ')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    {{-- Fontawesome--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- gg font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        * {
            box-sizing: border-box;
        }
        a{
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: none;
        }

        header {
            background: #10454F;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        nav {
            margin: 20px 0;
        }
        nav a {
            margin: 0 15px;
            color: #fff;
            text-decoration: none;
        }
        .container {
            width: 90%; /* Giảm từ 100% xuống 90% */
            max-width: 1400px; /* Thêm max-width để giới hạn kích thước tối đa */
            margin: auto;
            overflow: hidden;
            padding: 20px;
            background: #fff;
            margin-bottom: 40px;
            margin-top: 125px;
            
        }
    </style>
    @yield('stylesheet')
    {{-- <link rel="stylesheet" href="{{ asset('assets/clients/css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/clients/css/reponsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/clients/css/service_section.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/clients/css/style.css') }}"> --}}