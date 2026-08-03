<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Login</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('images/fav-gsec.png') }}">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <!-- Auth CSS -->
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}"/>
</head>
<body>
  <div class="auth-wrapper">
    <!-- ── LEFT: Brand Panel ── -->
    <div class="auth-brand">
      <div class="auth-brand-logo">
        <img src="{{ asset('images/site-logo.png') }}" alt="Greenshift Energy Consulting"/>
      </div>
      <div class="auth-brand-body">
        <span class="auth-brand-tag">
          <i class="bi bi-lightning-charge-fill"></i>Your Trusted Energy Consultancy
        </span>
        <h1 class="auth-brand-heading">Better Energy<br>Rates for<br><span>UK Businesses</span></h1>
        <p class="auth-brand-desc">We help businesses navigate the complex process of purchasing energy contracts.</p>
        <ul class="auth-brand-features">
		  <li><i class="bi bi-lightning-charge-fill"></i>Advanced Procurement for Energy »</li>
		  <li><i class="bi bi-arrow-left-right"></i>Flex Purchasing »</li>
		  <li><i class="bi bi-tree-fill"></i>ESOS Carbon Offsetting »</li>
		  <li><i class="bi bi-piggy-bank-fill"></i>Government & CCL Rebates »</li>
		  <li><i class="bi bi-battery-charging"></i>Battery Storage »</li>
		  <li><i class="bi bi-speedometer2"></i>Energy Management Systems »</li>
		</ul>
      </div>
      <div class="auth-brand-footer">&copy; {{ date('Y') }} Greenshift Energy Consulting. All rights reserved.</div>
    </div>
    <!-- ── RIGHT: Form Panel ── -->
    <div class="auth-form-panel">
      <div class="auth-brand-logo">
        <img src="{{ asset('images/site-logo.png') }}" alt="Greenshift Energy Consulting"/>
      </div>
      <div class="auth-form-box">{{ $slot }}</div>
    </div>
  </div>
</body>
</html>
