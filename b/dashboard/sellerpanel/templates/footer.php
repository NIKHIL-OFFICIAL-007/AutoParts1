</div> <!-- Close .main-content -->

</div> <!-- Close .seller-container -->

<!-- Common JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Common JavaScript for all pages
document.addEventListener('DOMContentLoaded', function() {
    // Animation for form elements
    const formGroups = document.querySelectorAll('.form-group');
    
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        group.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
    });
    
    // File upload hover effect
    const fileUploads = document.querySelectorAll('.file-upload');
    
    fileUploads.forEach(upload => {
        const fileInput = upload.querySelector('.file-input');
        
        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                upload.innerHTML = `
                    <i class="fa-solid fa-check-circle" style="color: #2ecc71; font-size: 40px;"></i>
                    <div class="file-upload-text">
                        <h3>${this.files.length} file(s) selected</h3>
                        <p>${this.files[0].name}</p>
                    </div>
                `;
            }
        });
    });

    // Active link highlighting
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (currentPage === linkPage) {
            link.classList.add('active');
        }
    });
});

// Animation keyframes
const styleElement = document.createElement('style');
styleElement.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }
`;
document.head.appendChild(styleElement);
</script>

</body>
</html>