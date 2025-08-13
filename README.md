# BookHaven - Online Bookstore Platform

<div align="center">
  <img src="Screenshots/logo.jpg" alt="BookHaven Logo" width="200">
  
  [[PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [[MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [[HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://html.spec.whatwg.org)
  [[CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://www.w3.org/Style/CSS)
  [[JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://javascript.com)
</div>

## 📖 About The Project

BookHaven is a comprehensive online bookstore platform designed to connect book sellers with readers through an intuitive and responsive web interface. The system facilitates a marketplace where multiple sellers can list their books for sale while customers can browse, search, and purchase from a diverse catalog.

The platform allows customers to browse all available books and place orders, while giving sellers the ability to accept or reject these orders based on their inventory and business requirements.

### 🌟 Key Features

- **Multi-Vendor Marketplace**: Support for multiple book sellers
- **User Authentication**: Secure login system with role-based access (Customer, Seller, Admin)
- **Book Management**: Comprehensive book catalog with detailed information
- **Order Management**: Complete order processing workflow
- **Review System**: Customer reviews and ratings
- **Shopping Cart**: Add to cart and wishlist functionality
- **Search & Filter**: Advanced book search and filtering options
- **Admin Dashboard**: Powerful admin tools for platform management
- **Seller Dashboard**: Dedicated seller interface for inventory management
- **Responsive Design**: Mobile-friendly interface
- **Dark Mode Support**: Toggle between light and dark themes

## 🖼️ Screenshots

### Home Page

![Home Page](Screenshots/home-page.jpg)

### Book Catalog
![Book Catalog](Screenshots/book-catalog.jpg)

### Book Details
![Book Details](Screenshots/book-details.jpg)

### Shopping Cart
![Shopping Cart](Screenshots/shopping-cart.jpg)

### Admin Dashboard
![Admin Dashboard](Screenshots/admin-dashboard.jpg)

### Seller Dashboard
![Seller Dashboard](Screenshots/seller-dashboard.jpg)

### User Management
![User Management](Screenshots/user-management.jpg)

### Order Management
![Order Management](Screenshots/order-management.jpg)

## 🛠️ Built With

* **Backend**: PHP 7.4+
* **Database**: MySQL 5.7+
* **Frontend**: HTML5, CSS3, JavaScript
* **Styling**: Custom CSS (Responsive Design)
* **Icons**: Font Awesome
* **Charts**: Chart.js
* **Server**: Apache (XAMPP/WAMP recommended)

## 📋 Prerequisites

Before you begin, ensure you have met the following requirements:

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Modern web browser

## ⚡ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/bookhaven.git
   cd bookhaven
   ```

2. **Set up your web server**
   - Place the project folder in your web server's document root
   - For XAMPP: `C:\xampp\htdocs\bookhaven`
   - For WAMP: `C:\wamp64\www\bookhaven`

3. **Create the database**
   ```sql
   CREATE DATABASE bookhaven;
   ```

4. **Import the database structure**
   ```bash
   mysql -u root -p bookhaven < database/bookhaven.sql
   ```

5. **Configure database connection**
   - Open `config/database.php`
   - Update the database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "bookhaven";
   ```

6. **Set up file permissions**
   - Ensure the `upload/` directory is writable
   ```bash
   chmod 755 upload/
   ```

7. **Access the application**
   - Open your web browser
   - Navigate to `http://localhost/bookhaven`

## 🚀 Usage

### For Customers
1. Register as a new customer or login with existing credentials
2. Browse the book catalog
3. Search and filter books by category, author, or title
4. Add books to cart or wishlist
5. Place orders and track order status
6. Write reviews for purchased books

### For Sellers
1. Register as a seller and set up your store
2. Add books to your inventory
3. Manage book details, pricing, and stock
4. Process customer orders (accept/reject)
5. View sales analytics and reports
6. Manage store settings

### For Administrators
1. Login with admin credentials
2. Manage users, books, and categories
3. Monitor platform activity
4. Review and approve seller applications
5. Generate reports and analytics
6. Configure site settings

## 🏗️ Project Structure

```
bookhaven/
│
├── 📁 assets/
│   ├── 📁 css/
│   ├── 📁 js/
│   └── 📁 images/
│
├── 📁 config/
│   └── 📄 database.php
│
├── 📁 includes/
│   ├── 📄 header.php
│   ├── 📄 footer.php
│   └── 📄 functions.php
│
├── 📁 admin/
│   ├── 📄 dashboard.php
│   ├── 📄 users.php
│   ├── 📄 books.php
│   └── 📄 settings.php
│
├── 📁 seller/
│   ├── 📄 dashboard.php
│   ├── 📄 books.php
│   ├── 📄 orders.php
│   └── 📄 analytics.php
│
├── 📁 upload/
│   └── 📁 books/
│
├── 📁 database/
│   └── 📄 bookhaven.sql
│
├── 📁 Screenshots/
│   └── 📷 [Project Screenshots]
│
├── 📄 index.php
├── 📄 login.php
├── 📄 register.php
├── 📄 books.php
├── 📄 book-details.php
├── 📄 cart.php
└── 📄 README.md
```

## 👥 User Roles

### 🛍️ Customer
- Browse and search books
- Add to cart and wishlist
- Place and track orders
- Write reviews and ratings
- Manage profile

### 🏪 Seller
- Manage book inventory
- Process orders
- View sales analytics
- Manage store settings
- Handle customer inquiries

### 👨‍💼 Administrator
- Platform oversight
- User management
- Content moderation
- Site configuration
- Analytics and reporting

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to match your database settings:

```php
<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "bookhaven";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
```

### Site Settings
Configure site-wide settings through the admin panel:
- Site name and description
- Contact information
- Payment settings
- Email configurations

## 🤝 Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 🐛 Bug Reports

If you encounter any bugs or issues, please:

1. Check if the issue already exists in the [Issues](https://github.com/yourusername/bookhaven/issues) section
2. If not, create a new issue with:
   - Clear description of the problem
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots (if applicable)
   - Your environment details

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Contact

**Project Developer**: Eissa ALgumaei
- Email: al
- LinkedIn: [Your LinkedIn](https://linkedin.com/in/yourprofile)
- GitHub: [@yourusername](https://github.com/yourusername)

**Project Link**: [https://github.com/yourusername/bookhaven](https://github.com/yourusername/bookhaven)

## 🙏 Acknowledgments

- [Font Awesome](https://fontawesome.com) for icons
- [Chart.js](https://www.chartjs.org) for analytics charts
- [PHP](https://php.net) community for excellent documentation
- [MySQL](https://mysql.com) for robust database solution

---

<div align="center">
  <strong>Made with ❤️ for book lovers everywhere</strong>
</div>

---

## 📊 Project Status

- ✅ User Authentication System
- ✅ Book Catalog Management
- ✅ Shopping Cart Functionality
- ✅ Order Management System
- ✅ Review and Rating System
- ✅ Admin Dashboard
- ✅ Seller Dashboard
- ✅ Responsive Design
- ✅ Dark Mode Support
- 🔄 Payment Gateway Integration (In Progress)
- 📋 Mobile App (Planned)

## 🌟 Support

If you like this project, please give it a ⭐ on GitHub!

For support and questions, please use the [Discussions](https://github.com/yourusername/bookhaven/discussions) tab.
