<!-- Footer -->
    <footer class="bg-primary text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <svg class="h-8 w-8 mr-3" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="white"/>
                            <path d="M12 20c0-4.4 3.6-8 8-8s8 3.6 8 8-3.6 8-8 8-8-3.6-8-8z" fill="#75A347"/>
                            <path d="M16 18h8v4h-8z" fill="#FF6B35"/>
                            <circle cx="20" cy="20" r="2" fill="#2D5016"/>
                        </svg>
                        <div>
                            <h3 class="text-xl font-bold font-accent">FarmScout Online</h3>
                            <p class="text-primary-200">Tapat na Presyo, Tapat na Serbisyo</p>
                        </div>
                    </div>
                    <p class="text-primary-200 mb-4">
                        Your trusted digital guide to Baloan Public Market. Empowering Filipino families with transparent, real-time pricing information for smarter market shopping.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-primary-200 hover:text-white transition-colors">Home</a></li>
                        <li><a href="categories.php" class="text-primary-200 hover:text-white transition-colors">Categories</a></li>
                        <li><a href="market-info.php" class="text-primary-200 hover:text-white transition-colors">Market Info</a></li>
                        <li><a href="quick-check.php" class="text-primary-200 hover:text-white transition-colors">Mobile Checker</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-primary-200">
                        <li>Baloan Public Market</li>
                        <li>La Union, Philippines</li>
                        <li>farmscout@email.com</li>
                        <li>+63 912 345 6789</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-primary-600 mt-8 pt-8 text-center text-primary-200">
                <p>&copy; 2025 FarmScout Online. All Rights Reserved. | Serving the Filipino community with transparency and trust.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('mobile-menu');
            const button = e.target.closest('button');
            
            if (!menu.contains(e.target) && !button) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>