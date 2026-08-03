<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>LOA Verification — Greenshift Energy</title>
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/fav-gsec.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/fav-gsec.png') }}">
		<link rel="apple-touch-icon" href="{{ asset('images/fav-gsec.png') }}">
		<link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
		<style>
		.spinner-border {
			width: 16px;
			height: 16px;
			border: 2px solid rgba(255,255,255,0.4);
			border-top-color: #fff;
			border-radius: 50%;
			display: inline-block;
			animation: gsSpin 0.7s linear infinite;
			vertical-align: middle;
		}
		.disable {
			pointer-events: none;
			cursor: not-allowed;
			opacity: 0.65;
		}
		.gs-alert--success {
			background: rgba(22, 163, 74, 0.10);
			border: 1.5px solid rgba(22, 163, 74, 0.25);
			color: #15803d;
			margin-top: 12px;
		}

		@keyframes gsSpin {
			to {
				transform: rotate(360deg);
			}
		}
			/* ============================================================
			   ROOT VARIABLES (from main CSS)
			============================================================ */
			:root {
			  --primary-color: #3D9082;
			  --primary-dark: #2e6e64;
			  --primary-light: rgba(61,144,130,0.12);
			  --secondary-color: #000E49;
			  --secondary-light: rgba(0,14,73,0.08);
			  --bg-page: #f0f2f7;
			  --bg-card: #ffffff;
			  --text-primary: #1a2340;
			  --text-secondary: #6b7a99;
			  --border-color: #e4e8f0;
			  --shadow-sm: 0 2px 8px rgba(0,14,73,0.06);
			  --shadow-md: 0 4px 20px rgba(0,14,73,0.10);
			  --radius: 12px;
			  --radius-sm: 8px;
			  --heading-font: 'Anton', sans-serif;
			  --body-font: 'Montserrat', sans-serif;
			  --fs-h1: 40px; --fs-h2: 32px; --fs-h3: 26px;
			  --fs-h4: 22px; --fs-h5: 18px; --fs-h6: 15px;
			  --fs-base: 14px; --fs-lg: 16px; --fs-sm: 13px;
			  --fs-xs: 12px; --fs-xxs: 11px;
			  --transition: 0.25s ease;
			}
			
			/* ============================================================
			   RESET
			============================================================ */
			*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
			body {
			  font-family: var(--body-font);
			  background-color: var(--bg-page);
			  color: var(--text-primary);
			  font-size: var(--fs-base);
			  min-height: 100vh;
			}
			h1,h2,h3,h4,h5,h6 {
			  font-family: var(--heading-font);
			  letter-spacing: 0.02em;
			  margin: 0;
				  font-weight: 500;
			}
			p, span, li, input, label, textarea, button { font-family: var(--body-font); margin: 0; }
			a { text-decoration: none; color: inherit; }
			ul { list-style: none; padding: 0; margin: 0; }
			input, textarea, select { outline: none; }
			
			/* Scrollbar */
			::-webkit-scrollbar { width: 5px; height: 5px; }
			::-webkit-scrollbar-track { background: transparent; }
			::-webkit-scrollbar-thumb { background: rgba(61,144,130,0.35); border-radius: 99px; }
			::-webkit-scrollbar-thumb:hover { background: var(--primary-color); }
			
				img {
					max-width: 100%;
				}
			.gs-alert {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 10px 16px;
				border-radius: var(--radius-sm);
				font-family: var(--body-font);
				font-size: var(--fs-sm);
				font-weight: 600;
				width: 100%;
			}
			.gs-alert--error {
				background: rgba(220, 38, 38, 0.08);
				border: 1.5px solid rgba(220, 38, 38, 0.25);
				color: #991b1b;
			}
			
			
			/* ============================================================
			   HEADER
			============================================================ */
			.loa-header {
					background: var(--bg-card);
					border-bottom: 1px solid var(--border-color);
					box-shadow: var(--shadow-sm);
					min-height: 64px;
					display: flex;
					align-items: center;
					justify-content: center;
					position: sticky;
					top: 0;
					z-index: 100;
					padding: 10px 0;
				}
				.loa-header-logo {
				  display: flex;
				  align-items: center;
				  gap: 10px;
				}
				.loa-header img {
					max-width: 150px;
				}
			.loa-header-logo-icon {
			  width: 36px;
			  height: 36px;
			  background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
			  border-radius: 8px;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  color: #fff;
			  font-size: 16px;
			}
			.loa-header-logo-text {
			  display: flex;
			  flex-direction: column;
			  gap: 0;
			}
			.loa-header-logo-name {
			  font-family: var(--heading-font);
			  font-size: 18px;
			  color: var(--secondary-color);
			  letter-spacing: 0.06em;
			  line-height: 1;
			}
			.loa-header-logo-sub {
			  font-size: 9px;
			  font-weight: 600;
			  letter-spacing: 0.18em;
			  text-transform: uppercase;
			  color: var(--primary-color);
			}
			.loa-header-back {
			  position: absolute;
			  right: 24px;
			  top: 50%;
			  transform: translateY(-50%);
			}
			
			/* ============================================================
			   PAGE WRAPPER
			============================================================ */
			.loa-page {
			  max-width: 100%;
			  margin: 0 auto;
			  padding: 32px 28px 48px;
			}
			
			/* ============================================================
			   PAGE HEADING
			============================================================ */
			.loa-page-head {
			  text-align: center;
			  margin-bottom: 32px;
			}
			.loa-page-head h1 {
			  font-size: clamp(22px, 4vw, var(--fs-h2));
			  color: var(--text-primary);
			  margin-bottom: 8px;
			}
			.loa-page-head h1 span { color: var(--primary-color); }
			.loa-page-head p {
			  font-size: var(--fs-sm);
			  color: var(--text-secondary);
			  font-weight: 500;
			}
			
			/* ============================================================
			   MAIN GRID — 3 columns
			============================================================ */
			.loa-grid {
			  display: grid;
			  grid-template-columns: 1.6fr 1.2fr 0.9fr;
			  gap: 20px;
			  align-items: start;
			}
			
			/* ============================================================
			   CARD BASE
			============================================================ */
			.loa-card {
			  background: var(--bg-card);
			  border: 1px solid var(--border-color);
			  border-radius: var(--radius);
			  box-shadow: var(--shadow-sm);
			  overflow: hidden;
			}
			.loa-card-header {
			  padding: 16px 20px;
			  border-bottom: 1px solid var(--border-color);
			}
			.loa-card-title {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			  letter-spacing: 0.04em;
			}
			.loa-card-body { padding: 20px; }
			
			/* ============================================================
			   COMPANY PROFILE ROW
			============================================================ */
			.loa-company-row {
			  display: flex;
			  align-items: center;
			  gap: 14px;
			  padding: 16px 20px;
			  border-bottom: 1px solid var(--border-color);
			  background: var(--bg-page);
			}
			.loa-company-avatar {
			  width: 52px;
			  height: 52px;
			  border-radius: 50%;
			  object-fit: cover;
			  border: 2px solid var(--primary-color);
			  flex-shrink: 0;
			}
			.loa-company-avatar-placeholder {
			  width: 52px;
			  height: 52px;
			  border-radius: 50%;
			  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
			  color: #fff;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  font-family: var(--heading-font);
			  font-size: 20px;
			  flex-shrink: 0;
			}
			.loa-company-info h3 {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			  letter-spacing: 0.02em;
			}
			.loa-company-info p {
			  font-size: var(--fs-xs);
			  color: var(--text-secondary);
			  font-weight: 500;
			  margin-top: 3px;
			}
			
			/* ============================================================
			   FORM FIELDS (reusing gs- classes)
			============================================================ */
			.gs-label {
			  font-size: var(--fs-xs);
			  font-weight: 700;
			  letter-spacing: 0.05em;
			  text-transform: uppercase;
			  color: var(--text-secondary);
			  display: block;
			  margin-bottom: 5px;
			}
			.gs-input, .gs-select, .gs-textarea {
			  width: 100%;
			  padding: 9px 13px;
			  border: 1.5px solid var(--border-color);
			  border-radius: var(--radius-sm);
			  font-family: var(--body-font);
			  font-size: var(--fs-sm);
			  color: var(--text-primary);
			  background-color: var(--bg-page);
			  transition: border-color var(--transition), box-shadow var(--transition);
			  min-height: 38px;
			}
			.gs-input::placeholder { color: var(--text-secondary); }
			.gs-input:focus, .gs-select:focus {
			  border-color: var(--primary-color);
			  background-color: #fff;
			  box-shadow: 0 0 0 3px rgba(61,144,130,0.12);
			}
			.otp-container {
				display: flex;
				gap: 5px;
			}
			.gs-input[readonly] {
			  background: var(--bg-page);
			  color: var(--text-primary);
			  cursor: default;
			}
			.gs-input[readonly]:focus { box-shadow: none; border-color: var(--border-color); }
			
			/* Input with suffix/prefix (MPAN +) */
			.input-addon-wrap {
			  display: flex;
			  align-items: stretch;
			  border: 1.5px solid var(--border-color);
			  border-radius: var(--radius-sm);
			  overflow: hidden;
			  background: var(--bg-page);
			  transition: border-color var(--transition);
			}
			.input-addon-wrap:focus-within {
			  border-color: var(--primary-color);
			  box-shadow: 0 0 0 3px rgba(61,144,130,0.12);
			}
			.input-addon-wrap .gs-input {
			  border: none !important;
			  border-radius: 0 !important;
			  background: transparent !important;
			  box-shadow: none !important;
			}
			.input-addon-btn {
			  width: 36px;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  background: var(--primary-light);
			  border: none;
			  border-left: 1.5px solid var(--border-color);
			  color: var(--primary-color);
			  font-size: 14px;
			  cursor: pointer;
			  transition: background var(--transition);
			  flex-shrink: 0;
			}
			.input-addon-btn:hover { background: rgba(61,144,130,0.22); }
			
			/* Form grid 2-col */
			.loa-form-row {
			  display: grid;
			  grid-template-columns: 1fr 1fr;
			  gap: 12px;
			}
			.loa-field {
				margin: 15px 0;
			}
			.loa-field:last-child { margin-bottom: 0; }
			
			/* Checkbox */
			.loa-checkbox-wrap {
			  display: flex;
			  align-items: flex-start;
			  gap: 10px;
			  padding: 14px 0;
			}
			.loa-checkbox-wrap input[type="checkbox"] {
			  width: 16px;
			  height: 16px;
			  accent-color: var(--primary-color);
			  cursor: pointer;
			  flex-shrink: 0;
			  margin-top: 2px;
			}
			.loa-checkbox-wrap label {
			  font-size: var(--fs-sm);
			  color: var(--text-secondary);
			  font-weight: 500;
			  cursor: pointer;
			  line-height: 1.55;
			}
			.loa-checkbox-wrap label b { color: var(--primary-color); }
			
			/* Buttons */
			.gs-btn {
			  display: inline-flex;
			  align-items: center;
			  justify-content: center;
			  gap: 7px;
			  padding: 10px 22px;
			  font-family: var(--body-font);
			  font-size: var(--fs-sm);
			  font-weight: 600;
			  border-radius: var(--radius-sm);
			  cursor: pointer;
			  border: 1.5px solid transparent;
			  transition: all var(--transition);
			  white-space: nowrap;
			  text-decoration: none;
			  min-height: 40px;
			}
			.gs-btn--primary {
			  background: var(--secondary-color);
			  color: #fff;
			  border-color: var(--secondary-color);
			  width: 100%;
			}
			.gs-btn--primary:hover {
			  background: var(--primary-color);
			  border-color: var(--primary-color);
			  transform: translateY(-1px);
			}
			.gs-btn--outline {
			  background: var(--bg-page);
			  color: var(--text-secondary);
			  border-color: var(--border-color);
			}
			.gs-btn--outline:hover {
			  border-color: var(--primary-color);
			  color: var(--primary-color);
			  background: var(--primary-light);
			}
			.gs-btn--teal {
			  background: var(--primary-color);
			  color: #fff;
			  border-color: var(--primary-color);
			}
			.gs-btn--teal:hover {
			  background: var(--primary-dark);
			  transform: translateY(-1px);
			}
			.gs-btn--sm {
			  padding: 6px 14px;
			  font-size: var(--fs-xs);
			  min-height: 32px;
			}
			.gs-btn--danger {
			  background: #dc2626;
			  color: #fff;
			  border-color: #dc2626;
			}
			.gs-btn--danger:hover { background: #991b1b; transform: translateY(-1px); }
			.btn-send-note {
			  font-size: 11px;
			  padding: 4px 5px;
			  min-height: unset;
			  border-radius: 5px;
			}
			
			/* ============================================================
			   UPLOAD SECTION
			============================================================ */
			.loa-upload-card { margin-bottom: 16px; }
			
				.loa-upload-head {
			  padding: 18px 20px 10px;
			  border-bottom: 1px solid var(--border-color);
			}
			   .loa-card .gs-panel-title {
					font-family: var(--heading-font);
					font-size: var(--fs-h5);
					color: var(--text-primary);
					letter-spacing: 0.02em;
					display: flex;
					align-items: center;
				}
			.loa-upload-head p {
			  font-size: var(--fs-xs);
			  color: var(--text-secondary);
			  font-weight: 500;
			  margin-top: 4px;
			}
			
			.loa-dropzone {
			  display: flex;
			  flex-direction: column;
			  align-items: center;
			  justify-content: center;
			  gap: 8px;
			  padding: 24px 20px;
			  margin: 16px 20px;
			  border: 2px dashed var(--border-color);
			  border-radius: var(--radius-sm);
			  cursor: pointer;
			  transition: all var(--transition);
			  background: var(--bg-page);
			}
			.loa-dropzone:hover, .loa-dropzone.over {
			  border-color: var(--primary-color);
			  background: var(--primary-light);
			}
			.loa-dropzone-icon {
			  width: 44px;
			  height: 44px;
			  border-radius: 50%;
			  background: var(--secondary-color);
			  color: #fff;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  font-size: 18px;
			}
			.loa-dropzone p {
			  font-size: var(--fs-sm);
			  font-weight: 600;
			  color: var(--text-primary);
			  text-align: center;
			}
			.loa-dropzone p span {
			  color: var(--primary-color);
			  text-decoration: underline;
			  text-underline-offset: 2px;
			}
			.loa-dropzone-hint {
			  font-size: var(--fs-xxs) !important;
			  color: var(--text-secondary) !important;
			  font-weight: 500 !important;
			}
			#fileInput { display: none; }
			
			/* File list */
			.loa-file-list { padding: 0 20px 16px; }
			.loa-file-item {
			  display: flex;
			  align-items: center;
			  gap: 12px;
			  padding: 10px 0;
			  border-bottom: 1px solid var(--border-color);
			}
			.loa-file-item:last-child { border-bottom: none; }
			.loa-file-icon {
			  width: 34px;
			  height: 34px;
			  border-radius: var(--radius-sm);
			  background: rgba(220,38,38,0.10);
			  color: #dc2626;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  font-size: 16px;
			  flex-shrink: 0;
			}
			.loa-file-info { flex: 1; min-width: 0; }
			.loa-file-name {
			  font-size: var(--fs-sm);
			  font-weight: 600;
			  color: var(--text-primary);
			  white-space: nowrap;
			  overflow: hidden;
			  text-overflow: ellipsis;
			}
			.loa-file-eye {
			  width: 28px;
			  height: 28px;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  background: none;
			  border: 1.5px solid var(--border-color);
			  border-radius: var(--radius-sm);
			  color: var(--text-secondary);
			  font-size: 13px;
			  cursor: pointer;
			  transition: all var(--transition);
			  flex-shrink: 0;
			}
			.loa-file-eye:hover {
			  border-color: var(--primary-color);
			  color: var(--primary-color);
			  background: var(--primary-light);
			}
			.passkey-form-block {
				max-width: 700px;
				margin: 0 auto;
			}
			.passkey-form-block .loa-card {
				padding: 40px;
			}
			.passkey-head {
				display: flex;
				flex-direction: column;
				gap: 5px;
				margin: 20px 0;
				align-items: center;
				text-align: center;
			}
			.otp-container input.gs-input {
				height: 72px;
				text-align: center;
				font-size: 20px;
			}
			/* ============================================================
			   SIGNATURE SECTION
			============================================================ */
			.loa-sig-card {}
			.loa-sig-head {
			  text-align: center;
			  padding: 16px 20px 14px;
			  border-bottom: 1px solid var(--border-color);
			}
			.loa-sig-head h5 {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			}
			.loa-sig-body { padding: 24px 20px; }
			.loa-sig-canvas-wrap {
			  border: 1.5px solid var(--border-color);
			  border-radius: var(--radius-sm);
			  background: #fff;
			  position: relative;
			  overflow: hidden;
			  margin-bottom: 12px;
			}
			#sigCanvas {
			  display: block;
			  width: 100%;
			  height: 110px;
			  cursor: crosshair;
			  touch-action: none;
			}
			.loa-sig-placeholder {
			  position: absolute;
			  inset: 0;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  pointer-events: none;
			}
			.loa-sig-placeholder span {
			  font-family: 'Brush Script MT', cursive;
			  font-size: 28px;
			  color: rgba(26,35,64,0.18);
			  user-select: none;
			}
			.loa-sig-btns {
			  display: flex;
			  gap: 8px;
			}
			.loa-sig-btns button { flex: 1; }
			
			/* ============================================================
			   LOA PREVIEW
			============================================================ */
			.loa-preview-card {}
			.loa-preview-header {
			  display: flex;
			  align-items: center;
			  justify-content: space-between;
			  padding: 16px 20px;
			  border-bottom: 1px solid var(--border-color);
			}
			.loa-preview-title {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			}
			.loa-preview-body {
			  padding: 20px;
			  min-height: 340px;
			  display: flex;
			  flex-direction: column;
			}
			
			/* The LOA document preview */
			.loa-doc {
			  background: #fff;
			  border: 1px solid var(--border-color);
			  border-radius: var(--radius-sm);
			  padding: 20px;
			  flex: 1;
			  position: relative;
			  overflow: hidden;
			}
			.loa-doc::before {
			  content: '';
			  position: absolute;
			  top: 0; left: 0; right: 0;
			  height: 3px;
			  background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
			}
			.loa-doc-logo {
			  display: flex;
			  align-items: center;
			  gap: 8px;
			  margin-bottom: 14px;
			  padding-bottom: 12px;
			  border-bottom: 1px solid var(--border-color);
			}
			.loa-doc-logo-icon {
			  width: 28px;
			  height: 28px;
			  background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
			  border-radius: 6px;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  color: #fff;
			  font-size: 12px;
			}
			.loa-doc-logo-name {
			  font-family: var(--heading-font);
			  font-size: 13px;
			  color: var(--secondary-color);
			  letter-spacing: 0.06em;
			}
			.loa-doc h6 {
			  font-family: var(--heading-font);
			  font-size: 13px;
			  color: var(--text-primary);
			  margin-bottom: 8px;
			  text-align: center;
			  letter-spacing: 0.05em;
			}
			.loa-doc-line {
			  display: flex;
			  justify-content: space-between;
			  gap: 8px;
			  padding: 5px 0;
			  font-size: 10px;
			  border-bottom: 1px dotted var(--border-color);
			  color: var(--text-secondary);
			}
			.loa-doc-line span:last-child { font-weight: 600; color: var(--text-primary); }
			.loa-doc-sig-line {
			  margin-top: 16px;
			  border-top: 1.5px solid var(--text-primary);
			  padding-top: 4px;
			  font-size: 9px;
			  color: var(--text-secondary);
			  display: flex;
			  justify-content: space-between;
			}
			.loa-doc-watermark {
			  position: absolute;
			  bottom: 20px;
			  right: 16px;
			  font-family: var(--heading-font);
			  font-size: 40px;
			  color: rgba(61,144,130,0.07);
			  letter-spacing: 0.1em;
			  user-select: none;
			  pointer-events: none;
			}
			.loa-preview-pages {
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  gap: 12px;
			  padding: 12px 20px 16px;
			  border-top: 1px solid var(--border-color);
			}
			.loa-page-nav {
			  width: 30px;
			  height: 30px;
			  border: 1.5px solid var(--border-color);
			  border-radius: 50%;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  background: var(--bg-page);
			  color: var(--text-secondary);
			  font-size: 12px;
			  cursor: pointer;
			  transition: all var(--transition);
			}
			.loa-page-nav:hover {
			  border-color: var(--primary-color);
			  color: var(--primary-color);
			  background: var(--primary-light);
			}
			.loa-page-count {
			  font-size: var(--fs-xs);
			  font-weight: 600;
			  color: var(--text-secondary);
			}
			
			/* ============================================================
			   BOTTOM SECURITY NOTE
			============================================================ */
			.loa-security-note {
			  text-align: center;
			  margin-top: 28px;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  gap: 7px;
			  font-size: var(--fs-xs);
			  color: var(--text-secondary);
			  font-weight: 500;
			}
			.loa-security-note i { color: var(--primary-color); font-size: 14px; }
			
			/* ============================================================
			   TOAST / ALERT
			============================================================ */
			.loa-toast {
			  position: fixed;
			  bottom: 24px;
			  right: 24px;
			  background: var(--secondary-color);
			  color: #fff;
			  padding: 12px 20px;
			  border-radius: var(--radius-sm);
			  font-size: var(--fs-sm);
			  font-weight: 600;
			  display: flex;
			  align-items: center;
			  gap: 8px;
			  box-shadow: var(--shadow-md);
			  z-index: 9999;
			  transform: translateY(80px);
			  opacity: 0;
			  transition: all 0.35s ease;
			}
			.loa-toast.show {
			  transform: translateY(0);
			  opacity: 1;
			}
			.loa-toast i { color: var(--primary-color); font-size: 16px; }
			
			/* ============================================================
			   RESPONSIVE
			============================================================ */
			@media (max-width: 1024px) {
			  .loa-grid { grid-template-columns: 1fr 1fr; }
			  .loa-col-right { grid-column: 1 / -1; }
			}
			@media (max-width: 700px) {
			  .loa-grid { grid-template-columns: 1fr; }
			  .loa-form-row { grid-template-columns: 1fr; }
			  .loa-header-back .gs-btn span { display: none; }
			  .loa-header-back .gs-btn { padding: 8px 12px; }
			  .loa-page { padding: 20px 12px 40px; }
			  .loa-page-head h1 { font-size: 20px; }
			  .passkey-form-block .loa-card {padding: 20px;}
			  .otp-container input.gs-input {height: auto; text-align: center; font-size: 14px; padding: 5px;}
			}
			
			
			/* ============================================================
			   UPLOAD SIGNATURE POPUP
			============================================================ */
			.sig-popup-overlay {
			  position: fixed;
			  inset: 0;
			  background: rgba(0,14,73,0.45);
			  backdrop-filter: blur(3px);
			  z-index: 2000;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  opacity: 0;
			  pointer-events: none;
			  transition: opacity 0.3s ease;
			}
			.sig-popup-overlay.active {
			  opacity: 1;
			  pointer-events: all;
			}
			.sig-popup {
			  background: var(--bg-card);
			  border-radius: var(--radius);
			  box-shadow: 0 20px 60px rgba(0,14,73,0.25);
			  width: 420px;
			  max-width: 92vw;
			  transform: scale(0.88) translateY(20px);
			  transition: transform 0.3s ease;
			  overflow: hidden;
			}
			.sig-popup-overlay.active .sig-popup {
			  transform: scale(1) translateY(0);
			}
			.sig-popup-header {
			  display: flex;
			  align-items: center;
			  justify-content: space-between;
			  padding: 16px 20px;
			  border-bottom: 1px solid var(--border-color);
			}
			.sig-popup-title {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			}
			.sig-popup-close {
			  width: 30px; height: 30px;
			  border: 1.5px solid var(--border-color);
			  border-radius: 50%;
			  background: none;
			  cursor: pointer;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  color: var(--text-secondary);
			  font-size: 12px;
			  transition: all var(--transition);
			}
			.sig-popup-close:hover { border-color: #dc2626; color: #dc2626; background: rgba(220,38,38,0.08); }
			.sig-popup-body { padding: 20px; }
			.sig-popup-hint {
			  font-size: var(--fs-sm);
			  color: var(--text-secondary);
			  margin-bottom: 14px;
			  font-weight: 500;
			}
			.sig-popup-hint b { color: var(--primary-color); }
			.sig-popup-canvas-wrap {
			  border: 2px dashed var(--primary-color);
			  border-radius: var(--radius-sm);
			  background: #fafbff;
			  position: relative;
			  overflow: hidden;
			}
			#sigPopupCanvas {
			  display: block;
			  width: 100%;
			  height: 150px;
			  cursor: crosshair;
			  touch-action: none;
			}
			.sig-popup-placeholder {
			  position: absolute;
			  inset: 0;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  pointer-events: none;
			}
			.sig-popup-placeholder span {
			  font-family: 'Brush Script MT', cursive;
			  font-size: 34px;
			  color: rgba(61,144,130,0.22);
			  user-select: none;
			}
			.sig-popup-footer {
			  display: flex;
			  align-items: center;
			  gap: 10px;
			  padding: 14px 20px 18px;
			  border-top: 1px solid var(--border-color);
			}
			
			/* ============================================================
			   PDF PREVIEW SLIDE-UP
			============================================================ */
			.pdf-preview-overlay {
			  position: fixed;
			  inset: 0;
			  background: rgba(0,14,73,0.5);
			  backdrop-filter: blur(4px);
			  z-index: 3000;
			  display: flex;
			  align-items: flex-end;
			  justify-content: center;
			  opacity: 0;
			  pointer-events: none;
			  transition: opacity 0.35s ease;
			}
			.pdf-preview-overlay.active {
			  opacity: 1;
			  pointer-events: all;
			}
			.pdf-preview-panel {
			  background: var(--bg-card);
			  border-radius: var(--radius) var(--radius) 0 0;
			  box-shadow: 0 -8px 40px rgba(0,14,73,0.2);
			  width: 100%;
			  max-width: 780px;
			  max-height: 88vh;
			  display: flex;
			  flex-direction: column;
			  transform: translateY(100%);
			  transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
			}
			.pdf-preview-overlay.active .pdf-preview-panel {
			  transform: translateY(0);
			}
			.pdf-preview-header {
			  display: flex;
			  align-items: center;
			  justify-content: space-between;
			  padding: 16px 24px;
			  border-bottom: 1px solid var(--border-color);
			  flex-shrink: 0;
			}
			.pdf-preview-title {
			  font-family: var(--heading-font);
			  font-size: var(--fs-h6);
			  color: var(--text-primary);
			}
			.pdf-preview-close {
			  width: 32px; height: 32px;
			  border: 1.5px solid var(--border-color);
			  border-radius: 50%;
			  background: none;
			  cursor: pointer;
			  display: flex;
			  align-items: center;
			  justify-content: center;
			  color: var(--text-secondary);
			  font-size: 13px;
			  transition: all var(--transition);
			}
			.pdf-preview-close:hover { border-color: #dc2626; color: #dc2626; background: rgba(220,38,38,0.08); }
			.pdf-preview-content {
			  flex: 1;
			  overflow-y: auto;
			  padding: 24px;
			  background: #e8eaf0;
			}
			.pdf-page {
			  background: #fff;
			  border-radius: 4px;
			  box-shadow: 0 2px 16px rgba(0,0,0,0.12);
			  margin: 0 auto;
			  max-width: 600px;
			  margin-bottom: 20px;
			}
			.pdf-page-inner {
			  padding: 40px 48px;
			  position: relative;
			}
			.pdf-watermark {
			  position: absolute;
			  top: 50%;
			  left: 50%;
			  transform: translate(-50%,-50%) rotate(-35deg);
			  font-family: var(--heading-font);
			  font-size: 72px;
			  color: rgba(61,144,130,0.05);
			  letter-spacing: 0.15em;
			  user-select: none;
			  pointer-events: none;
			  white-space: nowrap;
			}
			.pdf-header-bar {
			  display: flex;
			  align-items: flex-start;
			  justify-content: space-between;
			  padding-bottom: 20px;
			  border-bottom: 2px solid var(--secondary-color);
			  margin-bottom: 24px;
			}
			.pdf-title-block {
			  text-align: center;
			  margin-bottom: 28px;
			}
			.pdf-title-block h2 {
			  font-family: var(--heading-font);
			  font-size: 22px;
			  color: var(--secondary-color);
			  letter-spacing: 0.06em;
			  margin-bottom: 5px;
			}
			.pdf-title-block p {
			  font-size: 10px;
			  color: var(--text-secondary);
			  font-weight: 600;
			  letter-spacing: 0.08em;
			  text-transform: uppercase;
			}
			.pdf-section {
			  margin-bottom: 22px;
			}
			.pdf-section-title {
			  font-family: var(--heading-font);
			  font-size: 10px;
			  letter-spacing: 0.12em;
			  color: var(--primary-color);
			  text-transform: uppercase;
			  border-bottom: 1px solid var(--primary-light);
			  padding-bottom: 6px;
			  margin-bottom: 10px;
			}
			.pdf-field-row {
			  display: flex;
			  justify-content: space-between;
			  gap: 12px;
			  font-size: 11px;
			  padding: 5px 0;
			  border-bottom: 1px dotted var(--border-color);
			  color: var(--text-secondary);
			}
			.pdf-field-row span:last-child { font-weight: 700; color: var(--text-primary); }
			.pdf-body-text {
			  font-size: 11px;
			  color: var(--text-secondary);
			  line-height: 1.75;
			  margin-bottom: 8px;
			}
			.pdf-sig-block { margin-top: 32px; }
			.pdf-sig-line {
			  display: flex;
			  justify-content: space-between;
			  align-items: flex-end;
			  gap: 24px;
			}
			.pdf-footer {
			  display: flex;
			  justify-content: space-between;
			  align-items: center;
			  margin-top: 32px;
			  padding-top: 12px;
			  border-top: 1px solid var(--border-color);
			  font-size: 9px;
			  color: var(--text-secondary);
			}
			.pdf-preview-nav {
			  display: flex;
			  align-items: center;
			  justify-content: flex-end;
			  gap: 10px;
			  padding: 14px 24px;
			  border-top: 1px solid var(--border-color);
			  flex-shrink: 0;
			}
		</style>
	</head>

	<body data-lead-id="{{ $lead->id }}" data-verified="{{ $verified ? 'true' : 'false' }}">
		@php
		$full_address = collect([
		$lead->address ?? '',
		$lead->city ?? '',
		$lead->state ?? '',
		$lead->postcode ?? '',
		])->filter()->implode(', ');
		@endphp
		<!-- ===================== HEADER ===================== -->
		<header class="loa-header">
			<div class="loa-header-logo">
				<div class="auth-brand-logo">
					<img src="../images/site-logo.png" alt="Greenshift Energy Consulting" />
				</div>
			</div>
			
		</header>
		<!-- ===================== PAGE ===================== -->
		<main class="loa-page">
			<!-- Heading -->
			<div class="loa-page-head">
				<h1>Welcome, <span>{{ $lead->decision_maker_name ?? $lead->company_name ?? 'N/A' }} !</span> Complete Your LOA Verification</h1>
				<p>Please upload your electricity bill and sign the Letter of Authority to proceed with your quotation.</p>
			</div>
			@if(!$verified)
			<div class="passkey-form-block">
				<form onsubmit="return false;">
					<div class="loa-card">
						
						<div class="passkey-head">
							 <h2>Enter your Passkey</h2>
							 <p>Please enter the 8-digit verification code sent to your email address.</p>
						</div>
						<div class="loa-field">
							<div class="otp-container">
								@for($i = 0; $i < 8; $i++)
									<input type="text" class="gs-input gs-verify-input" placeholder="-" maxlength="1"/>
								@endfor
							</div>
						</div>
						
						<button class="gs-btn gs-btn--primary" onclick="handlePasskey(this)">
							<i class="bi bi-send-fill" id="verifyBtnIcon"></i> <span>Continue</span>
						</button>
						<div class="gs-alert gs-alert--error"
							id="passkeyError"
							style="margin-top:15px;display:none;">
						</div>
						@if($message)
							<div class="gs-alert gs-alert--error" style="margin-top: 15px">
								<i class="bi bi-x-circle-fill"></i>
								{{ $message }}
							</div>
						@endif
					</div>
				</form>
			</div>
			@else
				<!-- 3-col grid -->
				<div class="loa-grid">
					<!-- ============ COL 1 — BASIC INFO ============ -->
					<div>
						<div class="loa-card">
							<!-- Card Header -->
							<div class="loa-card-header">
								<h5 class="gs-panel-title">Basic Information</h5>
							</div>
							<!-- Company Row -->
							<div class="loa-company-row">
								<div class="loa-company-avatar-placeholder">{{ substr($lead->company_name ?? 'N/A', 0, 1) }}</div>
								<div class="loa-company-info">
									<h3>{{ $lead->company_name ?? 'N/A' }}</h3>
									<p><i class="bi bi-telephone-fill" style="color:var(--primary-color);margin-right:4px;"></i>{{ $lead->phone ?? 'N/A' }}</p>
								</div>
							</div>
							<!-- Body -->
							<div class="loa-card-body">
								<!-- Business Address -->
								<div class="loa-field">
									<label class="gs-label">Business Decision Person Name</label>
									<input type="text" class="gs-input" value="{{ $lead->decision_maker_name ?? 'N/A' }}" readonly />
								</div>
								<!-- Registered No + PostCode -->
								<div class="loa-form-row">
									<div class="loa-field" style="margin:0;">
										<label class="gs-label">Registered No</label>
										<input type="text" class="gs-input" value="{{ $lead->reg_number ?? '' }}" readonly />
									</div>
									<div class="loa-field" style="margin:0;">
										<label class="gs-label">Post Code</label>
										<input type="text" class="gs-input" value="{{ $lead->postcode ?? '' }}" readonly />
									</div>
								</div>
								<!-- MPAN + MPRN -->
								@if($lead->energy_type == 'electricity')
									<div class="loa-field" style="margin-bottom:0;">
										<label class="gs-label">MPAN <small style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--text-secondary);">(Electricity Meter Number)</small></label>
										<div class="input-addon-wrap">
											<input type="text" class="gs-input mpan-input" name="mpan_name" id="mpan_name" value="{{ $lead->mpan ?? '' }}" readonly />
											{{-- <button class="input-addon-btn edit-mpan-btn" title="Edit MPAN"><i class="bi bi-pencil"></i></button> --}}
										</div>
									</div>
								@elseif($lead->energy_type == 'gas')
									<div class="loa-field" style="margin-bottom:0;">
										<label class="gs-label">MPRN <small style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--text-secondary);">(Gas Meter Number)</small></label>
										<div class="input-addon-wrap">
											<input type="text" class="gs-input mrn-input" name="mrn_name" id="mrn_name" value="{{ $lead->mpan ?? '' }}" readonly />
											{{-- <button class="input-addon-btn edit-mrn-btn" title="Edit MPRN"><i class="bi bi-pencil"></i></button> --}}
										</div>
									</div>
								@endif
								<!-- Site Address -->
								<div class="loa-field" style="margin-top:12px;">
									<label class="gs-label">Site Address</label>
									<input type="text" class="gs-input" value="{{ $full_address ?: '' }}" readonly />
								</div>
								<!-- Checkbox -->
								<div class="loa-checkbox-wrap">
									<input type="checkbox" id="loaAuth" @checked($lead_loa_verified)/>
									<label for="loaAuth">I authorize <b>Greenshift Energy</b> to process my application based on provided Details.</label>
								</div>
								<!-- Send LOA -->
								@if($lead_loa_verified)
									<button class="gs-btn gs-btn--primary" id="loasubbutton" onclick="handleLOASent(this)" {{ !$loa_verified ? 'disabled' : '' }}>
										<i class="bi bi-check-circle-fill text-success"></i> LOA Submitted
									</button>
								@else
									<button class="gs-btn gs-btn--primary {{ !$loa_verified ? 'disable' : '' }}" id="loasubbutton" onclick="handleSendLOA(this)" {{ !$loa_verified ? 'disabled' : '' }}>
										<i class="bi bi-send-fill"></i> Verify LOA →
									</button>
								@endif
								<p style="font-size:var(--fs-xxs);color:var(--text-secondary);text-align:center;margin-top:10px;font-weight:500;">
									All Documents are securely uploaded and will be verified by our team.
								</p>
							</div>
						</div>
					</div>
					<!-- ============ COL 2 — UPLOAD + SIGNATURE ============ -->
					<div>
						<!-- Upload Card -->
						<div class="loa-card loa-upload-card">
							<div class="loa-card-header">
								<h5 class="gs-panel-title">Upload Bills & Invoices</h5>
								<p>Attach recent bills and Invoices below:</p>
							</div>
							<!-- Dropzone -->
							<div class="loa-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
								<div class="loa-dropzone-icon">
									<i class="bi bi-cloud-arrow-up-fill"></i>
								</div>
								<p>Upload Utility Bill or <span>Browse</span></p>
								<p class="loa-dropzone-hint">Formats: PDF, JPG, PNG (Max size: 5MB)</p>
							</div>
							<input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" />
							<!-- File list -->
							<div class="loa-file-list" id="fileList">
								<!-- Default files -->
								@foreach($lead->attachments as $attachment)
								@php
								$ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
								$iconClass = 'bi-file-earmark';
								$typeClass = '';
								if ($ext === 'pdf') {
								$iconClass = 'bi-file-earmark-pdf-fill';
								$typeClass = 'cl-file-icon--pdf';
								} elseif (in_array($ext, ['jpg','jpeg','png'])) {
								$iconClass = 'bi-file-earmark-image-fill';
								$typeClass = 'cl-file-icon--jpg';
								} elseif (in_array($ext, ['doc','docx'])) {
								$iconClass = 'bi-file-earmark-word-fill';
								$typeClass = 'cl-file-icon--doc';
								}
								@endphp
								<div class="loa-file-item">
									<div class="loa-file-icon {{ $typeClass }}"><i class="bi {{ $iconClass }}"></i></div>
									<div class="loa-file-info"><span class="loa-file-name">{{ $attachment->file_name }}</span></div>
									<button class="loa-file-eye" onclick="window.open('{{ asset('storage/'.$attachment->file_path) }}', '_blank')"> <i class="bi bi-eye"></i></button>
								</div>
								@endforeach
							</div>
						</div>
						<!-- Signature Card -->
						<div class="loa-card loa-sig-card">
							<div class="loa-sig-head">
								<h5>Your Signature</h5>
							</div>
							<div class="loa-sig-body">
								<button type="submit" name="status" id="submitsignbtn" value="active" class="gs-btn gs-btn--primary {{ $loa_verified ? 'already-signed' : '' }} {{ $loa_sent ? 'disable' : '' }}" onclick="openUploadSigPopup()">
									@if($loa_verified)
										<i class="bi bi-check-circle-fill text-success"></i> Signature Verified
									@else
										<i class="bi bi-pencil-fill"></i> Submit Signature
									@endif
								</button>
								@if($loa_sent)
								<div class="gs-alert gs-alert--success">
									<i class="spinner-border spinner-border-sm me-1" style="border-top-color: #28a745;" role="status"></i>
									<span>Waiting for your signature...</span>
								</div>

								<div class="gs-alert gs-alert--success">
									<i class="bi bi-patch-check-fill text-success"></i> 
									<span>Please sign the document sent to your email </span>
								</div>
								@endif
								@if($loa_verified)
								<div class="gs-alert gs-alert--success">
									<i class="bi bi-patch-check-fill text-success"></i> 
									<span><strong>Success!</strong> The document has been securely signed. Please Click on verify LOA Button to proceed.</span>
								</div>
								@endif
							</div>
						</div>
					</div>
					<!-- ============ COL 3 — LOA PREVIEW ============ -->
					<div class="loa-col-right">
						<div class="loa-card loa-preview-card">
							<div class="loa-preview-header">
								<h5 class="gs-panel-title">LOA Preview</h5>
								<button class="gs-btn gs-btn--outline gs-btn--sm" onclick="openPdfPreview()">
									<i class="bi bi-eye-fill"></i> Preview
								</button>
							</div>
							<div class="loa-preview-body">
								@if(!empty($loa_generated))
								<div style="aspect-ratio: 1 / 1.4; overflow: hidden; background: #fff;">
									<iframe 
										src="{{ $loa_generated }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" 
										style="width: 100%; height: 105%; border: none; color-scheme: light; margin-top: -1px;"
										scrolling="no"
									></iframe>
								</div>
								@endif
							</div>
						</div>
					</div>
				</div><!-- /grid -->
			@endif
			<!-- Security Note -->
			<div class="loa-security-note">
				<i class="bi bi-shield-lock-fill"></i>
				Your information is encrypted and secure. All data is processed in compliance with GDPR.
			</div>
		</main>
		<!-- Toast -->
		<div class="loa-toast" id="toast">
			<i class="bi bi-check-circle-fill"></i>
			<span id="toastMsg">Done!</span>
		</div>
		<!-- ===================== UPLOAD SIGNATURE POPUP ===================== -->
		<div class="sig-popup-overlay" id="sigPopupOverlay" onclick="closeSigPopupOutside(event)">
			<div class="sig-popup" id="sigPopup">
				<div class="sig-popup-header">
					<span class="sig-popup-title"><i class="bi bi-pen-fill" style="color:var(--primary-color);margin-right:7px;"></i>Upload Your Signature</span>
					<button class="sig-popup-close" onclick="closeUploadSigPopup()"><i class="bi bi-x-lg"></i></button>
				</div>
				<div class="sig-popup-body">
					<p class="sig-popup-hint">Draw your signature below, then click <b>Accept &amp; Submit</b></p>
					<div class="sig-popup-canvas-wrap">
						<canvas id="sigPopupCanvas"></canvas>
						<div class="sig-popup-placeholder" id="sigPopupPlaceholder">
							<span>Sign here...</span>
						</div>
					</div>
				</div>
				<div class="sig-popup-footer">
					<button class="gs-btn gs-btn--outline gs-btn--sm" onclick="clearPopupSignature()">
						<i class="bi bi-eraser-fill"></i> Clear
					</button>
					<button class="gs-btn gs-btn--primary" onclick="acceptAndSubmitSignature()" style="flex:1;">
						<i class="bi bi-check-circle-fill"></i> Accept &amp; Submit
					</button>
				</div>
			</div>
		</div>
		<!-- ===================== PDF PREVIEW SLIDE-UP ===================== -->
		<div class="pdf-preview-overlay" id="pdfPreviewOverlay" onclick="closePdfPreviewOutside(event)">
			<div class="pdf-preview-panel" id="pdfPreviewPanel">
				<div class="pdf-preview-header">
					<span class="pdf-preview-title"><i class="bi bi-file-earmark-pdf-fill" style="color:#dc2626;margin-right:7px;"></i>LOA Document — Bertshi UK Limited.pdf</span>
					<button class="pdf-preview-close" onclick="closePdfPreview()"><i class="bi bi-x-lg"></i></button>
				</div>
				<div class="pdf-preview-content">
					<!-- Dummy PDF pages -->
					<div class="pdf-page" id="pdfPage1">
						@if(!empty($loa_generated))
							<div class="pdf-preview-container" style="aspect-ratio: 1 / 1.4;overflow: hidden;border: 0;border-radius: 0;">
								<embed src="{{ $loa_generated }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" type="application/pdf" style="width: 100%; height: 100%; display: block;" />
							</div>
						@endif
					</div>
				</div>
				<div class="pdf-preview-nav">
					@if(!empty($loa_generated))
						<a href="{{ $loa_generated }}" download class="gs-btn gs-btn--outline gs-btn--sm">
							<i class="bi bi-download"></i> Download PDF
						</a>
					@endif
						<button class="gs-btn gs-btn--teal gs-btn--sm" onclick="closePdfPreview()"><i class="bi bi-x-circle-fill"></i> Close</button>
				</div>
			</div>
		</div>

		<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
		<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"></script>
		<script>
			window.Pusher = Pusher;
			const activeBodyLeadId = document.body.getAttribute('data-lead-id');
			let uploadedFiles = [];

			const fileInput = document.getElementById('fileInput');
			const fileList = document.getElementById('fileList');
			const dropzone = document.getElementById('dropzone');
			
			dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('over'); });
			dropzone.addEventListener('dragleave', () => dropzone.classList.remove('over'));
			dropzone.addEventListener('drop', e => {
			  e.preventDefault(); dropzone.classList.remove('over'); handleFiles(e.dataTransfer.files);
			});
			fileInput.addEventListener('change', () => handleFiles(fileInput.files));
			
			window.handleFiles = function(files) {
				Array.from(files).forEach(file => {
					// Unique ID to match the HTML element with the file in our array
					const fileId = Date.now() + Math.random().toString(36).substr(2, 9);
					
					// Track the file object along with its ID
					uploadedFiles.push({ id: fileId, file: file });

					const ext = file.name.split('.').pop().toLowerCase();
					let icon='bi-file-earmark', iconColor='';
					if(ext==='pdf'){icon='bi-file-earmark-pdf-fill';iconColor='color:#dc2626;';}
					else if(['jpg','jpeg','png'].includes(ext)){icon='bi-file-earmark-image-fill';iconColor='color:#2563eb;';}
					
					const item = document.createElement('div');
					item.className='loa-file-item';
					// Added data-id and updated the onclick to call a remove function
					item.setAttribute('data-id', fileId);
					item.innerHTML=`
					<div class="loa-file-icon" style="${iconColor}"><i class="bi ${icon}"></i></div>
					<div class="loa-file-info"><span class="loa-file-name">${file.name}</span></div>
					<button class="loa-file-eye" onclick="removeTrackedFile('${fileId}')">
						<i class="bi bi-trash"></i>
					</button>`;
					fileList.appendChild(item);
				});
				showToast(`${files.length} file(s) added.`,'bi-paperclip');
			};

			window.removeTrackedFile = function(fileId) {
				uploadedFiles = uploadedFiles.filter(item => item.id !== fileId);
				const element = document.querySelector(`[data-id="${fileId}"]`);
				if(element) element.remove();
			};

			window.handleLOASent = function(btn) {
				showToast('LOA Already submitted, please wait for our team to review.','bi bi-check-circle-fill');
				return;
			};
			
			/* ============================================================
			   SEND LOA
			============================================================ */
			window.handleSendLOA = function(btn) {
			  const checkbox = document.getElementById('loaAuth');
			  if(!checkbox.checked){
			    showToast('Please authorize by checking the box first.','bi-exclamation-circle-fill');
			    checkbox.closest('.loa-checkbox-wrap').style.animation='shake 0.4s ease';
			    setTimeout(()=>checkbox.closest('.loa-checkbox-wrap').style.animation='',500);
			    return;
			  }

				if (btn.classList.contains('disable')) {
					showToast('Please Submit signature before sending the LOA.','bi-exclamation-triangle-fill');
					return;
				}


			  btn.innerHTML='<i class="bi bi-hourglass-split"></i> sending...'; btn.disabled=true;
				const formData = new FormData();

				formData.append('lead_id', activeBodyLeadId);

				// Append each uploaded file to the FormData object
				uploadedFiles.forEach(item => {
					formData.append('files[]', item.file);
				});

				const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
				if(csrfToken) {
					formData.append('_token', csrfToken);
				}

				fetch('/verify-loa-doc', {
					method: 'POST',
					body: formData,
					headers: {
					'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => {
					if (!response.ok) throw new Error('Network response was not ok');
					return response.json();
				})
				.then(data => {
					// Success handling
					btn.innerHTML='<i class="bi bi-check-circle-fill"></i> LOA Sent!';
					btn.style.background='var(--primary-color)';
					showToast('LOA Submitted successfully! Our team will review shortly.','bi-check-circle-fill');
					
					// Clear files array and UI list on success
					uploadedFiles = [];
					fileList.innerHTML = '';
				})
				.catch(error => {
					console.error('Error:', error);
					showToast('Something went wrong. Please try again.', 'bi-exclamation-octagon-fill');
					btn.innerHTML='<i class="bi bi-send-fill"></i> Verify LOA →';
					btn.disabled = false;
				});


			}
			
			/* ============================================================
			   PDF PREVIEW SLIDE-UP
			============================================================ */
			function openPdfPreview() {
			  document.getElementById('pdfPreviewOverlay').classList.add('active');
			}
			function closePdfPreview() {
			  document.getElementById('pdfPreviewOverlay').classList.remove('active');
			}
			function closePdfPreviewOutside(e) {
			  if(e.target === document.getElementById('pdfPreviewOverlay')) closePdfPreview();
			}
			
			/* ============================================================
			   TOAST
			============================================================ */
			function showToast(msg, icon='bi-check-circle-fill') {
			  const t=document.getElementById('toast');
			  document.getElementById('toastMsg').textContent=msg;
			  t.querySelector('i').className=`bi ${icon}`;
			  t.classList.add('show');
			  setTimeout(()=>t.classList.remove('show'),3200);
			}
			
			/* Set doc date */
			document.getElementById('docDate').textContent = new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
			
			/* Shake animation */
			const style = document.createElement('style');
			style.textContent=`@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}`;
			document.head.appendChild(style);
			
			document.addEventListener('click', function (e) {
			    const btn = e.target.closest('.edit-mpan-btn');
			    if(btn){
			      const wrapper = btn.closest('.input-addon-wrap');
			      const input = wrapper.querySelector('.mpan-input');
			      if (input.hasAttribute('readonly')) {
			          input.removeAttribute('readonly');
			          input.focus();
			          btn.innerHTML = '<i class="bi bi-check-lg"></i>';
			          btn.title = 'Save';
			      } else {
			          input.setAttribute('readonly', true);
			          btn.innerHTML = '<i class="bi bi-pencil"></i>';
			          btn.title = 'Edit MPAN';
			          showToast('MPAN updated successfully.','bi-check-circle-fill');
			      }
			    }
			    const mrnBtn = e.target.closest('.edit-mrn-btn');
			    if (mrnBtn) {
			        const wrapper2 = mrnBtn.closest('.input-addon-wrap');
			        const input2 = wrapper2.querySelector('.mrn-input');
			        if (input2.hasAttribute('readonly')) {
			            input2.removeAttribute('readonly');
			            input2.focus();
			            mrnBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
			            mrnBtn.title = 'Save';
			        } else {
			            input2.setAttribute('readonly', true);
			            mrnBtn.innerHTML = '<i class="bi bi-pencil"></i>';
			            mrnBtn.title = 'Edit MPRN';
			        }
			    }
			});
			
		</script>
		<script>
			const inputs = document.querySelectorAll('.gs-verify-input');

			inputs.forEach((input, index) => {

				// Only number allow + auto next focus
				input.addEventListener('input', (e) => {

					// Non-number remove
					e.target.value = e.target.value.replace(/[^0-9]/g, '');

					// Next input focus
					if (e.target.value && index < inputs.length - 1) {
						inputs[index + 1].focus();
					}
				});

				// Backspace pe previous input
				input.addEventListener('keydown', (e) => {

					if (e.key === 'Backspace' && input.value === '' && index > 0) {
						inputs[index - 1].focus();
					}
				});

				// Paste full OTP support
				input.addEventListener('paste', (e) => {

					e.preventDefault();

					const pasteData = (e.clipboardData || window.clipboardData)
						.getData('text')
						.replace(/[^0-9]/g, '')
						.split('');

					pasteData.forEach((num, i) => {
						if (inputs[i]) {
							inputs[i].value = num;
						}
					});

					// Last filled input pe focus
					const lastIndex = Math.min(pasteData.length, inputs.length) - 1;

					if (lastIndex >= 0) {
						inputs[lastIndex].focus();
					}
				});

			});
			window.handlePasskey = async function(button) {

				const inputs = document.querySelectorAll('.gs-verify-input');

				let passkey = '';

				inputs.forEach(input => {
					passkey += input.value;
				});

				if (passkey.length !== 8) {
					window.showError('Please enter complete 8-digit passkey.');
					return;
				}

				const btnText = button.querySelector('span');
				const btnIcon = document.getElementById('verifyBtnIcon');

				button.disabled = true;
				btnText.innerText = 'Validating...';
				btnIcon.className = 'spinner-border spinner-border-sm';

				try {

					const response = await fetch("{{ route('verify.passkey') }}", {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
							"X-CSRF-TOKEN": "{{ csrf_token() }}"
						},
						body: JSON.stringify({
							lead_id: "{{ $lead->id }}",
							pass_key: passkey,
							current_url: window.location.origin + window.location.pathname
						})
					});

					const data = await response.json();

					if (data.success) {

						window.location.href = data.redirect_url;

					} else {

						window.showError(data.message);

						button.disabled = false;
						btnText.innerText = 'Continue';
						btnIcon.className = 'bi bi-send-fill';
					}

				} catch (e) {

					window.showError('Something went wrong.');

					button.disabled = false;
					btnText.innerText = 'Continue';
					btnIcon.className = 'bi bi-send-fill';
				}
			}

			window.showError = function(message) {
				const errorDiv = document.getElementById('passkeyError');

				errorDiv.style.display = 'block';
				errorDiv.innerHTML = `
					<i class="bi bi-x-circle-fill"></i>
					${message}
				`;
			};

			window.openUploadSigPopup = function() {
				const btn = document.getElementById('submitsignbtn');
				if (btn.classList.contains('already-signed')) {
					showToast('Document Already signed.','bi-exclamation-circle-fill');
					return;
				}

				if (btn.classList.contains('disable')) {
					return;
				}

				const icon = btn.querySelector('i');
				
				// Get the dynamic lead ID from the button's data attribute or template variable
				const leadId = {{ $lead->id }};
								
				// 2. UI Loading State
				btn.disabled = true;
				icon.className = 'spinner-border spinner-border-sm me-1';
				btn.lastChild.textContent = ' Please wait...';

				$.ajax({
					url: '/gs-pdf',
					type: 'POST',
					data: {
						_token: '{{ csrf_token() }}', // Laravel CSRF Protection token
						lead_id: leadId,
						status: btn.value,
					},
					success: function(response) {
						if(response.success) {
							btn.disabled = true;
							icon.className = 'bi bi-check-circle-fill text-success';
							btn.lastChild.textContent = ' Sent Successfully!';
							window.handleSignableSuccess();
						} else {
							window.showToast('failed to process document.','bi-exclamation-circle-fill');
						}
					},
					error: function(xhr) {
						console.log("--- DD() Payload Output ---");
						console.log(xhr.responseText);
						
						// Reset button state
						btn.disabled = false;
						icon.className = 'bi bi-pencil-fill';
						btn.lastChild.textContent = ' Submit Signature';

						showToast('failed to process document.','bi-exclamation-circle-fill');
					}
				});
			};

			window.handleSignableSuccess = function() {
				const targetButton = document.getElementById('submitsignbtn');
				
				if (targetButton) {
					const alreadyHasAlert = targetButton.nextElementSibling && targetButton.nextElementSibling.classList.contains('gs-alert--success');
										
					if (!alreadyHasAlert) {
						const alertDiv = document.createElement('div');
						alertDiv.className = 'gs-alert gs-alert--success';
						alertDiv.innerHTML = `
							<i class="bi bi-check-circle-fill"></i> 
							<span>Please sign the document sent to your email.</span>
						`;

						const statusAlertDiv = document.createElement('div');
						statusAlertDiv.className = 'gs-alert gs-alert--success';
						statusAlertDiv.innerHTML = `
							<i class="spinner-border spinner-border-sm me-1" style="border-top-color: #28a745;" role="status"></i>
							<span>Waiting for your signature...</span>
						`;
						setTimeout(() => {
							targetButton.parentNode.insertBefore(statusAlertDiv, targetButton.nextSibling);
						}, 5000);
						targetButton.parentNode.insertBefore(alertDiv, targetButton.nextSibling);
						
					}
				}
			};
			window.handleLoaVerification = function(url, leadId) {
				
			}

			window.Echo = new Echo({
				broadcaster: 'pusher',
				key: "{{ config('broadcasting.connections.pusher.key') }}",
				cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
				forceTLS: true
			});

			window.Echo.channel('loa-updates')
				.listen('.loa.signed', (e) => {
					console.log('Realtime LOA Status Event Received:', e);
					
					// Verify the incoming broadcast matches the current page's active lead record
					if (activeBodyLeadId && parseInt(e.data.lead_id) === parseInt(activeBodyLeadId)) {
						let btnnn = document.getElementById('submitsignbtn');

						document.querySelectorAll('.gs-alert--success').forEach(el => el.remove());

						// Construct the definitive final signature verified layout alert element
						const finalAlertDiv = document.createElement('div');
						finalAlertDiv.className = 'gs-alert gs-alert--success';
						finalAlertDiv.innerHTML = `<i class="bi bi-patch-check-fill text-success"></i> 
							<span><strong>Success!</strong> The document has been securely signed. Please Click on verify LOA Button to proceed.</span>`;

						if (btnnn) {
							btnnn.parentNode.insertBefore(finalAlertDiv, btnnn.nextSibling);
							btnnn.classList.remove('disable');
        					btnnn.removeAttribute('disabled');
							btnnn.classList.add('already-signed');
						}

						let btnnnn = document.getElementById('loasubbutton');
						if (btnnnn) {
							btnnnn.classList.remove('disable');
        					btnnnn.removeAttribute('disabled');
						}						
					}
				});
				
		</script>
	</body>

</html>