<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>403 – Access Denied | Greenshift</title>
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
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>

      <div class="err-code">403</div>
      <h1 class="err-title">Access Denied</h1>
      <div class="err-divider"></div>
      <p class="err-desc">
        You don't have permission to view this page.
        Contact your administrator to request access.
      </p>

      <div class="err-alert">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Your current role does not have access to this resource.</span>
      </div>

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
