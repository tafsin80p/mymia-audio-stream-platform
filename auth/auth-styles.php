<?php
/**
 * Authentication Page Styles
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
    :root {
        --primary-color: #BF4C1A;
        --primary-dark: #9F2B1A;
        --primary-light: #FF6B3D;
        --background-dark: #0A0A0A;
        --background-card: #1E1E1E;
        --text-light: #ffffff;
        --text-gray: #999999;
        --border-color: rgba(255, 255, 255, 0.1);
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body.nymia-login-body {
        background: linear-gradient(135deg, #0A0A0A 0%, #1A1A1A 100%);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .nymia-login-container {
        width: 100%;
        max-width: 600px;
        background: linear-gradient(135deg, #1E1E1E 0%, #141414 100%);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    
    .nymia-login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .nymia-login-logo {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        overflow: hidden;
    }
    
    .nymia-login-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }
    
    .nymia-login-title {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 10px;
    }
    
    .nymia-login-subtitle {
        color: var(--text-gray);
        font-size: 1rem;
    }
    
    .nymia-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .nymia-tab-button {
        flex: 1;
        padding: 15px;
        background: transparent;
        border: none;
        color: var(--text-gray);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }
    
    .nymia-tab-button:hover {
        color: var(--primary-light);
    }
    
    .nymia-tab-button.active {
        color: var(--text-light);
        border-bottom-color: var(--primary-color);
    }
    
    .nymia-form-group {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        align-content: flex-start;
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .nymia-form-group:has(> .nymia-form-label:first-child:not(:only-child)) {
        display: block;
    }
    
    .nymia-form-group > .nymia-form-label:first-child:not(:only-child) {
        display: block;
        width: 100%;
    }
    
    .nymia-form-label {
        display: block;
        color: var(--text-light);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .nymia-form-input {
        width: 100%;
        padding: 11px 16px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-light);
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .nymia-form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(191, 76, 26, 0.1);
        background: rgba(255, 255, 255, 0.08);
    }
    
    .nymia-form-input::placeholder {
        color: var(--text-gray);
    }
    
    /* Password Input Wrapper */
    .nymia-password-input-wrapper {
        position: relative;
        width: 100%;
    }
    
    .nymia-password-input-wrapper .nymia-form-input {
        padding-right: 50px;
    }
    
    .nymia-password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-gray);
        transition: color 0.3s ease;
    }
    
    .nymia-password-toggle:hover {
        color: var(--text-light);
    }
    
    .nymia-password-toggle:focus {
        outline: none;
    }
    
    .nymia-eye-icon {
        width: 20px;
        height: 20px;
        stroke: currentColor;
    }
    
    .nymia-remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .nymia-remember-me input[type="checkbox"] {
        accent-color: var(--primary-color);
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .nymia-remember-me label {
        color: var(--text-gray);
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .nymia-submit-button {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(191, 76, 26, 0.3);
        margin-bottom: 20px;
    }
    
    .nymia-submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(191, 76, 26, 0.4);
    }
    
    .nymia-submit-button:active {
        transform: translateY(0);
    }
    
    .nymia-forgot-password {
        text-align: center;
        margin-top: 15px;
    }
    
    .nymia-forgot-password a {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }
    
    .nymia-forgot-password a:hover {
        color: var(--primary-light);
    }
    
    .nymia-error-message,
    .nymia-success-message {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .nymia-error-message {
        background: rgba(220, 53, 69, 0.1);
        border-left: 3px solid #dc3545;
        color: #ff6b7d;
    }
    
    .nymia-error-message svg,
    .nymia-success-message svg {
        flex-shrink: 0;
    }
    
    .nymia-error-message span,
    .nymia-success-message span {
        flex: 1;
    }
    
    .nymia-success-message {
        background: rgba(40, 167, 69, 0.1);
        border-left: 3px solid #28a745;
        color: #28a745;
    }
    
    .nymia-divider {
        margin: 30px 0;
        text-align: center;
        position: relative;
        color: var(--text-gray);
        font-size: 0.9rem;
    }
    
    .nymia-divider::before,
    .nymia-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40%;
        height: 1px;
        background: var(--border-color);
    }
    
    .nymia-divider::before {
        left: 0;
    }
    
    .nymia-divider::after {
        right: 0;
    }
    
    @media (max-width: 480px) {
        .nymia-login-container {
            padding: 30px 20px;
        }
        
        .nymia-login-title {
            font-size: 1.5rem;
        }
    }
    
    .nymia-tab-content {
        display: none;
    }
    
    .nymia-tab-content.active {
        display: block;
    }
    
    .nymia-social-login {
        display: flex;
        flex-direction: row;
        gap: 12px;
        margin-bottom: 20px;
        justify-content: center;
        align-items: center;
    }
    
    .nymia-social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        width: 50px;
        height: 50px;
        min-width: 50px;
    }
    
    .nymia-social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.08);
    }
    
    .nymia-social-google {
        border-color: rgba(66, 133, 244, 0.3);
    }
    
    .nymia-social-google:hover {
        border-color: #4285F4;
        background: rgba(66, 133, 244, 0.1);
    }
    
    .nymia-social-facebook {
        border-color: rgba(24, 119, 242, 0.3);
    }
    
    .nymia-social-facebook:hover {
        border-color: #1877F2;
        background: rgba(24, 119, 242, 0.1);
    }
    
    .nymia-social-apple {
        border-color: rgba(0, 0, 0, 0.3);
    }
    
    .nymia-social-apple:hover {
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.12);
    }
    
    .nymia-social-btn svg {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
    }
    
    .nymia-social-btn span {
        display: none;
    }
    
    /* Login Modal Styles */
    .nymia-login-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .nymia-login-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
    }
    
    .nymia-login-modal-content {
        position: relative;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        z-index: 10001;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    .nymia-login-modal-content::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    .nymia-login-modal-content .nymia-login-container {
        margin: 0;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    
    .nymia-login-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10002;
        transition: all 0.3s ease;
        color: var(--text-light);
    }
    
    .nymia-login-modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }
    
    .nymia-login-modal-close svg {
        width: 20px;
        height: 20px;
    }
    
    @media (max-width: 768px) {
        .nymia-login-modal-content {
            max-width: 100%;
            max-height: 100vh;
        }
        
        .nymia-login-modal-content .nymia-login-container {
            border-radius: 0;
            padding: 30px 20px;
        }
        
        .nymia-login-modal-close {
            top: 10px;
            right: 10px;
        }
    }
    
    /* Forgot Password Form Styles */
    .nymia-forgot-password-header {
        text-align: center;
        margin-bottom: 24px;
    }
    
    .nymia-forgot-password-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-light);
        margin: 0 0 8px 0;
    }
    
    .nymia-forgot-password-header p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin: 0;
    }
    
    .nymia-submit-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }
    
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    
    /* Account Type Selector Styles */
    .nymia-account-type-selector {
        display: flex;
        gap: 16px;
        margin-top: 8px;
    }
    
    .nymia-account-type-option {
        flex: 1;
        cursor: pointer;
    }
    
    .nymia-account-type-option input[type="radio"] {
        display: none;
    }
    
    .nymia-account-type-card {
        padding: 20px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.03);
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .nymia-account-type-option:hover .nymia-account-type-card {
        border-color: rgba(191, 76, 26, 0.5);
        background: rgba(191, 76, 26, 0.05);
        transform: translateY(-2px);
    }
    
    .nymia-account-type-option input[type="radio"]:checked + .nymia-account-type-card {
        border-color: var(--primary-color);
        background: rgba(191, 76, 26, 0.1);
        box-shadow: 0 4px 12px rgba(191, 76, 26, 0.2);
    }
    
    .nymia-account-type-card.selected {
        border-color: var(--primary-color) !important;
        background: rgba(191, 76, 26, 0.1) !important;
        box-shadow: 0 4px 12px rgba(191, 76, 26, 0.2) !important;
    }
    
    .nymia-account-type-card > div:first-child {
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .nymia-account-type-card > div:nth-child(2) {
        font-weight: 600;
        color: #fff;
        margin-bottom: 4px;
    }
    
    .nymia-account-type-card > div:nth-child(3) {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    @media (max-width: 480px) {
        .nymia-account-type-selector {
            flex-direction: column;
            gap: 12px;
        }
        
        .nymia-account-type-card {
            padding: 16px;
        }
    }
</style>