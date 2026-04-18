<?php
/**
 * USER FOOTER
 * Location: app/views/user/footer.php
 */
?>

<footer style="background: linear-gradient(135deg, #1F2937, #111827); color: white; padding: 3rem 2rem; margin-top: 4rem;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <!-- Company Info -->
            <div>
                <h4 style="font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-gift"></i> Sinta
                </h4>
                <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 1rem;">
                    Professional event planning services for all occasions. Making your celebrations unforgettable.
                </p>
                <div style="display: flex; gap: 1rem;">
                    <a href="#" style="color: white; opacity: 0.7; transition: var(--transition);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" style="color: white; opacity: 0.7; transition: var(--transition);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" style="color: white; opacity: 0.7; transition: var(--transition);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 style="font-weight: 700; margin-bottom: 1rem;">Quick Links</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="/SINTA/public/index.php?route=packages" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> Browse Packages
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="/SINTA/public/index.php?route=occasions" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> Event Types
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="/SINTA/public/index.php?route=about" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> About Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 style="font-weight: 700; margin-bottom: 1rem;">Support</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="/SINTA/public/index.php?route=messages" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> Contact Us
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> FAQ
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="#" style="color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.8)'">
                            <i class="fas fa-arrow-right"></i> Terms & Privacy
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 style="font-weight: 700; margin-bottom: 1rem;">Newsletter</h4>
                <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 1rem;">Subscribe to get updates on new packages and special offers</p>
                <form style="display: flex; gap: 0.5rem;">
                    <input type="email" placeholder="Your email" style="flex: 1; padding: 0.5rem; border: none; border-radius: 4px; font-size: 0.9rem;">
                    <button type="submit" class="btn btn--primary" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Divider -->
        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem; text-align: center; color: rgba(255,255,255,0.6); font-size: 0.85rem;">
            <p style="margin: 0;">&copy; <?php echo date('Y'); ?> Sinta Event Planning. All rights reserved.</p>
        </div>
    </div>
</footer>
