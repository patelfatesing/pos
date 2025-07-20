
        // Keypad functionality
        document.querySelectorAll('.keypad-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const value = this.textContent.trim();
                console.log('Keypad pressed:', value);
                // Add keypad logic here
            });
        });

        // Payment button functionality
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const paymentType = this.textContent.trim();
                console.log('Payment method selected:', paymentType);
                // Add payment logic here
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.table-row');
                if (row) {
                    row.remove();
                    // Update totals here
                }
            });
        });

        // Menu item hover effects
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
