<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .division-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .module-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        .module-card:hover {
            transform: translateY(-5px);
        }
        .nav-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <div class="nav-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard Utama
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <a href="/" class="btn btn-outline-primary me-2">
                            <i class="fas fa-home me-1"></i>Homepage
                        </a>
                        <a href="/auth/profile" class="btn btn-outline-info me-2">
                            <i class="fas fa-user me-1"></i>Profile
                        </a>
                        <a href="/auth/logout" class="btn btn-outline-danger" 
                           onclick="return confirm('Yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="division-header">
            <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard Manajemen Perusahaan</h1>
            <p class="mb-0">Selamat datang, <strong><?= $user['nama_lengkap'] ?></strong> (<?= $user['role'] ?> - <?= $user['divisi'] ?>)</p>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card module-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Karyawan</h6>
                                <h3>150</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card module-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Proyek</h6>
                                <h3>25</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-project-diagram fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card module-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Aset</h6>
                                <h3>89</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card module-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Departemen</h6>
                                <h3>6</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divisions Access -->
        <div class="row">
            <div class="col-12">
                <div class="card module-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-th-large me-2"></i>Akses Divisi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (empty($menu)): ?>
                                <div class="col-12 text-center py-4">
                                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Anda tidak memiliki akses ke divisi manapun</h5>
                                    <p class="text-muted">Hubungi administrator untuk mendapatkan akses</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($menu as $item): ?>
                                <div class="col-md-4 mb-4">
                                    <a href="<?= $item['url'] ?>" class="card module-card text-decoration-none">
                                        <div class="card-body text-center">
                                            <i class="<?= $item['icon'] ?> fa-3x text-primary mb-3"></i>
                                            <h5 class="card-title"><?= $item['name'] ?></h5>
                                            <p class="card-text">Klik untuk mengelola divisi <?= $item['name'] ?></p>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card module-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Informasi Pengguna
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="30%"><strong>Nama Lengkap</strong></td>
                                <td><?= $user['nama_lengkap'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username</strong></td>
                                <td><?= $user['username'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Role</strong></td>
                                <td><span class="badge bg-primary"><?= ucfirst($user['role']) ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Divisi</strong></td>
                                <td><?= $user['divisi'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card module-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informasi Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-check text-success me-2"></i>Sistem Manajemen Perusahaan v1.0</p>
                        <p><i class="fas fa-check text-success me-2"></i>Modul HRGA sudah aktif</p>
                        <p><i class="fas fa-check text-success me-2"></i>Authentication system berjalan</p>
                        <p><i class="fas fa-clock text-warning me-2"></i>Modul lain dalam pengembangan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>