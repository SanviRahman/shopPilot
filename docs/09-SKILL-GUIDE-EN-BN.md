# CampaignFlow SaaS --- Development Skill Guide

# ক্যাম্পেইনফ্লো SaaS --- Development Skill Guide

> Document Type: Project Development Rules\
> Project: CampaignFlow SaaS\
> Framework: Laravel

------------------------------------------------------------------------

# 1. Purpose / উদ্দেশ্য

This document defines coding standards, architecture rules, security
rules, and AI development guidelines for CampaignFlow SaaS.

এই document CampaignFlow SaaS project-এর coding standard, architecture
rule, security rule এবং AI development guideline define করবে।

------------------------------------------------------------------------

# 2. Core Development Philosophy / মূল নীতি

Always follow:

-   Clean Architecture
-   Service Based Architecture
-   Single Responsibility Principle
-   Event Driven Design
-   Queue Based Processing
-   Secure Coding Practice
-   Multi Tenant Data Isolation

Avoid:

-   Fat Controller
-   Huge Model Logic
-   Duplicate Business Logic
-   Hard-coded business rules

------------------------------------------------------------------------

# 3. Laravel Architecture Rule

Application flow:

    Request
     ↓
    Form Request Validation
     ↓
    Controller
     ↓
    Service Layer
     ↓
    Model / Database
     ↓
    Response

Controller only handles request and response.

Business logic must stay inside Services.

------------------------------------------------------------------------

# 4. Folder Responsibility

    app/

    Services/
    Business logic

    Actions/
    Single purpose operation

    Models/
    Relationships and scopes

    Observers/
    Model lifecycle

    Events/
    Application events

    Listeners/
    Event handlers

    Jobs/
    Background processing

    Policies/
    Authorization

    Rules/
    Validation rules

    DTOs/
    Data transfer objects

    Traits/
    Reusable behavior

    Enums/
    Fixed states

------------------------------------------------------------------------

# 5. Service Layer Rules

Required services:

    CampaignService
    ProductService
    CheckoutService
    OrderService
    FraudService
    PaymentService
    PixelService
    AnalyticsService
    SubscriptionService
    WebhookService
    NotificationService

Services handle:

-   Business workflow
-   External API communication
-   Complex calculations
-   Data processing

------------------------------------------------------------------------

# 6. Controller Rules

Good:

``` php
public function store(StoreOrderRequest $request)
{
    return $this->orderService->create(
        $request->validated()
    );
}
```

Avoid:

``` php
public function store(Request $request)
{
    // Hundreds of lines of business logic
}
```

------------------------------------------------------------------------

# 7. Database Rules

Every business table should contain:

    id
    uuid
    workspace_id
    created_at
    updated_at

Use:

-   Foreign keys
-   Indexes
-   Unique constraints
-   Soft deletes where required

------------------------------------------------------------------------

# 8. Multi Tenant Rules

CampaignFlow SaaS is a multi tenant application.

Rules:

-   Every query must respect workspace_id
-   User can access only own workspace data
-   API must enforce tenant isolation
-   Policies must verify ownership

Never expose another tenant data.

------------------------------------------------------------------------

# 9. Validation Rules

Always use Laravel Form Request.

Examples:

    StoreCampaignRequest
    CheckoutRequest
    StoreProductRequest
    PaymentRequest
    FraudRuleRequest

Never trust user input.

------------------------------------------------------------------------

# 10. Security Rules

Mandatory:

-   CSRF Protection
-   XSS Prevention
-   SQL Injection Protection
-   Authorization Policy
-   Mass Assignment Protection
-   Rate Limiting
-   Secure Authentication
-   Webhook Verification
-   Payment Callback Verification

------------------------------------------------------------------------

# 11. Payment Architecture Rules

Payment must follow Driver Pattern.

Interface:

    PaymentGatewayInterface

Drivers:

    CodGateway
    SslCommerzGateway
    BkashGateway
    RocketGateway
    DbblGateway

Methods:

    initiate()
    verify()
    callback()
    webhook()
    refund()

Payment must be idempotent.

------------------------------------------------------------------------

# 12. Fraud Engine Rules

Fraud logic belongs to:

    FraudService

Fraud checks:

    IP
    Phone
    Email
    Device
    Order History
    Duplicate Order
    Velocity
    Customer Risk

Never put fraud logic inside Controller.

------------------------------------------------------------------------

# 13. Event & Listener Rules

Use events for communication.

Events:

    OrderPlaced
    OrderPaid
    FraudDetected
    PaymentCompleted
    SubscriptionActivated

Listeners:

    SendNotification
    TrackPixel
    UpdateAnalytics
    SendWebhook
    WriteAuditLog

------------------------------------------------------------------------

# 14. Queue & Job Rules

Heavy tasks must run through Queue.

Jobs:

    SendPixelEventJob
    ProcessFraudCheckJob
    SendWebhookJob
    SendEmailJob
    GenerateAnalyticsJob

Recommended:

    Redis
    Laravel Horizon

------------------------------------------------------------------------

# 15. Observer Rules

Observers should handle only model lifecycle tasks.

Allowed:

-   UUID generation
-   Audit creation
-   Event dispatch

Avoid:

-   Payment processing
-   Fraud calculation
-   External API calls

------------------------------------------------------------------------

# 16. API Rules

API standard:

    /api/v1

Use:

    Laravel Sanctum
    API Resources
    Form Requests
    Rate Limiting

------------------------------------------------------------------------

# 17. Webhook Rules

Verify:

    Signature
    Timestamp
    Transaction ID
    Duplicate Event
    Payload Integrity

Store:

    Request
    Response
    Status
    Attempts
    Error

------------------------------------------------------------------------

# 18. Coding Style Rules

Models:

    Order
    Campaign
    PaymentTransaction

Services:

    OrderService
    PaymentService
    FraudService

Jobs:

    SendPixelEventJob
    ProcessPaymentJob

Use meaningful names.

------------------------------------------------------------------------

# 19. Testing Rules

Required tests:

    Unit Test
    Feature Test
    API Test
    Authorization Test
    Tenant Isolation Test
    Payment Test
    Fraud Test
    Webhook Test
    Queue Test

------------------------------------------------------------------------

# 20. AI Development Assistant Rules

When generating code:

1.  Explain problem first
2.  Mention affected files
3.  Follow Laravel convention
4.  Provide complete code
5.  Add validation
6.  Consider security
7.  Explain testing steps
8.  Provide commands

Never:

-   Give random code
-   Ignore architecture
-   Put business logic in controller
-   Skip validation

------------------------------------------------------------------------

# 21. Documentation Rules

Maintain:

    01-PROJECT-OVERVIEW.md
    02-PRD.md
    03-FEATURES.md
    04-USER-ROLES.md
    05-BUSINESS-RULES.md
    06-ARCHITECTURE.md
    07-DATABASE-ERD.md
    08-DATABASE-SCHEMA.md
    09-APPLICATION-FLOW.md
    10-FOLDER-STRUCTURE.md

------------------------------------------------------------------------

# 22. Production Checklist

Before deployment:

-   Debug disabled
-   Queue worker running
-   Scheduler configured
-   Database backup enabled
-   Cache optimized
-   Logs monitored
-   Payment tested
-   Webhook tested
-   Security tested

------------------------------------------------------------------------

# Final Rule

CampaignFlow SaaS must be developed as a secure, scalable, modular,
enterprise-level Laravel SaaS platform.
