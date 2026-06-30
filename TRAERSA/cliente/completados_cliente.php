<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/Inicio_sesion.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

// Envíos entregados que pertenecen al cliente logueado (vía su fila en `clientes`)
$sql = "SELECT e.*, c.titulo AS servicio_titulo, c.tipo_entrega,
               (SELECT MAX(se.fecha) FROM seguimiento_envio se WHERE se.envio_id = e.id_envio AND se.estado = 'Entregado') AS fecha_entrega_real,
               (SELECT se.foto_evidencia FROM seguimiento_envio se WHERE se.envio_id = e.id_envio AND se.estado = 'Entregado' ORDER BY se.fecha DESC LIMIT 1) AS foto_evidencia
        FROM envios e
        INNER JOIN clientes cl ON cl.id_cliente = e.cliente_id
        LEFT JOIN categoria c ON c.id = e.servicio_id
        WHERE cl.usuario_id = ? AND e.estado = 'Entregado'
        ORDER BY e.fecha_solicitud DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cliente | TRAERSA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@500;700&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>

        /* =========================================================
           TRAERSA — Design tokens
           ========================================================= */
        :root{
            --navy-950: #081021;
            --navy-900: #0B1B33;
            --navy-800: #10274A;
            --blue-600: #1E5AA8;
            --blue-400: #4D8FE0;
            --red-600:  #E1251B;
            --red-700:  #B81C14;
            --cream:    #F6F4EF;
            --white:    #FFFFFF;
            --ink:      #14202E;
            --gray-500: #677081;
            --gray-300: #C7CDD6;

            --font-display: 'Bebas Neue', sans-serif;
            --font-body: 'Montserrat', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;

            --radius: 14px;
            --radius-lg: 22px;
            --ease: cubic-bezier(.22,.85,.32,1);
        }

        @media (prefers-reduced-motion: reduce){
            *, *::before, *::after{
                animation-duration: .001ms !important;
                transition-duration: .001ms !important;
            }
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: var(--font-body);
        }

        body{
            background: var(--cream);
            color: var(--ink);
            overflow-x:hidden;
        }

        img{ max-width:100%; }

        :focus-visible{
            outline: 3px solid var(--blue-400);
            outline-offset: 2px;
        }

        /* =========================================================
           Top navbar
           ========================================================= */
        .top-navbar{
            position:fixed;
            top:0; left:0;
            width:100%;
            height:80px;
            background:var(--white);
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 25px;
            box-shadow:0 2px 16px rgba(8,16,33,.08);
            z-index:2000;
        }

        .nav-left,
        .nav-right{
            width:250px;
            display:flex;
            align-items:center;
        }

        .nav-right{
            justify-content:flex-end;
            gap:25px;
        }

        .nav-center{
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .navbar-logo{
            height:56px;
            object-fit:contain;
        }

        .nav-icon{
            display:flex;
            align-items:center;
            gap:8px;
            cursor:pointer;
            font-weight:600;
            font-size:14px;
            color: var(--ink);
            text-decoration:none;
            transition:.25s var(--ease);
        }

        .nav-icon:hover{ color: var(--red-600); }
        .nav-icon.active{ color: var(--red-600); }
        .nav-icon i{ font-size:19px; }

        .menu-toggle{
            width:48px;
            height:48px;
            border:none;
            border-radius:50%;
            background: var(--red-600);
            color:white;
            font-size:19px;
            cursor:pointer;
            transition:.25s var(--ease);
        }

        .menu-toggle:hover{
            background: var(--red-700);
            transform:scale(1.06);
        }

        /* =========================================================
           Sidebar
           ========================================================= */
        .sidebar{
            position:fixed;
            top:80px; left:0;
            width:280px;
            height:calc(100vh - 80px);
            background: linear-gradient(180deg, var(--navy-950), var(--navy-900));
            padding:25px;
            color:white;
            overflow-y:auto;
            transition:.4s var(--ease);
            z-index:1500;
        }

        .sidebar.closed{ transform:translateX(-100%); }

        .menu-section{ margin-bottom:35px; }

        .menu-title{
            margin-bottom:15px;
            font-family: var(--font-mono);
            font-size:12px;
            font-weight:700;
            letter-spacing:.16em;
            text-transform:uppercase;
            color: var(--red-600);
        }

        .menu-title::after{
            content:'';
            display:block;
            width:28px;
            height:2px;
            margin-top:8px;
            border-radius:2px;
            background: linear-gradient(90deg, var(--red-600), var(--blue-400));
        }

        .menu-item{
            width:100%;
            min-height:50px;
            display:flex;
            align-items:center;
            gap:15px;
            padding:12px 18px;
            margin-bottom:10px;
            border-radius:var(--radius);
            text-decoration:none;
            color:white;
            font-size:14px;
            font-weight:500;
            transition:.25s var(--ease);
        }

        .menu-item i{
            width:18px;
            text-align:center;
            color: var(--blue-400);
            transition: color .25s var(--ease);
        }

        .menu-item:hover{
            background:rgba(255,255,255,.12);
            transform:translateX(5px);
        }

        .menu-item.active{ background:white; color: var(--red-600); }
        .menu-item.active i{ color: var(--red-600); }

        .btn-logout{
            width:100%;
            height:50px;
            border:none;
            border-radius:var(--radius);
            background: var(--red-600);
            color:white;
            font-weight:700;
            font-size:14px;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            text-decoration:none;
            transition:.25s var(--ease);
        }

        .btn-logout:hover{
            background: var(--red-700);
            transform:translateY(-2px);
        }

        /* =========================================================
           Main content
           ========================================================= */
        .main-content{
            margin-top:100px;
            margin-left:280px;
            padding:30px;
            transition:.4s var(--ease);
            width:calc(100% - 280px);
        }

        .main-content.full{ margin-left:0; width:100%; }

        .hero-card{
            position:relative;
            overflow:hidden;
            background:white;
            border-radius:var(--radius-lg);
            padding:40px 35px;
            box-shadow:0 10px 30px rgba(8,16,33,.08);
            margin-bottom:30px;
            text-align:center;
        }

        .hero-card::after{
            content:'\f0d1';
            font-family:"Font Awesome 6 Free";
            font-weight:900;
            position:absolute;
            right:-18px; bottom:-28px;
            font-size:150px;
            line-height:1;
            color: rgba(225,37,27,.06);
            pointer-events:none;
        }

        .hero-card > *{ position:relative; }

        .hero-card h1{
            font-family: var(--font-display);
            font-size:clamp(34px, 5vw, 52px);
            letter-spacing:.01em;
            color: var(--navy-900);
            margin-bottom:10px;
            word-wrap:break-word;
        }

        .hero-card h1 span{ color: var(--red-600); }

        .hero-card p{
            color: var(--gray-500);
            font-size:15px;
        }

        .eyebrow{
            display:block;
            text-align:center;
            font-family: var(--font-mono);
            font-weight:700;
            font-size:12px;
            letter-spacing:.18em;
            text-transform:uppercase;
            color: var(--red-600);
            margin-bottom:10px;
        }

        .section-title{
            font-family: var(--font-display);
            font-size:clamp(30px, 4.4vw, 44px);
            letter-spacing:.01em;
            color: var(--navy-900);
            text-align:center;
        }

        .line{
            width:70px;
            height:4px;
            background: linear-gradient(90deg, var(--red-600), var(--blue-600));
            border-radius:4px;
            margin:18px auto 30px;
        }

        [data-reveal]{
            opacity:0;
            transform:translateY(24px);
            transition: opacity .6s var(--ease), transform .6s var(--ease);
        }
        [data-reveal].in-view{ opacity:1; transform:translateY(0); }

        /* =========================================================
           Filtros rápidos (estilo categorías de tienda)
           ========================================================= */
        .filter-bar{
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap:10px;
            margin-bottom:34px;
        }

        .filter-chip{
            border:1px solid var(--gray-300);
            background:white;
            color: var(--navy-900);
            padding:10px 20px;
            border-radius:999px;
            font-size:13px;
            font-weight:700;
            cursor:pointer;
            transition:.22s var(--ease);
            display:flex;
            align-items:center;
            gap:8px;
        }

        .filter-chip i{ font-size:12px; color: var(--blue-600); }

        .filter-chip:hover{
            border-color: var(--red-600);
            transform:translateY(-2px);
        }

        .filter-chip.active{
            background: var(--red-600);
            border-color: var(--red-600);
            color:white;
        }

        .filter-chip.active i{ color:white; }

        /* =========================================================
           Service cards
           ========================================================= */
        .services-grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(280px, 1fr));
            gap:25px;
            align-items:stretch;
        }

        .service-card{
            position:relative;
            background:white;
            border-radius:var(--radius-lg);
            overflow:hidden;
            box-shadow:0 8px 24px rgba(8,16,33,.08);
            transition:.3s var(--ease), opacity .25s var(--ease);
            display:flex;
            flex-direction:column;
        }

        .service-card::before{
            content:'';
            position:absolute;
            top:0; left:0; right:0;
            height:4px;
            background: linear-gradient(90deg, var(--red-600), var(--blue-600));
            transform:scaleX(0);
            transform-origin:left;
            transition:transform .35s var(--ease);
            z-index:2;
        }

        .service-card:hover::before{ transform:scaleX(1); }

        .service-card:hover{
            transform:translateY(-8px);
            box-shadow:0 20px 36px rgba(8,16,33,.14);
        }

        .service-card.is-hidden{
            display:none;
        }

        .service-media{
            position:relative;
            overflow:hidden;
            background: linear-gradient(135deg, var(--navy-900), var(--navy-800));
        }

        .service-media img{
            width:100%;
            height:200px;
            object-fit:cover;
            display:block;
            transition:transform .5s var(--ease);
        }

        .service-card:hover .service-media img{
            transform:scale(1.06);
        }

        .service-tag{
            position:absolute;
            top:12px; left:12px;
            background: rgba(8,16,33,.78);
            backdrop-filter: blur(2px);
            color:white;
            font-family: var(--font-mono);
            font-size:10.5px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
            padding:6px 11px;
            border-radius:999px;
            display:flex;
            align-items:center;
            gap:6px;
        }

        .service-tag i{ color: var(--blue-400); font-size:10px; }

        .service-content{
            padding:22px;
            display:flex;
            flex-direction:column;
            flex:1;
        }

        .service-content h3{
            font-family: var(--font-display);
            font-size:24px;
            letter-spacing:.01em;
            margin-bottom:14px;
            text-align:center;
            color: var(--navy-900);
        }

        .service-info{
            list-style:none;
            color: var(--ink);
            font-size:14px;
            line-height:1.7;
            margin-bottom:18px;
            flex:1;
            padding:12px 0;
            border-top:1px solid rgba(20,32,46,.08);
            border-bottom:1px solid rgba(20,32,46,.08);
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .service-info li{ display:flex; align-items:center; gap:10px; }
        .service-info i{ width:18px; text-align:center; color: var(--blue-600); font-size:13px; }

        .price{
            font-size:30px;
            font-weight:800;
            color: var(--red-600);
            margin:16px 0 18px;
            text-align:center;
        }

        .price small{
            display:block;
            font-size:11px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
            color: var(--gray-500);
        }

        .service-buttons{
            display:flex;
            gap:12px;
            margin-top:auto;
        }

        .btn-primary,
        .btn-secondary{
            flex:1;
            min-width:120px;
            height:48px;
            border:none;
            border-radius:var(--radius);
            cursor:pointer;
            font-family: var(--font-body);
            font-weight:700;
            font-size:14px;
            transition:.22s var(--ease);
            position:relative;
            overflow:hidden;
            text-decoration:none;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }

        .btn-primary{ background: var(--red-600); color:white; }
        .btn-secondary{ background: var(--cream); color: var(--navy-900); }

        .btn-primary:hover{ background: var(--red-700); transform:translateY(-2px); }
        .btn-secondary:hover{ transform:translateY(-2px); }

        .btn-primary:active{ transform:translateY(0) scale(.98); }

        .empty-state{
            grid-column:1/-1;
            text-align:center;
            color: var(--gray-500);
            padding:50px 20px;
            background:white;
            border-radius:var(--radius-lg);
        }

        .empty-state i{
            font-size:34px;
            color: var(--blue-400);
            margin-bottom:14px;
            display:block;
        }

        @media(max-width:1100px){ .services-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:700px){ .services-grid{ grid-template-columns:1fr; } }

        /* =========================================================
           Overlay
           ========================================================= */
        .overlay{
            position:fixed;
            inset:0;
            background:rgba(8,16,33,.55);
            z-index:1200;
            opacity:0;
            visibility:hidden;
            transition:.3s var(--ease);
        }
        .overlay.active{ opacity:1; visibility:visible; }

        /* =========================================================
           Footer
           ========================================================= */
        .footer{
            margin-top:60px;
            background: var(--navy-950);
            color:rgba(255,255,255,.85);
            padding:60px 5% 25px;
        }

        .footer-container{
            max-width:1180px;
            margin:0 auto;
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:35px;
            margin-bottom:35px;
            padding-bottom:35px;
            border-bottom:1px solid rgba(255,255,255,.1);
        }

        .footer-box h4{
            margin-bottom:18px;
            font-family: var(--font-display);
            font-size:19px;
            letter-spacing:.02em;
            color:white;
        }

        .footer-box ul{ list-style:none; }

        .footer-box ul li{
            margin-bottom:10px;
            font-size:14px;
            color:rgba(255,255,255,.6);
            transition:.25s var(--ease);
        }

        .footer-box ul li:hover{ color:white; transform:translateX(4px); }

        .social{ display:flex; gap:14px; margin-top:15px; flex-wrap:wrap; }

        .social i{
            width:38px; height:38px;
            border-radius:50%;
            border:1px solid rgba(255,255,255,.25);
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:.25s var(--ease);
            font-size:16px;
        }

        .social i:hover{
            background: var(--red-600);
            border-color: var(--red-600);
            transform:translateY(-3px);
        }

        .contact{
            max-width:1180px;
            margin:0 auto;
            display:flex;
            flex-wrap:wrap;
            justify-content:center;
            gap:28px;
            font-size:14px;
            color:rgba(255,255,255,.75);
            text-align:center;
        }

        .contact div{ display:flex; align-items:center; gap:10px; }
        .contact i{ color: var(--red-600); }

        /* =========================================================
           Responsive
           ========================================================= */
        @media(max-width:900px){
            .sidebar{ transform:translateX(-100%); }
            .sidebar.mobile-active{ transform:translateX(0); }
            .main-content{ margin-left:0; width:100%; padding:20px; }
            .hero-card{ padding:28px 22px; }
            .nav-right{ width:auto; gap:15px; }
            .nav-icon span{ display:none; }
            .top-navbar{ height:72px; padding:0 16px; }
        }

        @media(max-width:600px){
            .top-navbar{ padding:0 12px; }
            .nav-left{ width:auto; }
            .navbar-logo{ height:42px; }
            .service-buttons{ flex-direction:column; }
            .contact{ flex-direction:column; gap:14px; }
            .contact div{ justify-content:center; }
            .main-content{ padding:16px 12px 20px; }
        }

        @media(max-width:480px){
            .nav-right{ gap:8px; }
            .menu-toggle{ width:42px; height:42px; }
            .navbar-logo{ height:36px; }
        }


        /* =========================================================
           Tracker de cotización (estilo seguimiento de pedido)
           ========================================================= */
        .status-pill{
            position:absolute;
            top:12px; left:12px;
            z-index:2;
            font-family: var(--font-mono);
            font-size:10.5px;
            font-weight:700;
            letter-spacing:.06em;
            text-transform:uppercase;
            padding:6px 12px;
            border-radius:999px;
            display:flex;
            align-items:center;
            gap:6px;
            color:white;
        }
        .status-pill.pendiente{ background: rgba(225,37,27,.92); }
        .status-pill.proceso{ background: rgba(30,90,168,.92); }
        .status-pill.completado{ background: rgba(8,16,33,.85); }

        .tracker{
            display:flex;
            align-items:flex-start;
            margin:4px 0 18px;
        }

        .tracker-step{
            flex:1;
            text-align:center;
            position:relative;
        }

        .tracker-step::before{
            content:'';
            position:absolute;
            top:6px; left:-50%;
            width:100%; height:2px;
            background: var(--gray-300);
            z-index:1;
        }

        .tracker-step:first-child::before{ display:none; }

        .tracker-step.done::before{ background: var(--red-600); }

        .tracker-dot{
            width:14px; height:14px;
            border-radius:50%;
            background: var(--gray-300);
            margin:0 auto 8px;
            position:relative;
            z-index:2;
            transition:.3s var(--ease);
        }

        .tracker-step.done .tracker-dot,
        .tracker-step.active .tracker-dot{
            background: var(--red-600);
            box-shadow:0 0 0 4px rgba(225,37,27,.15);
        }

        .tracker-label{
            font-size:10.5px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.04em;
            color: var(--gray-500);
        }

        .tracker-step.active .tracker-label,
        .tracker-step.done .tracker-label{ color: var(--red-600); }

        .completed-badge{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            background: rgba(30,90,168,.1);
            color: var(--blue-600);
            border:1px solid rgba(30,90,168,.25);
            border-radius:var(--radius);
            padding:12px;
            font-weight:700;
            font-size:14px;
            margin:16px 0;
        }

        .stopped-badge{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            background: rgba(225,37,27,.08);
            color: var(--red-700);
            border:1px solid rgba(225,37,27,.25);
            border-radius:var(--radius);
            padding:12px;
            font-weight:700;
            font-size:13px;
            margin:16px 0;
        }

        .international-header{
            text-align:center;
            margin-bottom:10px;
        }

        /* =========================================================
           Evidencia de entrega + comentario del cliente
           ========================================================= */
        .evidence-photo-wrap{
            margin:14px 0;
        }

        .evidence-label{
            display:flex;
            align-items:center;
            gap:6px;
            font-family: var(--font-mono);
            font-size:10.5px;
            font-weight:700;
            letter-spacing:.06em;
            text-transform:uppercase;
            color: var(--gray-500);
            margin-bottom:8px;
        }

        .evidence-photo{
            width:100%;
            border-radius:var(--radius);
            border:1px solid rgba(20,32,46,.1);
            display:block;
        }

        .comment-box{
            background: var(--cream);
            border:1px solid rgba(20,32,46,.08);
            border-radius:var(--radius);
            padding:14px;
            margin:14px 0;
        }

        .comment-label{
            display:flex;
            align-items:center;
            gap:6px;
            font-size:12px;
            font-weight:700;
            color: var(--navy-900);
            margin-bottom:8px;
        }

        .comment-input{
            width:100%;
            min-height:64px;
            border:1px solid var(--gray-300);
            border-radius:10px;
            padding:10px 12px;
            font-size:13px;
            font-family: var(--font-body);
            resize:vertical;
            outline:none;
            background:white;
        }

        .comment-input:focus{
            border-color: var(--blue-600);
        }

        .btn-comment-save,
        .btn-comment-edit{
            margin-top:8px;
            border:none;
            background: var(--blue-600);
            color:white;
            padding:9px 16px;
            border-radius:10px;
            font-size:12px;
            font-weight:700;
            cursor:pointer;
            transition:.22s var(--ease);
        }

        .btn-comment-save:hover,
        .btn-comment-edit:hover{
            background: var(--navy-900);
        }

        .comment-saved{
            font-size:13.5px;
            color: var(--ink);
            line-height:1.5;
            font-style:italic;
        }

        .comment-msg{
            font-size:11.5px;
            margin-top:6px;
            color: var(--blue-600);
            display:none;
        }

        .comment-msg.show{ display:block; }

    </style>

</head>

<body>

    <header class="top-navbar">

        <nav class="nav-left">
            <button class="menu-toggle" id="menuToggle" aria-label="Abrir u ocultar menú" aria-controls="sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </nav>

        <div class="nav-center">
            <a href="cliente.php">
                <img src="imagenes/logo.png" class="navbar-logo" alt="Logo TRAERSA">
            </a>
        </div>

        <nav class="nav-right">

            <a href="mi_cuenta.php" class="nav-icon">
                <i class="fa-regular fa-user"></i>
                <span>Mi cuenta</span>
            </a>

        </nav>

    </header>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar" id="sidebar">

        <div class="menu-section">

            <div class="menu-title">Envíos</div>

            <a href="ciudad_cliente.php" class="menu-item">
                <i class="fa-solid fa-truck"></i>
                <span>Ciudad</span>
            </a>

            <a href="departamento_cliente.php" class="menu-item">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Departamentos</span>
            </a>

            <a href="Urbana_cliente.php" class="menu-item">
                <i class="fa-solid fa-city"></i>
                <span>Urbana</span>
            </a>

        </div>

        <section class="menu-section">

            <div class="menu-title">Gestión</div>

            <a href="cotizaciones_cliente.php" class="menu-item">
                <i class="fa-solid fa-file-lines"></i>
                <span>Rastrear</span>
            </a>
            <a href="completados_cliente.php" class="menu-item">
                <i class="fa-solid fa-circle-check"></i>
                <span>Completados</span>
            </a>

        </section>

        <button class="btn-logout" onclick="window.location.href='../login/logout.php'">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Cerrar sesión
        </button>

    </aside>

    <main class="main-content" id="mainContent">

        <section class="hero-card" data-reveal>

            <p class="eyebrow">Historial de envíos</p>

            <h1>
                SERVICIOS <span>COMPLETADOS</span>
            </h1>

            <p>
                Revisa todos los servicios completados y entregados correctamente.
            </p>

        </section>

        <section>

            <p class="eyebrow" data-reveal>Historial</p>
            <h2 class="section-title" data-reveal>Envíos entregados</h2>
            <div class="line" data-reveal></div>

            <div class="services-grid">

                <?php if ($resultado->num_rows === 0): ?>

                    <div class="empty-state">
                        <i class="fa-solid fa-box-open"></i>
                        Todavía no tienes envíos entregados. En cuanto completemos uno de tus pedidos, aparecerá aquí.
                    </div>

                <?php else: ?>

                    <?php while ($fila = $resultado->fetch_assoc()): ?>

                    <article class="service-card" data-reveal>
                        <div class="service-media">
                            <span class="status-pill completado"><i class="fa-solid fa-circle-check"></i> Completado</span>
                            <img src="imagenes/completado-default.jpg" alt="<?php echo htmlspecialchars($fila['servicio_titulo'] ?: 'Envío'); ?>" onerror="this.onerror=null;this.removeAttribute('src');">
                        </div>
                        <div class="service-content">
                            <h3><?php echo htmlspecialchars($fila['servicio_titulo'] ?: 'Servicio'); ?></h3>

                            <ul class="service-info">
                                <li><i class="fa-solid fa-route"></i> <?php echo htmlspecialchars(($fila['origen'] ?: '—') . ' → ' . ($fila['destino'] ?: '—')); ?></li>
                                <?php if ($fila['paquetes']): ?>
                                <li><i class="fa-solid fa-box"></i> <?php echo (int) $fila['paquetes']; ?> paquetes enviados</li>
                                <?php endif; ?>
                                <?php if ($fila['peso']): ?>
                                <li><i class="fa-solid fa-weight-hanging"></i> <?php echo htmlspecialchars($fila['peso']); ?> Kg</li>
                                <?php endif; ?>
                            </ul>

                            <div class="completed-badge">
                                <i class="fa-solid fa-circle-check"></i>
                                Entregado el <?php echo $fila['fecha_entrega_real'] ? date('d/m/Y', strtotime($fila['fecha_entrega_real'])) : date('d/m/Y', strtotime($fila['fecha_solicitud'])); ?>
                            </div>

                            <?php if (!empty($fila['foto_evidencia'])): ?>
                            <div class="evidence-photo-wrap">
                                <span class="evidence-label"><i class="fa-solid fa-camera"></i> Comprobante de entrega</span>
                                <img src="../uploads/<?php echo htmlspecialchars($fila['foto_evidencia']); ?>" alt="Comprobante de entrega" class="evidence-photo">
                            </div>
                            <?php endif; ?>

                            <div class="comment-box" data-envio="<?php echo (int) $fila['id_envio']; ?>">
                                <label class="comment-label"><i class="fa-regular fa-comment-dots"></i> Tu comentario (opcional)</label>
                                <?php if (!empty($fila['comentario_cliente'])): ?>
                                    <p class="comment-saved"><?php echo htmlspecialchars($fila['comentario_cliente']); ?></p>
                                    <button type="button" class="btn-comment-edit" onclick="editarComentario(this)">Editar comentario</button>
                                <?php else: ?>
                                    <textarea class="comment-input" placeholder="¿Cómo estuvo el servicio?" maxlength="500"></textarea>
                                    <button type="button" class="btn-comment-save" onclick="guardarComentario(this)">Guardar comentario</button>
                                <?php endif; ?>
                                <p class="comment-msg"></p>
                            </div>

                            <div class="service-buttons">
                                <a href="cliente.php" class="btn-primary">
                                    <i class="fa-solid fa-rotate-right"></i>
                                    Volver a contratar
                                </a>
                            </div>
                        </div>
                    </article>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>

        </section>

    </main>

<footer class="footer">

        <div class="footer-container">

            <section class="footer-box">
                <h4>Síguenos en redes sociales</h4>
                <div class="social">
                    <i class="fa-brands fa-facebook"></i>
                    <i class="fa-brands fa-x-twitter"></i>
                    <i class="fa-brands fa-instagram"></i>
                    <i class="fa-brands fa-linkedin"></i>
                </div>
            </section>

            <section class="footer-box">
                <h4>Servicios</h4>
                <ul>
                    <li>Gestión Aduanal</li>
                    <li>Transporte Terrestre</li>
                    <li>Transporte Marítimo</li>
                    <li>Almacenaje</li>
                </ul>
            </section>

            <section class="footer-box">
                <h4>Grupo TRAERSA</h4>
                <ul>
                    <li>Únete a nuestro equipo</li>
                    <li>Sobre nosotros</li>
                    <li>Deseas ser proveedor</li>
                </ul>
            </section>

            <section class="footer-box">
                <h4>Nuestros valores</h4>
                <ul>
                    <li>Sostenibilidad</li>
                    <li>Garantía total</li>
                    <li>Responsabilidad</li>
                </ul>
            </section>

        </div>

        <div class="contact">
            <div>
                <i class="fa-brands fa-whatsapp"></i>
                <span>+502 78562384</span>
            </div>
            <div>
                <i class="fa-solid fa-envelope"></i>
                <span>traersa2026@gmail.com</span>
            </div>
            <div>
                <i class="fa-solid fa-location-dot"></i>
                <span>31 av 2-48 zona 6 de mixco</span>
            </div>
        </div>

    </footer>
    <script>

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    entry.target.classList.add("in-view");
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll("[data-reveal]").forEach(el => revealObserver.observe(el));

        const sidebar = document.getElementById("sidebar");
        const menuToggle = document.getElementById("menuToggle");
        const overlay = document.getElementById("overlay");
        const mainContent = document.getElementById("mainContent");

        window.addEventListener("load", () => {
            if(window.innerWidth > 900){
                sidebar.classList.add("closed");
                mainContent.classList.add("full");
            }
        });

        menuToggle.addEventListener("click", () => {
            if(window.innerWidth <= 900){
                sidebar.classList.toggle("mobile-active");
                overlay.classList.toggle("active");
            }else{
                sidebar.classList.toggle("closed");
                mainContent.classList.toggle("full");
            }
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("mobile-active");
            overlay.classList.remove("active");
        });

        const currentPage = location.pathname.split("/").pop();
        document.querySelectorAll(".menu-item, .nav-icon[href]").forEach(item => {
            if(item.getAttribute("href") === currentPage){
                item.classList.add("active");
            }
        });

        function guardarComentario(boton){

            const box = boton.closest(".comment-box");
            const envioId = box.dataset.envio;
            const textarea = box.querySelector(".comment-input");
            const msg = box.querySelector(".comment-msg");
            const comentario = textarea.value.trim();

            if(comentario === ""){
                msg.textContent = "Escribe algo antes de guardar.";
                msg.classList.add("show");
                return;
            }

            boton.disabled = true;

            fetch("procesar_comentario_cliente.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "envio_id=" + encodeURIComponent(envioId) + "&comentario=" + encodeURIComponent(comentario)
            })
            .then(res => res.json())
            .then(data => {
                msg.textContent = data.message;
                msg.classList.add("show");
                if(data.success){
                    textarea.outerHTML = `<p class="comment-saved">${comentario.replace(/</g, "&lt;")}</p>`;
                    boton.outerHTML = '<button type="button" class="btn-comment-edit" onclick="editarComentario(this)">Editar comentario</button>';
                }
            })
            .catch(() => {
                msg.textContent = "Ocurrió un error de conexión.";
                msg.classList.add("show");
            })
            .finally(() => { boton.disabled = false; });
        }

        function editarComentario(boton){
            const box = boton.closest(".comment-box");
            const actual = box.querySelector(".comment-saved").textContent;
            box.querySelector(".comment-saved").outerHTML = `<textarea class="comment-input" maxlength="500">${actual}</textarea>`;
            boton.outerHTML = '<button type="button" class="btn-comment-save" onclick="guardarComentario(this)">Guardar comentario</button>';
        }

    </script>

</body>

</html>