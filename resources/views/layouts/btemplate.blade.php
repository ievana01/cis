<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Toko Aksesoris</title>

    <!-- Custom fonts for this template-->
    <link href=" {{ asset('btemplate/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    {{-- search table --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <!-- Custom styles for this template-->
    <link href=" {{ asset('btemplate/css/sb-admin-2.min.css') }}" rel="stylesheet">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> --}}
</head>
@yield('javascript')


<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #0A3D62">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Toko Aksesoris</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="/">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dasbor</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePenjualan"
                    aria-expanded="true" aria-controls="collapsePenjualan">
                    <i class="fa fa-line-chart"></i>
                    <span>Penjualan</span>
                </a>
                <div id="collapsePenjualan" class="collapse" aria-labelledby="headingPenjualan"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href={{ route('sales.index') }}>Order Penjualan</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Konfigurasi</h6>
                        <a class="collapse-item" href="sales-configuration">Konfigurasi Penjualan</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePembelian"
                    aria-expanded="true" aria-controls="collapsePembelian">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Pembelian</span>
                </a>
                <div id="collapsePembelian" class="collapse" aria-labelledby="headingPembelian"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('purchase.index') }}">Order Pembelian</a>
                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Konfigurasi</h6>
                        <a class="collapse-item" href="purchase-configuration">Konfigurasi Pembelian</a>
                    </div>
                </div>
            </li>
            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSediaan"
                    aria-expanded="true" aria-controls="collapseSediaan">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Sediaan</span>
                </a>
                <div id="collapseSediaan" class="collapse" aria-labelledby="headingSediaan"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Manajemen Produk</h6>
                        <a class="collapse-item" href="{{ route('product.index') }}">Produk</a>
                        <a class="collapse-item" href="{{ route('pindahProduk.index') }}">Pindah Produk</a>
                        <a class="collapse-item" href="{{ route('category.index') }}">Kategori Produk</a>
                        <a class="collapse-item" href="{{ route('warehouse.index') }}">Gudang</a>

                        {{-- <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Laporan</h6>
                        <a class="collapse-item" href="report-stock">Lokasi Stok Produk</a> --}}

                        <div class="collapse-divider"></div>
                        <h6 class="collapse-header">Konfigurasi</h6>
                        <a class="collapse-item" href="inventory-configuration">Konfigurasi Sediaan</a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseData"
                    aria-expanded="true" aria-controls="collapseData">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Data</span>
                </a>
                <div id="collapseData" class="collapse" aria-labelledby="headingData"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('dataStore.index') }}">Data Toko</a>
                        <a class="collapse-item" href="{{ route('customer.index') }}">Data Pelanggan</a>
                        <a class="collapse-item" href="{{ route('supplier.index') }}">Data Pemasok</a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport"
                    aria-expanded="true" aria-controls="collapseData">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Laporan</span>
                </a>
                <div id="collapseReport" class="collapse" aria-labelledby="headingData"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="sales-data">Laporan Produk Terjual</a>
                        <a class="collapse-item" href="purchase-data">Laporan Pembelian Produk</a>
                        <a class="collapse-item" href="/laporan-laba">Laporan Laba Kotor</a>
                    </div>
                </div>
            </li>


        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>


                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{-- <img class="img-profile rounded-circle" src="img/undraw_profile.svg"> --}}
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Halo,
                                    {{ Auth::user()->username }}</span>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <form action="{{ route('logout') }}" method="post" class="px-3 py-2">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>

                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            {{-- <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer> --}}
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src=" {{ asset('btemplate/vendor/jquery/jquery.min.js') }}"></script>
    <script src=" {{ asset('btemplate/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src=" {{ asset('btemplate/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src=" {{ asset('btemplate/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src=" {{ asset('btemplate/vendor/chart.js/Chart.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src=" {{ asset('btemplate/js/demo/chart-area-demo.js') }}"></script>
    <script src=" {{ asset('btemplate/js/demo/chart-pie-demo.js') }}"></script>

</body>

</html>
