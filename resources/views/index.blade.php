<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Egegen Frontend Task">
    <meta name="keywords" content="frontend, task, egegen">
    <title>Egegen Frontend Task</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body>
    <!-- Header Section -->
    <header class="header" role="banner">
        <nav class="navbar" role="navigation" aria-label="Ana navigasyon">
            <div class="nav-container">
                <!-- Logo -->
                <div class="logo">
                    <h1>LOGO</h1>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-toggle" aria-label="Menüyü aç/kapat" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Navigation Links -->
                <ul class="nav-links">
                    <li><a href="#home">Ana Sayfa</a></li>
                    <li><a href="#about">Hakkımızda</a></li>
                    <li class="dropdown">
                        <div class="dropdown-container">
                            <a href="#services" class="dropdown-link">
                                Hizmetler
                                <span class="arrow desktop-arrow">▼</span>
                            </a>
                            <button class="dropdown-arrow" aria-haspopup="true" aria-expanded="false">
                                <span class="arrow mobile-arrow">▼</span>
                            </button>
                        </div>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#web-tasarim" role="menuitem">Web Tasarım</a></li>
                            <li><a href="#mobil-app" role="menuitem">Mobil Uygulama</a></li>
                            <li><a href="#seo" role="menuitem">SEO Hizmetleri</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content" role="main">
        <!-- Grid Layout Section -->
        <section class="grid-section" aria-labelledby="grid-title">
            <h2 id="grid-title" class="section-title">Grid Layout</h2>
            <div class="grid-container">
                <div class="grid-item" style="grid-area: item1"></div>
                <div class="grid-item" style="grid-area: item2"></div>
                <div class="grid-item" style="grid-area: item3"></div>
                <div class="grid-item" style="grid-area: item4"></div>
                <div class="grid-item" style="grid-area: item5"></div>
                <div class="grid-item" style="grid-area: item6"></div>
                <div class="grid-item" style="grid-area: item7"></div>
                <div class="grid-item" style="grid-area: item8"></div>
                <div class="grid-item" style="grid-area: item9"></div>
            </div>
        </section>

        <!-- Accordion Section -->
        <section class="accordion-section" aria-labelledby="accordion-title">
            <h2 id="accordion-title" class="section-title">Sık Sorulan Sorular</h2>
            <div class="accordion">
                <article class="accordion-item active">
                    <header class="accordion-header">
                        <h3>
                            <button class="accordion-button" aria-expanded="true" aria-controls="content-1">
                                Web tasarımında hangi teknolojiler kullanılır?
                                <span class="accordion-icon">−</span>
                            </button>
                        </h3>
                    </header>
                    <div class="accordion-content" id="content-1" role="region" aria-labelledby="button-1">
                        <div class="accordion-body">
                            <p>Modern web tasarımında HTML5, CSS3, JavaScript, React, Vue.js gibi teknolojiler yaygın
                                olarak kullanılmaktadır. Responsive tasarım, SEO optimizasyonu ve kullanıcı deneyimi de
                                önemli faktörlerdir.</p>
                        </div>
                    </div>
                </article>

                <article class="accordion-item">
                    <header class="accordion-header">
                        <h3>
                            <button class="accordion-button" aria-expanded="false" aria-controls="content-2">
                                Responsive tasarım neden önemlidir?
                                <span class="accordion-icon">+</span>
                            </button>
                        </h3>
                    </header>
                    <div class="accordion-content" id="content-2" role="region" aria-labelledby="button-2">
                        <div class="accordion-body">
                            <p>Responsive tasarım, web sitenizin farklı cihazlarda (masaüstü, tablet, mobil) optimal
                                görünüm sağlamasını garanti eder. Bu, kullanıcı deneyimini artırır ve SEO
                                performansınızı iyileştirir.</p>
                        </div>
                    </div>
                </article>

                <article class="accordion-item">
                    <header class="accordion-header">
                        <h3>
                            <button class="accordion-button" aria-expanded="false" aria-controls="content-3">
                                SEO optimizasyonu nasıl yapılır?
                                <span class="accordion-icon">+</span>
                            </button>
                        </h3>
                    </header>
                    <div class="accordion-content" id="content-3" role="region" aria-labelledby="button-3">
                        <div class="accordion-body">
                            <p>SEO optimizasyonu için semantic HTML kullanımı, meta etiketler, hızlı yükleme süreleri,
                                kaliteli içerik ve uygun anahtar kelime kullanımı önemlidir. Ayrıca mobil uyumluluk da
                                kritik bir faktördür.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <p>&copy; 2025 Egegen Frontend Task</p>
    </footer>
</body>

</html>
