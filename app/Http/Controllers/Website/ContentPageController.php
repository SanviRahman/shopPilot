<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    public function about(): View
    {
        return view('website.pages.about', [
            'customerCount' => User::query()->count(),
            'productCount' => Product::query()->where('status', 'active')->count(),
            'categoryCount' => Category::query()->where('status', 'active')->count(),
        ]);
    }

    public function faq(): View
    {
        return view('website.pages.faq', ['faqGroups' => $this->faqGroups()]);
    }

    public function policy(string $slug): View
    {
        $policies = $this->policies();
        abort_unless(array_key_exists($slug, $policies), 404);

        return view('website.pages.policy', ['policySlug' => $slug, 'policy' => $policies[$slug], 'policies' => $policies]);
    }

    private function faqGroups(): array
    {
        return [
            'orders' => ['label' => 'Orders', 'icon' => 'fa-box', 'items' => [
                ['q' => 'How do I place an order on ShopPilot?', 'a' => 'Browse products, add the items you want to your cart, proceed to checkout, provide delivery information, choose a payment method and confirm your order.'],
                ['q' => 'Can I track my order?', 'a' => 'Yes. Open Track Order from the top bar and enter your order number with the email address or phone number used during checkout.'],
                ['q' => 'Can I buy a product without keeping it in my cart?', 'a' => 'Yes. Use Buy Now on the product details page to open checkout for that product without changing your normal shopping cart.'],
            ]],
            'shipping' => ['label' => 'Shipping', 'icon' => 'fa-truck', 'items' => [
                ['q' => 'How long does delivery take?', 'a' => 'Delivery time depends on your location and the selected delivery option. The final delivery method and any fee are shown during checkout.'],
                ['q' => 'Do you offer free delivery?', 'a' => 'Free delivery may be available for eligible orders or promotions. The checkout summary always shows the final shipping amount before you place the order.'],
            ]],
            'payments' => ['label' => 'Payments', 'icon' => 'fa-credit-card', 'items' => [
                ['q' => 'What payment methods do you accept?', 'a' => 'Available payment methods are shown at checkout. ShopPilot can support enabled manual payment methods such as bKash or Nagad based on admin configuration.'],
                ['q' => 'Why is my payment status submitted but not verified?', 'a' => 'A submitted manual payment must be reviewed before it becomes verified. Your order page will show the latest payment status.'],
            ]],
            'returns' => ['label' => 'Returns', 'icon' => 'fa-sync-alt', 'items' => [
                ['q' => 'What is your return and refund policy?', 'a' => 'Eligible products can be returned according to the conditions on the Return & Refund Policy page. Products should normally remain unused and in their original condition.'],
                ['q' => 'I received a damaged or wrong item. What should I do?', 'a' => 'Contact support as soon as possible with your order number and details of the issue so the team can review the case.'],
            ]],
            'account' => ['label' => 'Account', 'icon' => 'fa-user', 'items' => [
                ['q' => 'Do I need an account to place an order?', 'a' => 'Guest checkout is supported. Creating an account gives you faster access to orders, wishlist, profile settings and payment submissions.'],
                ['q' => 'How do I change my password?', 'a' => 'Sign in, open My Account, choose Change Password, verify your current password and save a new secure password.'],
            ]],
            'support' => ['label' => 'Support', 'icon' => 'fa-headset', 'items' => [
                ['q' => 'How can I contact ShopPilot support?', 'a' => 'Open the Contact page to find the active phone number, email address, location map and the customer message form.'],
                ['q' => 'Can I send a message from the website?', 'a' => 'Yes. Use the contact form and your message will be stored securely for the support team to review.'],
            ]],
        ];
    }

    private function policies(): array
    {
        return [
            'shipping-policy' => [
                'title' => 'Shipping Policy', 'icon' => 'fa-truck', 'updated' => 'October 03, 2026', 'intro' => 'This policy explains how ShopPilot prepares orders, calculates delivery options and shares shipment information.',
                'sections' => [
                    ['title' => 'Order Processing', 'icon' => 'fa-box-open', 'text' => 'Orders are reviewed after submission and processed according to stock availability and payment status.', 'points' => ['Order details are confirmed before fulfillment.', 'Available delivery options are shown during checkout.', 'Processing can take longer during holidays or unusually high order volume.'], 'tone' => 'blue'],
                    ['title' => 'Delivery Information', 'icon' => 'fa-shipping-fast', 'text' => 'Delivery timing and fees depend on destination, courier availability and the selected delivery method.', 'points' => ['Keep your phone number and delivery address accurate.', 'Track Order can be used to review the latest order timeline.', 'Shipping charges are displayed before the order is submitted.'], 'tone' => 'green'],
                    ['title' => 'Delivery Issues', 'icon' => 'fa-exclamation-circle', 'text' => 'If an order is delayed, returned or cannot be delivered, contact support with the order number for assistance.', 'points' => ['Courier delays may occur outside ShopPilot control.', 'Support can help verify the latest order status.', 'Incorrect delivery information may require additional processing.'], 'tone' => 'amber'],
                ],
            ],
            'return-refund-policy' => [
                'title' => 'Return & Refund Policy', 'icon' => 'fa-undo-alt', 'updated' => 'October 03, 2026', 'intro' => 'We want each order to arrive as expected. This policy describes the basic conditions for return and refund requests.',
                'sections' => [
                    ['title' => 'Return Eligibility', 'icon' => 'fa-check-circle', 'text' => 'A product should normally be unused, complete and in its original condition before a return request is reviewed.', 'points' => ['Keep the original packaging where possible.', 'Report damaged or incorrect products promptly.', 'Some product categories may have additional restrictions.'], 'tone' => 'blue'],
                    ['title' => 'Non-Returnable Items', 'icon' => 'fa-ban', 'text' => 'Certain products may not be eligible after opening, use or activation for safety and hygiene reasons.', 'points' => ['Used or physically damaged items may be rejected.', 'Personal care or hygiene-sensitive products may be restricted.', 'Final-sale items can be excluded when clearly marked.'], 'tone' => 'red'],
                    ['title' => 'Refund Review', 'icon' => 'fa-wallet', 'text' => 'Approved refunds are processed after the returned item and request details are reviewed.', 'points' => ['Refund timing depends on the payment method.', 'Original order and payment references may be required.', 'Support will communicate the result of the review.'], 'tone' => 'green'],
                ],
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy', 'icon' => 'fa-shield-alt', 'updated' => 'October 03, 2026', 'intro' => 'This policy explains the information ShopPilot collects to operate the store and how that information is used and protected.',
                'sections' => [
                    ['title' => 'Information We Collect', 'icon' => 'fa-user-shield', 'text' => 'We may collect account, order, delivery, payment-reference and support information that you choose to provide.', 'points' => ['Account and profile details.', 'Order and delivery information.', 'Contact messages and support requests.'], 'tone' => 'blue'],
                    ['title' => 'How We Use Information', 'icon' => 'fa-cogs', 'text' => 'Information is used to provide store functionality, process orders, prevent abuse and improve customer support.', 'points' => ['Operate checkout and account features.', 'Provide order tracking and support.', 'Maintain security and service reliability.'], 'tone' => 'green'],
                    ['title' => 'Your Choices', 'icon' => 'fa-sliders-h', 'text' => 'You can update profile information through your account and contact support if you need help with account-related data.', 'points' => ['Keep contact information accurate.', 'Use secure passwords.', 'Contact support for account assistance.'], 'tone' => 'amber'],
                ],
            ],
            'terms-conditions' => [
                'title' => 'Terms & Conditions', 'icon' => 'fa-file-contract', 'updated' => 'October 03, 2026', 'intro' => 'These terms describe the general rules for using ShopPilot, creating orders and interacting with store services.',
                'sections' => [
                    ['title' => 'Using ShopPilot', 'icon' => 'fa-store', 'text' => 'Use the website lawfully and provide accurate information when creating an account, order or support request.', 'points' => ['Do not misuse store features or attempt unauthorized access.', 'Keep account credentials private.', 'Provide accurate checkout and contact information.'], 'tone' => 'blue'],
                    ['title' => 'Product & Order Information', 'icon' => 'fa-box', 'text' => 'Prices, availability and product information can change as inventory and store configuration are updated.', 'points' => ['An order can depend on stock availability.', 'Final totals are confirmed during checkout.', 'Obvious data or pricing errors may require correction.'], 'tone' => 'green'],
                    ['title' => 'Service Availability', 'icon' => 'fa-server', 'text' => 'ShopPilot may update, maintain or temporarily restrict parts of the service to protect reliability and security.', 'points' => ['Scheduled maintenance can affect availability.', 'Features may evolve over time.', 'Security controls can limit suspicious activity.'], 'tone' => 'amber'],
                ],
            ],
        ];
    }
}
