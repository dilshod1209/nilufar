<?php
session_start();
include 'config.php'; 

// Ma’lumotlarni bazadan olish
try {
    $flora = $pdo->query("SELECT * FROM flora ORDER BY id DESC")->fetchAll();
    $fauna = $pdo->query("SELECT * FROM fauna ORDER BY id DESC")->fetchAll();
    $orol_info = $pdo->query("SELECT * FROM orol_info ORDER BY year DESC")->fetchAll();
} catch (PDOException $e) {
    die("Bazaga ulanishda xatolik: " . $e->getMessage());
}

// Taklif yuborish logikasi
$success = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_text'])) {
    if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $subject = htmlspecialchars($_POST['subject']);
        $text = htmlspecialchars($_POST['message_text']);

        if(strlen($text) < 10) {
            $error = "Xabar mazmuni juda qisqa (kamida 10 ta belgi).";
        } else {
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, subject, message_text) VALUES (?, ?, ?)");
            if($stmt->execute([$userId, $subject, $text])) {
                $success = "Rahmat! Taklifingiz muvaffaqiyatli qabul qilindi.";
            } else {
                $error = "Xatolik yuz berdi, qaytadan urinib ko'ring.";
            }
        }
    } else {
        $error = "Taklif yuborish uchun tizimga kirishingiz shart!";
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orol Ekologiyasi - Ferdo Style</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --main-green: #2e7d32; --soft-green: #e8f5e9; }
        body { background-color: #f9fbf9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Navigatsiya */
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-pills .nav-link.active { background-color: var(--main-green); border-radius: 20px; }
        .nav-link { color: #555; font-weight: 500; }

        /* Slayder */
        .carousel-img { height: 550px; object-fit: cover; filter: brightness(0.65); }
        .carousel-caption { bottom: 25%; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); }

        /* Kartochkalar */
        .card { border: none; border-radius: 20px; transition: 0.3s; }
        .card:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .section-title { border-left: 6px solid var(--main-green); padding-left: 15px; margin: 40px 0 30px; color: var(--main-green); font-weight: 700; }

        /* Takliflar formasi */
        .feedback-card { background: white; border-radius: 25px; border: 1px solid #eee; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #ddd; }
        .form-control:focus { border-color: var(--main-green); box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.1); }
        .btn-submit { background: var(--main-green); color: white; border-radius: 12px; padding: 12px; font-weight: 600; border: none; transition: 0.3s; }
        .btn-submit:hover { background: #1b5e20; transform: scale(1.02); }

        /* --- Xarita bloki uchun maxsus dizayn --- */
        .map-section { background: white; border-radius: 25px; border: 1px solid #eee; padding: 30px; margin-top: 40px; }
        .map-wrapper { border-radius: 20px; overflow: hidden; height: 350px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .coord-pill { background: var(--soft-green); border-radius: 15px; padding: 15px; display: inline-flex; align-items: center; margin-top: 15px; }

        /* Footer */
        footer { background: #1a1a1a; color: #ccc; padding: 60px 0 30px; }
        .footer-logo { color: var(--main-green); font-weight: 800; font-size: 1.5rem; text-decoration: none; }
        .btn-tg { background: #0088cc; color: white !important; border-radius: 50px; padding: 8px 20px; text-decoration: none; display: inline-flex; align-items: center; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top bg-white">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="#">🌍 Orol Eco</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="nav nav-pills ms-auto" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#fauna">Hayvonot</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#flora">O‘simliklar</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">Tarix</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#messages">Taklif yuborish</a></li>
                </ul>
                <div class="ms-lg-4">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle rounded-pill px-3" data-bs-toggle="dropdown">
                                👤 <?= htmlspecialchars($_SESSION['user']['username']) ?>
                            </button>
                            <ul class="dropdown-menu border-0 shadow">
                                <li><a class="dropdown-item text-danger" href="logout.php">Chiqish</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success btn-sm rounded-pill px-4">Kirish</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div id="ecoSlider" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php 
            $combined = array_merge($fauna, $flora);
            shuffle($combined);
            $slides = array_slice($combined, 0, 4);
            foreach ($slides as $i => $s): 
                $name = $s['animal_name'] ?? $s['plant_name'];
            ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= htmlspecialchars($s['image']) ?>" class="d-block w-100 carousel-img">
                <div class="carousel-caption">
                    <h1 class="display-3 fw-bold text-white"><?= htmlspecialchars($name) ?></h1>
                    <p class="lead">Orolbo'yi ekotizimini birgalikda o'rganamiz va asraymiz.</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container pb-5">
        <div class="tab-content">
            
            <div class="tab-pane fade show active" id="fauna">
                <h2 class="section-title">🐾 Hayvonot dunyosi</h2>
                <div class="row">
                    <?php foreach ($fauna as $a): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm overflow-hidden">
                            <img src="<?= htmlspecialchars($a['image']) ?>" class="card-img-top" style="height:220px; object-fit:cover;">
                            <div class="card-body">
                                <h5 class="fw-bold"><?= htmlspecialchars($a['animal_name']) ?></h5>
                                <p class="text-muted small"><?= mb_strimwidth($a['description'], 0, 120, "...") ?></p>
                                <button class="btn btn-outline-success btn-sm rounded-pill w-100">Batafsil ma'lumot</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="map-section shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="badge bg-success mb-2">📍 Joylashuv Monitoringi</span>
                            <h3 class="fw-bold">Orol Dengizi <span class="text-success">Hududi</span></h3>
                            <p class="text-muted">Hudud ekologik ahamiyatga ega va raqamli xaritalar orqali kuzatiladi.</p>
                            <div class="coord-pill">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">🚀</div>
                                <div>
                                    <div class="small fw-bold text-secondary">KOORDINATALAR</div>
                                    <div class="fw-bold">45.0000° N, 59.0000° E</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 mt-md-0">
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3106362.8398492043!2d57.6591024375!3d44.89437145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x41e8605c3127815d%3A0xe5a363d6f1c422c5!2sAral%20Sea!5e0!3m2!1sen!2s!4v1712345678901" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="flora">
                <h2 class="section-title">🌱 O'simliklar dunyosi</h2>
                <div class="row">
                    <?php foreach ($flora as $f): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm overflow-hidden">
                            <img src="<?= htmlspecialchars($f['image']) ?>" class="card-img-top" style="height:220px; object-fit:cover;">
                            <div class="card-body">
                                <h5 class="fw-bold"><?= htmlspecialchars($f['plant_name']) ?></h5>
                                <p class="text-muted small"><?= mb_strimwidth($f['description'], 0, 120, "...") ?></p>
                                <button class="btn btn-outline-success btn-sm rounded-pill w-100">Batafsil ma'lumot</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="map-section shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="badge bg-success mb-2">📍 Joylashuv Monitoringi</span>
                            <h3 class="fw-bold">Orol Dengizi <span class="text-success">Hududi</span></h3>
                            <p class="text-muted">Hudud ekologik ahamiyatga ega va raqamli xaritalar orqali kuzatiladi.</p>
                            <div class="coord-pill">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">🚀</div>
                                <div>
                                    <div class="small fw-bold text-secondary">KOORDINATALAR</div>
                                    <div class="fw-bold">45.0000° N, 59.0000° E</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 mt-md-0">
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3106362.8398492043!2d57.6591024375!3d44.89437145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x41e8605c3127815d%3A0xe5a363d6f1c422c5!2sAral%20Sea!5e0!3m2!1sen!2s!4v1712345678901" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="history">
                <h2 class="section-title">📜 Tarixiy voqealar va ishlar</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <?php foreach ($orol_info as $info): ?>
                        <div class="card p-4 mb-4 shadow-sm border-0 bg-white">
                            <div class="d-flex align-items-start">
                                <div class="badge bg-success p-3 rounded-4 me-3 fs-5"><?= $info['year'] ?></div>
                                <div>
                                    <h4 class="fw-bold text-dark"><?= htmlspecialchars($info['title']) ?></h4>
                                    <p class="text-muted"><?= htmlspecialchars($info['description']) ?></p>
                                    <small class="text-secondary fw-bold">Mas'ul: <?= htmlspecialchars($info['leader']) ?></small>
                                </div>
                            </div>
                            <?php if($info['image']): ?>
                                <img src="<?= $info['image'] ?>" class="img-fluid rounded-4 mt-3">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="map-section shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="badge bg-success mb-2">📍 Joylashuv Monitoringi</span>
                            <h3 class="fw-bold">Orol Dengizi <span class="text-success">Hududi</span></h3>
                            <p class="text-muted">Hudud ekologik ahamiyatga ega va raqamli xaritalar orqali kuzatiladi.</p>
                            <div class="coord-pill">
                                <div class="bg-white rounded-circle p-2 me-3 shadow-sm">🚀</div>
                                <div>
                                    <div class="small fw-bold text-secondary">KOORDINATALAR</div>
                                    <div class="fw-bold">45.0000° N, 59.0000° E</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 mt-md-0">
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3106362.8398492043!2d57.6591024375!3d44.89437145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x41e8605c3127815d%3A0xe5a363d6f1c422c5!2sAral%20Sea!5e0!3m2!1sen!2s!4v1712345678901" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="messages">
                <h2 class="section-title">💡 Sizning taklifingiz biz uchun muhim</h2>
                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="feedback-card p-5 shadow-sm">
                            <?php if($success): ?>
                                <div class="alert alert-success border-0 rounded-4 p-3 animate__animated animate__fadeIn">
                                    ✅ <?= $success ?>
                                </div>
                            <?php endif; ?>

                            <?php if($error): ?>
                                <div class="alert alert-danger border-0 rounded-4 p-3">
                                    ❌ <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['user'])): ?>
                                <form method="POST" action="#messages">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Taklif mavzusi</label>
                                        <select name="subject" class="form-select" required>
                                            <option value="Ekologiyani asrash">Ekologiyani asrash</option>
                                            <option value="Daraxt ekish aktsiyasi">Daraxt ekish aktsiyasi</option>
                                            <option value="Suv muammosi">Suv muammosi</option>
                                            <option value="Boshqa">Boshqa</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Xabar mazmuni</label>
                                        <textarea name="message_text" class="form-control" rows="6" placeholder="Orol muammosini hal qilish uchun qanday yechim taklif qilasiz?..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn-submit w-100 shadow-sm">Taklifni yuborish</button>
                                </form>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="mb-4 fs-1">🔒</div>
                                    <h5 class="fw-bold">Taklif yuborish uchun login qiling</h5>
                                    <p class="text-muted px-4">Sizning fikringiz biz uchun qadrli. Iltimos, hisobingizga kiring va o'z fikringizni yozing.</p>
                                    <a href="login.php" class="btn btn-success rounded-pill px-5">Kirish / Ro'yxatdan o'tish</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <a href="#" class="footer-logo">🌍 Orol Eco</a>
                    <p class="mt-3 small">Orolbo'yi mintaqasining noyob florasi va faunasini asrash bizning asosiy maqsadimizdir.</p>
                </div>
                <div class="col-md-4 text-center">
                    <h6 class="text-white fw-bold mb-3">Bo'limlar</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#fauna" class="text-secondary text-decoration-none d-block mb-2">Hayvonot</a></li>
                        <li><a href="#flora" class="text-secondary text-decoration-none d-block mb-2">O'simliklar</a></li>
                        <li><a href="#history" class="text-secondary text-decoration-none d-block mb-2">Tarixiy ishlar</a></li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end">
                    <h6 class="text-white fw-bold mb-3">Biz bilan bog'laning</h6>
                    <p class="small mb-3">Savollaringiz bormi? Yozing:</p>
                    <a href="https://t.me/berdiboyevaaaa" target="_blank" class="btn-tg">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" width="20" class="me-2">
                        Telegram orqali yozish
                    </a>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-3 text-center">
                <p class="small">&copy; <?= date('Y') ?> Ferdo Style - Barcha huquqlar himoyalangan.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>