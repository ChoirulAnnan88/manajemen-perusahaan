<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">Sistem Manajemen Perusahaan</h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>150</h4>
                        <p>Total Karyawan</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>25</h4>
                        <p>Total Proyek</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-project-diagram fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>89</h4>
                        <p>Total Aset</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>12</h4>
                        <p>Departemen</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Divisions Grid -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Divisi Perusahaan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($divisions as $key => $division): ?>
                    <div class="col-md-4 mb-3">
                        <a href="<?= base_url('/division/' . $key) ?>" class="card text-decoration-none">
                            <div class="card-body text-<?= $division['color'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title"><?= $division['code'] ?></h5>
                                        <p class="card-text mb-0"><?= $division['name'] ?></p>
                                    </div>
                                    <div class="fs-1">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="#" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus"></i><br>
                            Tambah Karyawan
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="#" class="btn btn-outline-success w-100">
                            <i class="fas fa-project-diagram"></i><br>
                            Buat Proyek
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="#" class="btn btn-outline-warning w-100">
                            <i class="fas fa-box"></i><br>
                            Kelola Aset
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="fas fa-file-invoice-dollar"></i><br>
                            Keuangan
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="#" class="btn btn-outline-dark w-100">
                            <i class="fas fa-chart-bar"></i><br>
                            Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>