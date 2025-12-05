<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Prediksi Pengiriman Paket')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    
    @stack('styles')
    
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Smooth transitions */
        * {
            transition: all 0.3s ease;
        }
        
        /* Active nav link */
        .nav-link-active {
            color: #2563eb !important;
            border-bottom: 3px solid #2563eb;
        }
        
        /* Hover effects */
        .nav-link:hover {
            color: #2563eb;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-3 rounded-lg">
                        <i class="fas fa-box text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            Prediksi Paket
                        </h1>
                        <p class="text-xs text-gray-500">Kota Malang</p>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 font-medium flex items-center space-x-2 {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('data.pengiriman') }}" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 font-medium flex items-center space-x-2 {{ request()->routeIs('data.pengiriman') ? 'nav-link-active' : '' }}">
                        <i class="fas fa-database"></i>
                        <span>Data Pengiriman</span>
                    </a>
                    
                    <a href="{{ route('visualisasi') }}" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 font-medium flex items-center space-x-2 {{ request()->routeIs('visualisasi') ? 'nav-link-active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Visualisasi</span>
                    </a>
                    
                    <a href="{{ route('upload') }}" 
                       class="nav-link px-4 py-2 rounded-lg text-gray-700 font-medium flex items-center space-x-2 {{ request()->routeIs('upload') ? 'nav-link-active' : '' }}">
                        <i class="fas fa-upload"></i>
                        <span>Upload Data</span>
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700 hover:text-blue-600 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="{{ route('dashboard') }}" 
                   class="block px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                
                <a href="{{ route('data.pengiriman') }}" 
                   class="block px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg {{ request()->routeIs('data.pengiriman') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-database mr-2"></i> Data Pengiriman
                </a>
                
                <a href="{{ route('visualisasi') }}" 
                   class="block px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg {{ request()->routeIs('visualisasi') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-chart-line mr-2"></i> Visualisasi
                </a>
                
                <a href="{{ route('upload') }}" 
                   class="block px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg {{ request()->routeIs('upload') ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                    <i class="fas fa-upload mr-2"></i> Upload Data
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-12">
        <div class="container mx-auto px-6 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-600 text-sm mb-4 md:mb-0">
                    <i class="fas fa-copyright"></i> 2025 Prediksi Pengiriman Paket - Kota Malang
                </div>
                <div class="flex space-x-4">
                    <a href="https://github.com/FrankStein31/" class="text-gray-600 hover:text-blue-600">
                        <i class="fab fa-github text-xl"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/frankie-steinlie/" class="text-gray-600 hover:text-blue-600">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="https://www.instagram.com/steinliejoki" class="text-gray-600 hover:text-blue-600">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    
    @stack('scripts')
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const button = document.getElementById('mobile-menu-button');
            
            if (!menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
