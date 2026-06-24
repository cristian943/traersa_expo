<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard TRAERSA</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="..css/ECA.css" rel="stylesheet">

</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@700&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        background: #050505;
        color: #fff;
        min-height: 100vh;
        display: flex;
        overflow: hidden;
    }

    /* TITULOS H1 */
    h1 {
        font-family: 'Bebas Neue', sans-serif;
        letter-spacing: 2px;
    }

    /* SUBTITULOS */
    h2,
    h3,
    h4,
    h5,
    h6,
    .menu-title,
    .table-header h3 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    /* CONTENIDO GENERAL */
    p,
    span,
    small,
    li,
    a,
    button,
    input,
    textarea,
    label,
    .user-info,
    .stat-info {
        font-family: 'Arial Nova', Arial, sans-serif;
    }

    /* SCROLL */

    ::-webkit-scrollbar {
        width: 7px;
    }

    ::-webkit-scrollbar-thumb {
        background: #9b111e;
        border-radius: 20px;
    }

    /* BOTON MENU */

    .menu-toggle {
        position: fixed;
        top: 18px;
        left: 18px;
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 3000;
        transition: .4s;
        box-shadow: 0 10px 25px rgba(211, 47, 47, .35);
    }

    .menu-toggle:hover {
        transform: scale(1.08);
    }

    .menu-toggle i {
        color: #fff;
        font-size: 18px;
    }

    /* =========================
   FORMULARIO PAQUETERIA
========================= */

    .gallery-top {
        background: linear-gradient(145deg, #0d0d0d, #070707);
        border: 1px solid #1d1d1d;
        border-radius: 24px;
        padding: 30px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 22px;
        align-items: end;
        box-shadow: 0 0 30px rgba(0, 0, 0, .45);
    }

    /* TITULO */

    .gallery-top h2 {
        grid-column: 1/-1;
        font-size: 42px;
        letter-spacing: 2px;
        color: #fff;
        margin-bottom: 10px;
    }

    /* INPUTS */

    .gallery-input {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .gallery-input label {
        font-size: 12px;
        font-weight: 700;
        color: #f1f1f1;
        letter-spacing: 1.5px;
    }

    /* INPUTS / SELECT / TEXTAREA */

    .gallery-input input,
    .gallery-input select,
    .gallery-input textarea {
        width: 100%;
        height: 50px;
        border-radius: 14px;
        border: 1px solid #232323;
        background: #0f0f0f;
        padding: 0 16px;
        color: #fff;
        outline: none;
        transition: .3s ease;
        font-size: 14px;
    }

    .gallery-input textarea {
        min-height: 90px;
        padding-top: 14px;
        resize: none;
    }

    /* FOCUS */

    .gallery-input input:focus,
    .gallery-input select:focus,
    .gallery-input textarea:focus {
        border-color: #d32f2f;
        box-shadow: 0 0 18px rgba(211, 47, 47, .25);
        background: #131313;
    }

    /* PLACEHOLDER */

    .gallery-input input::placeholder,
    .gallery-input textarea::placeholder {
        color: #777;
    }

    /* RADIO BUTTONS */

    .radio-group {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .radio-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        color: #d8d8d8;
        transition: .3s;
    }

    .radio-group label:hover {
        color: #fff;
    }

    /* RADIO CUSTOM */

    .radio-group input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #d32f2f;
        cursor: pointer;
    }

    /* PRECIO */

    .price-box input {
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        border: none;
        color: white;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0 0 20px rgba(211, 47, 47, .25);
    }

    /* BOTON */

    .gallery-btn {
        height: 52px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1px;
        cursor: pointer;
        transition: .35s;
    }

    .gallery-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(211, 47, 47, .35);
    }

    /* FECHA */

    .gallery-date {
        grid-column: 1/-1;
        text-align: right;
        color: #8d8d8d;
        font-size: 13px;
        font-weight: 700;
        margin-top: 10px;
    }

    /* FILE */

    input[type="file"] {
        padding: 10px;
        cursor: pointer;
    }

    input[type="file"]::-webkit-file-upload-button {
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        border: none;
        padding: 10px 15px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        margin-right: 10px;
        transition: .3s;
    }

    input[type="file"]::-webkit-file-upload-button:hover {
        opacity: .9;
    }

    /* RESPONSIVE */

    @media(max-width:900px) {

        .gallery-top {
            grid-template-columns: 1fr;
        }

        .radio-group {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .gallery-top h2 {
            font-size: 32px;
        }

        .gallery-date {
            text-align: center;
        }
    }

    /* OVERLAY */

    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .65);
        backdrop-filter: blur(4px);
        opacity: 0;
        visibility: hidden;
        transition: .4s;
        z-index: 998;
    }

    .overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* SIDEBAR */

    .sidebar {
        width: 270px;
        background: linear-gradient(180deg, #000000, #920505);
        border-right: 1px solid #1a1a1a;
        display: flex;
        flex-direction: column;
        padding: 22px 0;
        transition: .4s ease;
        position: relative;
        z-index: 999;
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #9b111e transparent;
    }

    .sidebar::-webkit-scrollbar {
        width: 0px;
        transition: .3s;
    }

    .sidebar:hover::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #9b111e;
        border-radius: 20px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    /* SIDEBAR RETRAIDO */

    .sidebar.closed {
        width: 90px;
    }

    .sidebar.closed .menu-item {
        padding: 13px;
    }

    .sidebar.closed .menu-section {
        padding: 0 10px;
    }

    .sidebar.closed .sidebar-bottom {
        padding: 0 10px;
    }

    /* LOGO */

    .logo-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 35px;
        padding: 0 10px;
    }

    .logo-container img {
        width: 65px;
        height: 65px;
        object-fit: contain;
        margin-bottom: 12px;
        animation: float 3s ease-in-out infinite;
    }

    .logo-container h1 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 2px;
        transition: .3s;
    }

    /* MENU */

    .menu-section {
        padding: 0 15px;
        margin-bottom: 25px;
    }

    .menu-title {
        color: #d32f2f;
        font-size: 10px;
        font-weight: 700;
        margin-bottom: 12px;
        letter-spacing: 2px;
        padding-left: 10px;
        transition: .3s;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        color: #d0d0d0;
        padding: 13px 15px;
        border-radius: 14px;
        margin-bottom: 8px;
        transition: .3s;
        position: relative;
        overflow: hidden;
    }

    .menu-item span {
        transition: .3s;
        white-space: nowrap;
    }

    .menu-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
                transparent,
                rgba(255, 255, 255, .08),
                transparent);
        transition: .5s;
    }

    .menu-item:hover::before {
        left: 100%;
    }

    .menu-item:hover {
        background: #121212;
        color: #fff;
        transform: translateX(5px);
    }

    .menu-item.active {
        background: linear-gradient(135deg, #7b1111, #9b111e);
        color: #fff;
        box-shadow: 0 0 25px rgba(211, 47, 47, .35);
    }

    .menu-item i {
        min-width: 22px;
        text-align: center;
        font-size: 15px;
    }

    /* SIDEBAR CLOSED */

    .sidebar.closed .logo-container h1,
    .sidebar.closed .menu-title,
    .sidebar.closed .menu-item span,
    .sidebar.closed .btn-logout span {
        opacity: 0;
        visibility: hidden;
        display: none;
    }

    .sidebar.closed .menu-item {
        justify-content: center;
    }

    .sidebar.closed .btn-logout {
        justify-content: center;
    }

    .sidebar.closed .logo-container img {
        width: 50px;
    }

    /* LOGOUT */

    .sidebar-bottom {
        margin-top: auto;
        padding: 0 15px;
    }

    .btn-logout {
        width: 100%;
        border: none;
        padding: 14px;
        border-radius: 14px;
        background: linear-gradient(135deg, #7b1111, #a01414);
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: .3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-logout:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(211, 47, 47, .35);
    }

    /* MAIN */

    .main-content {
        flex: 1;
        overflow-y: auto;
        height: 100vh;
        padding: 28px;
        transition: .4s;
    }

    /* HEADER */

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .header-title h2 {
        font-size: 32px;
        margin-bottom: 6px;
    }

    .header-title p {
        color: #8a8a8a;
        font-size: 13px;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #0f0f0f;
        border: 1px solid #1f1f1f;
        padding: 10px 16px;
        border-radius: 15px;
        transition: .3s;
    }

    .user-profile:hover {
        border-color: #9b111e;
        box-shadow: 0 0 25px rgba(211, 47, 47, .18);
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #9b111e, #d32f2f);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .user-info {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* STATS */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
    }

    .stat-card {
        background: linear-gradient(145deg, #101010, #0a0a0a);
        border: 1px solid #1c1c1c;
        border-radius: 22px;
        padding: 22px;
        position: relative;
        overflow: hidden;
        transition: .4s;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 170px;
        height: 170px;
        background: rgba(211, 47, 47, .07);
        border-radius: 50%;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        border-color: #9b111e;
        box-shadow: 0 15px 35px rgba(211, 47, 47, .16);
    }

    .stat-icon {
        width: 65px;
        height: 65px;
        border-radius: 18px;
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        transition: .4s;
    }

    .stat-card:hover .stat-icon {
        transform: rotate(8deg) scale(1.08);
    }

    .stat-info small {
        color: #9e9e9e;
        font-size: 12px;
    }

    .stat-info h3 {
        font-size: 30px;
        margin: 5px 0;
    }

    .stat-info p {
        color: #8b8b8b;
        font-size: 12px;
    }


    /* FOOTER */
    footer {
        background: black;
        color: white;
        padding: 50px 5%;
    }

    .footer {
        margin-top: 100px;
    }

    .footer-container {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 40px;
    }

    .footer-box {
        flex: 1;
        min-width: 200px;
    }

    .footer-box h4 {
        margin-bottom: 20px;
        font-size: 28px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    .footer-box ul {
        list-style: disc;
        padding-left: 20px;
    }

    .footer-box ul li {
        margin-bottom: 10px;
        font-family: 'Arial Nova', Arial, sans-serif;
    }

    .social {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    .social i {
        font-size: 40px;
    }

    .contact {
        border-top: 1px solid gray;
        padding-top: 20px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .contact div {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 22%;
        /* 4 columnas */
        font-size: 14px;
        /* más pequeño */
    }

    .contact span {
        font-family: 'Arial Nova', Arial, sans-serif;
    }


    /* TABLAS */

    .tables-grid {
        margin-top: 28px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .table-card {
        background: linear-gradient(145deg, #101010, #0a0a0a);
        border: 1px solid #1c1c1c;
        border-radius: 22px;
        padding: 22px;
        transition: .4s;
    }

    .table-card:hover {
        border-color: #9b111e;
        box-shadow: 0 10px 30px rgba(211, 47, 47, .15);
    }

    .table-header {
        margin-bottom: 20px;
    }

    .table-header h3 {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .services-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .btn-view {
        border: none;
        background: linear-gradient(135deg, #7b1111, #d32f2f);
        color: #fff;
        padding: 10px 15px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: .3s;
    }

    .btn-view:hover {
        transform: translateY(-2px);
    }

    .table-content {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .table-row {
        display: grid;
        grid-template-columns: 50px 1fr auto auto;
        align-items: center;
        gap: 15px;
        padding: 14px;
        border-radius: 14px;
        background: #0c0c0c;
        border: 1px solid #181818;
        transition: .3s;
    }

    .table-row:hover {
        background: #121212;
        border-color: #2a2a2a;
    }

    .table-row span {
        font-weight: 800;
        font-size: 14px;
    }

    .table-row strong {
        display: block;
        font-size: 13px;
        margin-bottom: 3px;
    }

    .table-row p {
        color: #8f8f8f;
        font-size: 11px;
    }

    .table-row small {
        color: #8f8f8f;
        font-size: 11px;
    }

    .status-btn {
        border: none;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }

    .running {
        background: #111;
        border: 1px solid #ffffff30;
    }

    .completed {


        background: linear-gradient(135deg, #7b1111, #d32f2f);
    }
.gallery-row{
    display:flex;
    align-items:center;
    gap:18px;
    padding:15px;
    margin-bottom:12px;
    background:#0f0f0f;
    border:1px solid #202020;
    border-radius:15px;
    transition:.3s;
}

.gallery-row:hover{
    border-color:#9b111e;
    box-shadow:0 0 15px rgba(211,47,47,.15);
}

/* IMAGEN */

.gallery-img{
    width:120px;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-shrink:0;
}

.gallery-img img{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:12px;
    border:2px solid #252525;
}

/* INFORMACION */

.gallery-info{
    flex:1;
}

.gallery-info h3{
    font-size:18px;
    margin-bottom:8px;
    color:#fff;
}

.gallery-info p{
    font-size:13px;
    color:#cfcfcf;
    margin-bottom:4px;
    line-height:1.4;
}

.gallery-info strong{
    color:#d32f2f;
}

.gallery-info small{
    color:#888;
    display:block;
    margin-top:8px;
}

/* BOTONES */

.gallery-actions{
    display:flex;
    flex-direction:column;
    gap:8px;
    width:120px;
}

.gallery-actions button{
    width:100%;
    padding:10px;
    border:none;
    border-radius:10px;
    color:white;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
}

.gallery-actions a:first-child button{
    background:#222;
}

.gallery-actions a:last-child button{
    background:linear-gradient(135deg,#7b1111,#d32f2f);
}

/* QUITAR ENCABEZADO */

.gallery-head{
    display:none;
}


.gallery-info{
    flex:1;
}

.gallery-info h3{
    font-size:18px;
    font-weight:600;
    margin-bottom:12px;
    color:#fff;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:10px 25px;
}

.info-item{
    display:flex;
    flex-direction:column;
}

.info-item span{
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:1px;
    color:#777;
}

.info-item strong{
    font-size:14px;
    font-weight:500;
    color:#f1f1f1;
}

.fecha-envio{
    margin-top:10px;
    color:#888;
    font-size:12px;
}

    .edit-btn,
    .delete-btn {
        border: none;
        padding: 11px 18px;
        border-radius: 10px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: .3s;
    }

    .edit-btn {
        background: #111;
        border: 1px solid #ffffff20;
    }

    .delete-btn {
        background: linear-gradient(135deg, #7b1111, #d32f2f);
    }

    .edit-btn:hover,
    .delete-btn:hover {
        transform: translateY(-2px);
    }

    .gallery-row small {
        color: #8f8f8f;
        font-size: 12px;
    }

    .gallery-empty {
        width: 100%;
        padding: 60px 20px;
        text-align: center;
        color: #8f8f8f;
    }

    .gallery-empty i {
        font-size: 60px;
        margin-bottom: 15px;
        color: #d32f2f;
    }

    .gallery-empty p {
        font-size: 15px;
        letter-spacing: 1px;
        font-weight: 700;
    }

    /* RESPONSIVE */

    @media(max-width:1100px) {

        .gallery-row {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .gallery-actions {
            justify-content: center;
        }

        .gallery-img {
            display: flex;
            justify-content: center;
        }

    }

    @media(max-width:700px) {

        .gallery-top {
            flex-direction: column;
            align-items: stretch;
        }

        .gallery-input input {
            width: 100%;
        }

        .gallery-date {
            margin-left: 0;
        }

    }

    /* ANIMACIONES */

    .fade-up {
        animation: fadeUp .8s ease;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-7px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    /* RESPONSIVE */

    @media(max-width:1000px) {

        .tables-grid {
            grid-template-columns: 1fr;
        }

    }

    @media(max-width:900px) {

        body {
            overflow: auto;
        }

        .sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            height: 100vh;
            width: 270px;
        }

        .sidebar.mobile-active {
            left: 0;
        }

        .main-content {
            width: 100%;
            padding: 90px 20px 20px;
        }

        footer {
            padding: 30px 5%;
        }

    }

    @media(max-width:768px) {

        .header-title h2 {
            font-size: 25px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

    }

    @media(max-width:650px) {

        .table-row {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .services-header {
            flex-direction: column;
        }

    }

    @media(max-width:500px) {

        .main-content {
            padding: 85px 15px 15px;
        }

        .user-profile {
            width: 100%;
            justify-content: center;
        }

    }
</style>


<body>

       <div class="menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </div>

    <div class="overlay" id="overlay"></div>

   
    <aside class="sidebar fade-up" id="sidebar">

        <div class="logo-container">

            <a href="admin.php">
                <img src="imagenes/logo2.png" alt="Logo">
            </a>

            <h1>TRAERSA</h1>
        </div>




        <div class="menu-section">

            <div class="menu-title">OPERACIONES</div>

            <a href="Cotizaciones_admin.php" class="menu-item ">
                <i class="fa-solid fa-tags"></i>
                <span>Cotizaciones</span>
            </a>

            <a href="Ejecucion_admin.php" class="menu-item">
                <i class="fa-regular fa-clock"></i>
                <span>En ejecución</span>
            </a>

            <a href="Completados_admin.php" class="menu-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Completados</span>
            </a>



          </div>

         <div class="menu-section">

            <div class="menu-title">CONTENIDOS</div>


          
                        <a href="Editar_categorias.php" class="menu-item active">
                <i class="fa-solid fa-border-all"></i>
                <span>Catalogo</span>
            </a>
            

         </div>

         <div class="menu-section">

            <div class="menu-title">ADMINISTRACIÓN</div>

            <a href="Editar_Usuario.php" class="menu-item">
                <i class="fa-solid fa-users"></i>
                <span>Usuarios</span>
            </a>

        </div>

        <div class="sidebar-bottom">

           <form action="../login/logout.php" method="POST">

    <button type="submit" class="btn-logout">

        <i class="fa-solid fa-arrow-right-from-bracket"></i>

        Cerrar sesión

    </button>

</form>

        </div>

    </aside>
    <main class="main-content fade-up">

        <header class="header">

            <div class="header-title">
                <h2>Bienvenido Administrador</h2>

            </div>

            <div class="user-profile">

                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="user-info">
                    ADMIN
                </div>

            </div>

        </header>
        <!-- CONTENIDO GALERIA -->

        <div class="gallery-container fade-up">
            <?php
include("../conexion.php");

$id = $_GET['id'] ?? 0;

$titulo = "";
$descripcion = "";
$paquetes = "";
$kilogramos = "";
$tipo_entrega = "";
$precio = "";
$imagen_actual = "";

if($id > 0){

    $consulta = $conn->query("SELECT * FROM categoria WHERE id='$id'");

    if($consulta->num_rows > 0){

        $fila = $consulta->fetch_assoc();

        $titulo = $fila['titulo'];
        $descripcion = $fila['descripcion'];
        $paquetes = $fila['paquetes'];
        $kilogramos = $fila['kilogramos'];
        $tipo_entrega = $fila['tipo_entrega'];
        $precio = $fila['precio'];
        $imagen_actual = $fila['imagen'];
    }
}
?>
<?php
$modo_edicion = isset($_GET['id']);
?>
           <form action="<?= isset($_GET['editar']) ? 'actualizar_categoria.php' : 'guardar_categoria.php' ?>" 
      method="POST" 
      enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="imagen_actual" value="<?= $imagen_actual ?>">

    <!-- ARCHIVO -->
    <div class="gallery-input">
        <label>ARCHIVO / FOTO DEL PAQUETE:</label>
       <input type="file" name="imagen" accept=".png,.jpg,.jpeg">

       <?php
$mostrar_imagen = !empty($imagen_actual) ? $imagen_actual : "default-package.png";
?>

<br>
<img src="../uploads/<?= $mostrar_imagen ?>" width="100">
    </div>

    <!-- TITULO -->
    <div class="gallery-input">
        <label>TÍTULO:</label>
        <input type="text"
               name="titulo"
               value="<?= $titulo ?>"
               placeholder="Ejemplo: Envío de documentos"
               required>
    </div>

    <!-- DESCRIPCION -->
    <div class="gallery-input">
        <label>DESCRIPCIÓN:</label>
        <textarea name="descripcion"><?= $descripcion ?></textarea>
    </div>

    <!-- PAQUETES -->
    <div class="gallery-input">
        <label>NÚMERO DE PAQUETES:</label>
        <input type="number"
               name="paquetes"
               value="<?= $paquetes ?>">
    </div>

    <!-- PESO -->
    <div class="gallery-input">
        <label>KILOGRAMOS:</label>
        <input type="number"
               step="0.01"
               name="kilogramos"
               value="<?= $kilogramos ?>">
    </div>

    <!-- TIPO DE ENTREGA -->
    <div class="gallery-input">
        <label>TIPO DE ENTREGA:</label>

        <div class="radio-group">

            <label>
                <input type="radio" name="tipo_entrega" value="Urbana"
                <?= $tipo_entrega=="Urbana" ? "checked" : "" ?>>
                Urbana
            </label>

            <label>
                <input type="radio" name="tipo_entrega" value="Departamentos"
                <?= $tipo_entrega=="Departamentos" ? "checked" : "" ?>>
                Departamentos
            </label>

            <label>
                <input type="radio" name="tipo_entrega" value="Ciudad"
                <?= $tipo_entrega=="Ciudad" ? "checked" : "" ?>>
                Ciudad
            </label>

        </div>
    </div>

   
    <!-- PRECIO -->
<div class="gallery-input">
    <label>PRECIO DEL SERVICIO (SIN IVA):</label>

    <input type="number"
           name="precio"
           step="0.01"
           value="<?= $precio ?>"
           placeholder="Ingrese el precio">

    <?php
    if($precio > 0){
        $precio_con_iva = $precio * 1.12;
        echo "<small>Precio con IVA: Q" . number_format($precio_con_iva, 2) . "</small>";
    }
    ?>
</div>

  <div style="display:flex; gap:10px; margin-top:15px;">

<?php if($modo_edicion){ ?>

    <button type="submit"
            formaction="actualizar_categoria.php"
            class="gallery-btn">
        ACTUALIZAR SERVICIO
    </button>

<?php } ?>

    <button type="submit"
            formaction="guardar_categoria.php"
            class="gallery-btn">
        AGREGAR SERVICIO
    </button>

</div>

</form>

        </div>

        <div class="gallery-table">

            <div class="gallery-header">
                <h3></h3>
            </div>

            <!-- ENCABEZADOS -->

            <div class="gallery-row gallery-head">

                <span>IMAGEN</span>
                <span>TÍTULO Y DESCRIPCIÓN</span>
                <span>ACCIONES</span>
                <span>FECHA</span>

            </div>

         

            <div class="gallery-empty">

              

               <?php

include("../conexion.php");

$resultado = $conn->query("SELECT * FROM categoria ORDER BY fecha DESC");

while($fila = $resultado->fetch_assoc()){
?>

<div class="gallery-row">

    <div class="gallery-img">
<?php
$imagen = !empty($fila['imagen']) ? trim($fila['imagen']) : "default-package.png";
?>

<img src="../uploads/<?php echo $imagen; ?>" width="120">
    
    </div>

    <div class="gallery-info">
        <h3><?php echo $fila['titulo']; ?></h3>

        <p><strong>Descripción:</strong>
        <?php echo $fila['descripcion']; ?></p>

        <p><strong>Paquetes:</strong>
        <?php echo $fila['paquetes']; ?></p>

        <p><strong>Kilogramos:</strong>
        <?php echo $fila['kilogramos']; ?> Kg</p>

        <p><strong>Tipo de entrega:</strong>
        <?php echo $fila['tipo_entrega']; ?></p>

        <p><strong>Precio:</strong>
        Q<?php echo number_format($fila['precio'],2); ?></p>

        <p><strong>Fecha:</strong>
        <?php echo $fila['fecha']; ?></p>
    </div>

    <div class="gallery-actions">

        <a href="Editar_categorias.php?id=<?php echo $fila['id']; ?>">
    <button type="button">EDITAR</button>
</a>

      <a href="eliminar_categoria.php?id=<?php echo $fila['id']; ?>"
onclick="return confirm('¿Desea eliminar este servicio?');">
    <button type="button" class="delete-btn">
        ELIMINAR
    </button>
</a>

    </div>

</div>

<hr>

<?php
}
?>

            </div>

        </div>

        </div>


        <footer class="footer">

            <div class="footer-container">

                <div class="footer-box">

                    <h4>Síguenos en redes sociales</h4>

                    <div class="social">
                        <i class="fa-brands fa-facebook"></i>
                        <i class="fa-brands fa-x-twitter"></i>
                        <i class="fa-brands fa-instagram"></i>
                        <i class="fa-brands fa-linkedin"></i>
                    </div>

                </div>

                <div class="footer-box">

                    <h4>Servicios</h4>

                    <ul>
                        <li>Gestión Aduanal</li>
                        <li>Transporte Terrestre</li>
                        <li>Transporte Marítimo</li>
                        <li>Almacenaje</li>
                        <li>Proyectos Especiales</li>
                    </ul>

                </div>

                <div class="footer-box">

                    <h4>Grupo TRAERSA</h4>

                    <ul>
                        <li>Únete a nuestro equipo</li>
                        <li>Sobre nosotros</li>
                        <li>Deseas ser proveedor</li>
                    </ul>

                </div>

                <div class="footer-box">

                    <h4>Nuestros valores</h4>

                    <ul>
                        <li>Sostenibilidad</li>
                        <li>Garantía total</li>
                        <li>Responsabilidad</li>
                    </ul>

                </div>

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

    </main>

    <script>

        const sidebar = document.getElementById("sidebar");
        const menuToggle = document.getElementById("menuToggle");
        const overlay = document.getElementById("overlay");

        menuToggle.addEventListener("click", () => {

            if (window.innerWidth <= 900) {

                sidebar.classList.toggle("mobile-active");
                overlay.classList.toggle("active");

            } else {

                sidebar.classList.toggle("closed");

            }

        });

        overlay.addEventListener("click", () => {

            sidebar.classList.remove("mobile-active");
            overlay.classList.remove("active");

        });

        const menuItems = document.querySelectorAll(".menu-item");

        menuItems.forEach(item => {

            item.addEventListener("click", () => {

                menuItems.forEach(el => {
                    el.classList.remove("active");
                });

                item.classList.add("active");

            });

        });

    </script>

</body>

</html>