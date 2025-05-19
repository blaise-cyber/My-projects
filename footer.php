</main>
    <footer>
        <style>
            .footer {
                background-color: #333;
                color: white;
                padding: 15px 20px;
                margin-top: 450px;
                min-height: 80px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                position: relative;
            }
            .footer p {
                margin: 0;
            }
            .social-icons {
                margin-left: auto;
                margin-right: 20px;
                display: flex;
                align-items: center;
            }
            .social-icons a {
                color: white;
                margin: 0 12px;
                font-size: 20px;
                text-decoration: none;
                transition: color 0.3s;
                position: relative;
            }
            .social-icons a:hover {
                color: #ccc;
            }
            .social-icons a[aria-label]::after {
                content: attr(aria-label);
                position: absolute;
                top: -30px;
                left: 50%;
                transform: translateX(-50%);
                background: #555;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                opacity: 0;
                transition: opacity 0.3s;
                pointer-events: none;
            }
            .social-icons a:hover::after {
                opacity: 1;
            }
            .copyright {
                margin-left: 20px;
            }
            .newsletter {
                margin: 0 20px;
            }
            .newsletter a {
                color: #fff;
                text-decoration: none;
                border: 1px solid #fff;
                padding: 5px 10px;
                border-radius: 4px;
                transition: background 0.3s;
            }
            .newsletter a:hover {
                background: #555;
            }
            .back-to-top {
                position: absolute;
                right: 20px;
                bottom: 80px;
                background: #555;
                color: white;
                padding: 10px;
                border-radius: 50%;
                text-decoration: none;
                font-size: 18px;
                display: none;
            }
            .back-to-top:hover {
                background: #777;
            }
            @media (max-width: 768px) {
                .footer {
                    flex-direction: column;
                    align-items: flex-start;
                    padding: 20px;
                    height: auto;
                }
                .social-icons {
                    margin: 10px 0;
                    justify-content: center;
                    width: 100%;
                }
                .copyright, .newsletter {
                    margin: 5px 0;
                }
                .newsletter {
                    text-align: center;
                    width: 100%;
                }
            }
        </style>
        <!-- Add Font Awesome for social media and other icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        
        <div class="footer">
            <p class="copyright">© 2025 Online Quiz System</p>
            <div class="newsletter">
                <a href="/subscribe" aria-label="Subscribe to newsletter">Subscribe to Updates</a>
            </div>
            <div class="social-icons">
                <a href="https://www.facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://www.youtube.com" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://www.pinterest.com" target="_blank" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
            </div>
            <a href="#" class="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up"></i></a>
        </div>

        <script>
            // Show/hide back-to-top button based on scroll position
            window.addEventListener('scroll', function() {
                const backToTop = document.querySelector('.back-to-top');
                if (window.scrollY > 300) {
                    backToTop.style.display = 'block';
                } else {
                    backToTop.style.display = 'none';
                }
            });
        </script>
    </footer>
</body>
</html>