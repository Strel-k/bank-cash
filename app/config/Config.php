<?php
class Config {
    // Database configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'b_cash_ajax';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    // Application settings
    const APP_NAME = 'B-Cash';
    const APP_URL = 'http://localhost';
    
    // Security settings
    const JWT_SECRET = 'your-secret-key-change-this';
    const TOKEN_EXPIRY = 3600; // 1 hour
    
    // Payment settings
    const MIN_TRANSFER_AMOUNT = 1.00;
    const MAX_TRANSFER_AMOUNT = 50000.00;
    const MAX_ADD_MONEY_AMOUNT = 100000.00;
    const MAX_BILL_PAYMENT_AMOUNT = 50000.00;
    const TRANSACTION_FEE = 0.00; // Free for now
    
    // API settings
    const API_VERSION = 'v1';
    const API_RATE_LIMIT = 100; // requests per minute
}
?>
