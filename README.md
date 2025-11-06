# 🏦 B-Cash - Digital Wallet with AI Facial Recognition

A modern, secure digital wallet application built with PHP and JavaScript, featuring **real AI-powered facial recognition** for enhanced security.

## ✨ Key Features

### 🤖 **AI Facial Recognition**
- **Real-time face detection** using Face-API.js
- **Live camera capture** with anti-spoofing protection
- **Liveness detection** to prevent photo/video attacks
- **Bank-grade security** with similarity scoring
- **100% free** - no API costs (client-side processing)

### 💰 **Digital Wallet**
- User registration and authentication
- Secure money transfers
- Transaction history and analytics
- Real-time balance updates
- Multi-factor security

### 🔒 **Security Features**
- Face verification before account creation
- Encrypted password storage
- Session management
- CSRF protection
- Secure file uploads

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser with camera support

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Strel-k/Bank-Cash-AJAX-Project.git
   cd Bank-Cash-AJAX-Project
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < database/b_cash.sql
   ```

3. **Configure database connection**
   ```php
   // Edit app/config/Config.php
   const DB_HOST = 'localhost';
   const DB_NAME = 'b_cash_ajax';
   const DB_USER = 'your_username';
   const DB_PASS = 'your_password';
   ```

4. **Download AI models**
   ```bash
   php download-ai-models.php
   ```

5. **Set up web server**
   - Point document root to `public/` directory
   - Ensure `.htaccess` is enabled for Apache

6. **Test the system**
   ```bash
   php test_system.php
   ```

## 🤖 AI Setup Guide

### Face-API.js Models
The system uses Face-API.js for real-time facial recognition:

1. **Automatic Setup**: Run `php download-ai-models.php`
2. **Manual Setup**: Visit `setup-face-recognition.html` for detailed instructions
3. **Test AI**: Open `public/test-face-ai.html` to verify AI functionality

### AI Features
- **Face Detection**: 90-95% accuracy
- **Liveness Detection**: Anti-spoofing protection
- **Face Comparison**: Mathematical similarity scoring
- **Real-time Processing**: No server-side AI costs

## 📁 Project Structure

```
B-Cash-AJAX-Project/
├── app/
│   ├── config/          # Configuration files
│   ├── controllers/     # Business logic
│   ├── models/         # Data models
│   ├── services/       # External services
│   └── helpers/        # Utility functions
├── public/
│   ├── api/            # API endpoints
│   ├── js/             # JavaScript files
│   ├── css/            # Stylesheets
│   ├── models/         # AI model files
│   └── *.php           # Public pages
├── database/           # SQL schema files
├── uploads/            # User uploads (protected)
└── css/               # Additional styles
```

## 🔒 Security

### Data Protection
- **No sensitive data** in Git repository
- **Encrypted uploads** directory
- **Secure session** management
- **GDPR compliant** data handling

### AI Privacy
- **Client-side processing** - no data sent to external servers
- **Local face analysis** - biometric data stays in browser
- **No tracking** - Face-API.js runs offline

## 🛠️ Development

### Testing
- `test_system.php` - System verification
- `public/test-face-ai.html` - AI functionality test
- Browser console - Real-time AI debugging

### API Endpoints
- `/api/auth.php` - Authentication
- `/api/wallet.php` - Wallet operations
- `/api/transaction.php` - Transaction management
- `/api/verification.php` - Document verification

## 📱 Browser Support

### Required Features
- **Camera access** (getUserMedia API)
- **Modern JavaScript** (ES6+)
- **Canvas support** for image processing
- **WebRTC** for real-time video

### Tested Browsers
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+

## 🎯 Production Deployment

### Security Checklist
- [ ] Update database credentials
- [ ] Enable SSL/HTTPS
- [ ] Set secure file permissions
- [ ] Configure error logging
- [ ] Remove test files
- [ ] Enable production mode

### Performance
- [ ] Enable gzip compression
- [ ] Optimize database queries
- [ ] Cache static assets
- [ ] Monitor AI model loading

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

### Development Guidelines
- Follow PSR-4 autoloading standards
- Use meaningful commit messages
- Test AI features across browsers
- Maintain security best practices

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

### Documentation
- `SECURITY.md` - Security guidelines
- `setup-face-recognition.html` - AI setup guide
- Inline code comments

### Issues
- Report bugs via GitHub Issues
- Include browser and PHP version
- Provide steps to reproduce

## 🎉 Acknowledgments

- **Face-API.js** - Real-time face recognition
- **Font Awesome** - Icons
- **Inter Font** - Typography
- **PHP Community** - Framework inspiration

---

**Built with ❤️ for secure digital banking**

*Real AI facial recognition • Bank-grade security • 100% free to run*