<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>404 – Page Not Found | Greenshift</title>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/dashboard.css">

	<style>
		 body {
		  background-color: var(--bg-page);
		  color: var(--text-primary);
		  min-height: 100vh;
		  display: flex;
		  align-items: center;
		  justify-content: center;
		  padding: 24px 16px;
		}

	</style>

</head>
<body>
  <div class="err-wrap">
    <div class="err-body">

      <div class="err-icon">
        <svg viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
          <line x1="11" y1="8" x2="11" y2="14"/>
          <line x1="8" y1="11" x2="14" y2="11"/>
        </svg>
      </div>

      <div class="err-code">404</div>
      <h1 class="err-title">Page Not Found</h1>
      <div class="err-divider"></div>
      <p class="err-desc">
        The page you're looking for doesn't exist or has been moved.
        Try going back or returning to the dashboard.
      </p>

      <div class="err-actions">
        <a href="/" class="gs-btn gs-btn--primary">
          <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          Go to Dashboard
        </a>
        <a href="javascript:history.back()" class="gs-btn gs-btn--outline">
          <svg viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
          Go Back
        </a>
      </div>

    </div>
    <div class="err-foot">
      &copy; 2026 <strong>Greenshift</strong> Energy Consulting
    </div>
  </div>
</body>
</html>
