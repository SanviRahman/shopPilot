@php
    $footerName = $footerContact?->name ?: 'ShopPilot Support';
    $footerEmail = $footerContact?->email ?: 'support@shoppilot.com';
    $footerPhone = $footerContact?->phone ?: '+880 1712-345678';
@endphp
<footer class="site-footer" id="contact">
    <div class="newsletter-wrap">
        <div class="container">
            <div class="newsletter-card">
                <div class="newsletter-copy"><span class="newsletter-icon"><i class="far fa-envelope"></i></span><div><h3>Get Exclusive Offers &amp; Updates</h3><p>Subscribe to our newsletter and never miss a deal.</p></div></div>
                <form class="newsletter-form" data-newsletter-form><input type="email" placeholder="Enter your email address" required><button type="submit">Subscribe</button></form>
            </div>
        </div>
    </div>

    <div class="container footer-grid">
        <div class="footer-brand-column">
            <a href="{{ route('website.home') }}" class="brand footer-brand" aria-label="ShopPilot home"><span class="brand-mark"><i class="fas fa-shopping-bag"></i></span><span class="brand-copy"><strong>Shop<span>Pilot</span></strong><small>Shop Smart. Live Better</small></span></a>
            <p>Your trusted online shopping destination for quality products at the best prices.</p>
            <div class="social-links"><a href="#" aria-label="Facebook" data-coming-soon="Facebook"><i class="fab fa-facebook-f"></i></a><a href="#" aria-label="YouTube" data-coming-soon="YouTube"><i class="fab fa-youtube"></i></a><a href="#" aria-label="Instagram" data-coming-soon="Instagram"><i class="fab fa-instagram"></i></a><a href="#" aria-label="LinkedIn" data-coming-soon="LinkedIn"><i class="fab fa-linkedin-in"></i></a></div>
        </div>

        <div><h4>Quick Links</h4><ul><li><a href="{{ route('website.home') }}">Home</a></li><li><a href="{{ route('website.shop') }}">Shop</a></li><li><a href="{{ route('website.shop') }}">Categories</a></li><li><a href="{{ route('website.shop', ['featured' => 1]) }}">Offers</a></li><li><a href="{{ route('website.blogs.index') }}">Blogs</a></li><li><a href="{{ route('website.about') }}">About Us</a></li><li><a href="{{ route('website.contact') }}">Contact</a></li></ul></div>

        <div><h4>Customer Care</h4><ul><li><a href="{{ route('website.track-order') }}">Track Order</a></li><li><a href="{{ route('website.policy', 'shipping-policy') }}">Shipping Policy</a></li><li><a href="{{ route('website.policy', 'return-refund-policy') }}">Return &amp; Refund</a></li><li><a href="{{ route('website.policy', 'terms-conditions') }}">Terms &amp; Conditions</a></li><li><a href="{{ route('website.policy', 'privacy-policy') }}">Privacy Policy</a></li><li><a href="{{ route('website.faq') }}">FAQ</a></li></ul></div>

        <div><h4>Contact Us</h4><ul class="contact-list"><li><i class="fas fa-user-shield"></i><span>{{ $footerName }}</span></li><li><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^+0-9]/', '', $footerPhone) }}">{{ $footerPhone }}</a></li><li><i class="fas fa-envelope"></i><a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></li><li><i class="far fa-clock"></i><span>Online support via Contact page</span></li></ul></div>

        <div class="payment-column"><h4>We Accept</h4><div class="payment-logos" aria-label="Accepted payment methods"><span class="payment-logo-card"><img src="{{ asset('assets/website/images/payments/visa.svg') }}" alt="Visa"></span><span class="payment-logo-card"><img src="{{ asset('assets/website/images/payments/mastercard.svg') }}" alt="Mastercard"></span><span class="payment-logo-card"><img src="{{ asset('assets/website/images/payments/bkash.svg') }}" alt="bKash"></span><span class="payment-logo-card"><img src="{{ asset('assets/website/images/payments/nagad.svg') }}" alt="Nagad"></span><span class="payment-logo-card payment-logo-wide"><img src="{{ asset('assets/website/images/payments/cod.svg') }}" alt="Cash on Delivery"></span></div><p class="payment-note"><i class="fas fa-shield-alt"></i> Secure payment experience</p></div>
    </div>

    <div class="container footer-bottom"><p>© {{ now()->year }} ShopPilot. All rights reserved.</p><div><a href="{{ route('website.policy', 'privacy-policy') }}">Privacy Policy</a><span>|</span><a href="{{ route('website.policy', 'terms-conditions') }}">Terms &amp; Conditions</a><span>|</span><a href="{{ route('website.faq') }}">FAQ</a></div></div>
</footer>
