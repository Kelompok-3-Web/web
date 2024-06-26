<?php
include_once "model/survey/m_survey.php";
include_once "model/survey/m_survey_soal.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['nama']) && empty($_SESSION['username']) && empty($_SESSION['user_id'])) {
  header("location:login.php");
  exit; // Important to exit after header redirect
}

$menu = !empty($_GET['pages']) ? explode('/', $_GET['pages'])[0] : "index";
$sub_menu = !empty($_GET['sub_menu']) ? explode('/', $_GET['sub_menu'])[0] : "";

if (!empty($_GET['survey_id'])) {
  $surveyId = $_GET['survey_id'];
  $surveyModel = new mSurvey();
  $surveyResult = $surveyModel->getDataById($surveyId);

  if ($surveyResult) {
    $survey = $surveyResult->fetch_assoc();
  } else {
    // Handle case where the survey result is not found or query fails
    $survey = null;
    // Optionally log the error or set an error message
  }
} else {
  // Handle the case where survey_id is not provided
  $survey = null;
  // Optionally log the error or set an error message
}

$kategori = array("Layanan Akademik", "Layanan Non Akademik", "Sarana dan Prasarana");
// echo __DIR__ . "/model/jawaban";
$dir = new DirectoryIterator(dirname(__DIR__ . "/model/jawaban"));
$skor = array();

function ambildatagrafik($table)
{
  $kategori_id = [2, 3, 4];
  $hasil = array();

  foreach ($kategori_id as $_ => $value) {
    include "model/koneksi.php";
    // query
    $query = $db->prepare("
            SELECT COUNT(jawab.jawaban) as total_jawaban, jawaban
            FROM {$table} as jawab 

            JOIN m_survey_soal as soal 
                ON jawab.soal_id = soal.soal_id
                
            WHERE soal.kategori_id = {$value}
            GROUP BY jawaban;
        ");

    $query->execute();
    $result = $query->get_result();

    $dat = array();
    while ($row = $result->fetch_assoc()) {
      $dat[$row['jawaban']] = $row['total_jawaban'];
    }
    $hasil[$value] = $dat;
  }

  return $hasil;
}

$tabel = ["t_jawaban_alumni", "t_jawaban_dosen", "t_jawaban_industri", "t_jawaban_mahasiswa", "t_jawaban_ortu", "t_jawaban_tendik"];
$sangat_puas = array(0, 0, 0);
$puas = array(0, 0, 0);
$tidak_puas = array(0, 0, 0);
$sangat_tidak_puas = array(0, 0, 0);

foreach ($tabel as $_ => $value) {
  $apalah = ambildatagrafik($value);

  foreach ($apalah as $key => $value) {
    if ($key === 2) {
      $tro = 0;
    } elseif ($key === 3) {
      $tro = 1;
    } else {
      $tro = 2;
    }


    @$sangat_puas[$tro] += $value[4];
    @$puas[$tro] += $value[3];
    @$tidak_puas[$tro] += $value[2];
    @$sangat_tidak_puas[$tro] += $value[1];
  }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web Survey Polinema | <?php echo isset($_GET['pages']) ? ucfirst($_GET['pages']) : "Dashboard" ?></title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <!-- Tema style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- CSS BOX -->
  <link rel="stylesheet" href="style.css">
  <!-- jQuery calender -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="calender.css">

</head>

<body class="hold-transition sidebar-mini layout-fixed" style="height: 100%;">
  <div class="wrapper">
    <!-- Navbar -->
    <?php include_once "layouts/header.php" ?>
    <!-- Sidebar -->
    <?php include_once "layouts/sidebar.php" ?>

    <div class="content-wrapper">
      <?php
      if (!empty($_GET['pages'])) {
        include "pages/" . $_GET['pages'] . (!strpos($_GET['pages'], ".php") ? "/index.php" : "");
      } else {
      ?>
        <section class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1>Dashboard</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
            <section class="content">
              <div class="container-fluid">
                <?php
                include_once "model/m_dashboard.php";
                $obj = new mDashboard();
                ?>
                <div class="row">
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-info">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenDosen()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Dosen</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-user-tie"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-purple">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenMahasiswa()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Mahasiswa</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-user"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-success">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenMahasiswa()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Alumni</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenOrtu()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Orang Tua</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-user-friends"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-primary">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenTendik()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Tendik</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-user-gear"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 col-6">
                    <div class="small-box bg-secondary">
                      <div class="inner">
                        <p>
                        <h3><?php echo $obj->jumlahRespondenIndustri()->fetch_assoc()['jumlah'] ?></h3>
                        </p>
                        <br>
                        <p>Industri</p>
                      </div>
                      <div class="icon">
                        <i class="fas fa-city"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="container" style="width: 100%;">
                  <div class="row">
                    <div class="col-md-9">
                      <h4>Hasil Form Survey</h4>
                      <div class="chart-container" style="position: relative; height:40vh;">
                        <canvas id="grafikKepuasan"></canvas>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <h4>Calender</h4>
                      <div id="datepicker"></div>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </section>
      <?php } ?>
    </div>

    <?php include_once "layouts/footer.php" ?>
  </div>
  <!-- Bootstrap 4 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
  <!-- chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Data untuk grafik
    var kategori = <?php echo json_encode($kategori); ?>;
    var sangat_puas = <?php echo json_encode($sangat_puas); ?>;
    var puas = <?php echo json_encode($puas); ?>;
    var tidak_puas = <?php echo json_encode($tidak_puas); ?>;
    var sangat_tidak_puas = <?php echo json_encode($sangat_tidak_puas); ?>;

    // Membuat grafik
    var ctx = document.getElementById('grafikKepuasan').getContext('2d');
    var grafikKepuasan = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: kategori,
        datasets: [{
            label: 'Sangat Puas',
            data: sangat_puas,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          },
          {
            label: 'Puas',
            data: puas,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          },
          {
            label: 'Tidak Puas',
            data: tidak_puas,
            backgroundColor: 'rgba(255, 206, 86, 0.2)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1
          },
          {
            label: 'Sangat Tidak Puas',
            data: sangat_tidak_puas,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
          }
        ]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Inisialisasi Datepicker
    $(function() {
      $("#datepicker").datepicker();
    });
  </script>
</body>

</html>