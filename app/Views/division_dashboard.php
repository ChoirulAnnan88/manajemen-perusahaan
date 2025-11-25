<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>
        
        <h1 class="h3 mb-4"><?= $title ?></h1>
    </div>
</div>

<!-- Division Specific Content -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informasi Divisi</h5>
            </div>
            <div class="card-body">
                <p>Halaman dashboard khusus untuk divisi <?= $title ?>.</p>
                <p>Fitur yang tersedia:</p>
                <ul>
                    <li>Manajemen data divisi</li>
                    <li>Laporan kinerja</li>
                    <li>Monitoring aktivitas</li>
                    <li>Analisis data</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistik Divisi</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total Karyawan:</strong> 25
                </div>
                <div class="mb-3">
                    <strong>Proyek Aktif:</strong> 5
                </div>
                <div class="mb-3">
                    <strong>Target Bulanan:</strong> 85%
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <a href="#" class="btn btn-primary btn-sm w-100 mb-2">Tambah Data</a>
                <a href="#" class="btn btn-success btn-sm w-100 mb-2">Generate Laporan</a>
                <a href="#" class="btn btn-info btn-sm w-100">Pengaturan</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>