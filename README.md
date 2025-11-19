# EasyChat - Simple Messaging App

A complete messaging web application built with PHP, MySQL, and modern frontend technologies. Features include user authentication, profile management, and a real-time two-way messaging system.

## Highlights

- **Zero Dependencies**: Pure PHP with no framework overhead
- **Real-time Messaging**: Auto-refresh every 3 seconds for instant communication
- **Secure**: Bcrypt password hashing, prepared statements, XSS protection
- **Responsive Design**: Built with Tailwind CSS for mobile-first experience
- **Rich Media**: Support for image sharing in conversations
- **User Management**: Complete profile system with avatar uploads
- **MVC Architecture**: Clean, maintainable code structure

## Features

### 1. User Authentication
- **User Registration**: Create new accounts with email verification and password validation
- **User Login**: Secure login with password hashing (bcrypt)
- **Session Management**: Persistent user sessions
- **Form Validation**: Both frontend and backend validation

### 2. User Profile Management
- Update name, email, and phone number
- Upload and change profile pictures
- Automatic image resizing and compression
- Profile picture validation (type and size)
- View profile information and account creation date

### 3. Two-Way Messaging System
- **Send & Receive Messages**: Real-time bidirectional messaging
- **Message Types**: Text messages and image attachments
- **AJAX Real-Time Updates**: No page refresh required
- **Message Status**: Mark messages as read/unread with visual indicators
- **Conversation List**: View all conversations with last message preview
- **Unread Message Count**: Badge showing number of unread messages
- **Live Polling**: Auto-refresh every 3 seconds for new messages
- **User Search**: Find and start conversations with other users

## Technical Stack

### Backend
- **PHP** (Core PHP) - Server-side logic
- **MySQL** - Database management
- **PDO** - Database abstraction layer
- **MVC Architecture** - Clean folder structure

### Frontend
- **HTML5** - Markup
- **Tailwind CSS** - Styling framework
- **JavaScript (ES6+)** - Client-side interactivity
- **AJAX/Fetch API** - Asynchronous communication

## Project Structure

```
message-app/
├── config/
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection
├── controllers/
│   ├── AuthController.php  # Authentication logic
│   ├── ProfileController.php # Profile management
│   └── MessageController.php # Messaging logic
├── models/
│   ├── User.php           # User model
│   └── Message.php        # Message model
├── views/
│   ├── register.php       # Registration page
│   ├── profile.php        # Profile management page
│   ├── messages.php       # Messaging interface
│   └── logout.php         # Logout handler
├── public/
│   ├── css/               # Custom stylesheets (if any)
│   ├── js/
│   │   ├── auth.js        # Authentication scripts
│   │   ├── profile.js     # Profile scripts
│   │   └── messages.js    # Messaging scripts
   └── uploads/
       ├── profiles/      # Profile pictures
       │   └── .gitkeep   # Keep directory in git
       └── messages/      # Message images
           └── .gitkeep   # Keep directory in git
├── database.sql           # Database schema and seed data
├── index.php              # Login page (entry point)
├── .env.example           # Environment variables template
├── .htaccess              # Apache configuration
├── .gitignore             # Git ignore rules
└── README.md              # Project documentation

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server with Apache (for .htaccess support) or Nginx
- PHP extensions: PDO, pdo_mysql, gd (for image processing)
- Composer (optional, for future dependencies)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd message-app
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < database.sql
   ```
   
   Or manually execute the SQL script in your MySQL client.

3. **Configure environment variables**
   
   Copy the example environment file and configure it:
   ```bash
   cp .env.example .env
   ```
   
   Then edit `.env` with your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=message_app
   DB_USER=root
   DB_PASS=your_password
   BASE_URL=http://localhost:8000
   ```

4. **Set up upload directories**
   
   Ensure the upload directories have proper permissions:
   ```bash
   chmod -R 755 public/uploads
   ```
   
   Create `.gitkeep` files to maintain directory structure in Git:
   ```bash
   touch public/uploads/profiles/.gitkeep
   touch public/uploads/messages/.gitkeep
   ```

5. **Start the development server**
   
   Using PHP built-in server (development only):
   ```bash
   php -S localhost:8000
   ```
   
   **Note**: For production or to use `.htaccess` features, use Apache or Nginx.
   
   For Apache with XAMPP/MAMP:
   - Place the project in your `htdocs` directory
   - Access via `http://localhost/message-app`

6. **Access the application**
   
   Open your browser and navigate to: `http://localhost:8000`


## Configuration

### Environment Variables
The application uses a `.env` file for configuration. Use `.env.example` as a template:
- `DB_HOST`: Database host (default: localhost)
- `DB_NAME`: Database name (default: message_app)
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `BASE_URL`: Application base URL (default: http://localhost:8000)

### Application Settings
In `config/config.php`:
- `MAX_FILE_SIZE`: Maximum upload file size (default: 5MB)
- `ALLOWED_IMAGE_TYPES`: Accepted image formats
- `MESSAGE_REFRESH_INTERVAL`: Auto-refresh interval in milliseconds (default: 3000ms)

### Apache Configuration (.htaccess)
The `.htaccess` file includes:
- URL rewriting for cleaner URLs
- File access restrictions for sensitive files (.sql, .md, .gitignore)
- Upload size limits (10MB)
- Error logging configuration
- Directory browsing disabled
- GZIP compression enabled

## Default Test Users

The database includes test users with the password `password123`:

| Name          | Email              | Phone        |
|---------------|-------------------|--------------|
| John Doe      | john@example.com  | 1234567890   |
| Jane Smith    | jane@example.com  | 0987654321   |
| Bob Johnson   | bob@example.com   | 5551234567   |

### Login
1. Navigate to `http://localhost:8000`
2. Enter your email and password
3. Click "Login"

### Send a Message
1. Click on a user from the conversations list or search for a new contact
2. Type your message in the input field
3. Optionally attach an image using the image button
4. Press Enter or click Send

### Update Profile
1. Click on your profile picture or name in the top right
2. Update your information
3. Upload a new profile picture if desired
4. Click "Update Profile"

### Delete a Message
1. Hover over any message you sent
2. Click the delete icon (trash can)
3. Confirm the deletion

## Security Features

- **Password Security**: Password hashing using `password_hash()` with bcrypt algorithm
- **SQL Injection Prevention**: Prepared statements with PDO
- **XSS Protection**: Input sanitization through `htmlspecialchars()` and `strip_tags()`
- **File Upload Security**: Type and size validation for uploaded files
- **Session Management**: Secure session-based authentication
- **File Access Control**: `.htaccess` restricts access to sensitive files (.sql, .md, etc.)
- **Directory Protection**: Directory browsing disabled via `.htaccess`
- **CSRF Protection**: Ready for enhancement (can be added to forms)

## API Endpoints

### Authentication
- `POST /controllers/AuthController.php?action=login` - User login
- `POST /controllers/AuthController.php?action=register` - User registration
- `GET /views/logout.php` - User logout

### Messages
- `POST /controllers/MessageController.php?action=send` - Send message
- `GET /controllers/MessageController.php?action=get_conversation` - Get conversation
- `GET /controllers/MessageController.php?action=get_conversations_list` - List all conversations
- `GET /controllers/MessageController.php?action=get_new_messages` - Poll for new messages
- `GET /controllers/MessageController.php?action=get_users` - Get all users
- `GET /controllers/MessageController.php?action=get_unread_count` - Get unread count
- `POST /controllers/MessageController.php?action=delete_message` - Delete message
- `GET /controllers/MessageController.php?action=get_deleted_messages` - Get recently deleted messages

### Profile
- `POST /controllers/ProfileController.php?action=update` - Update profile
- `POST /controllers/ProfileController.php?action=change_password` - Change password
- `GET /controllers/ProfileController.php?action=get` - Get profile data

## Additional Files

### .env.example
Template file for environment configuration. Contains all necessary environment variables with default values. Copy this to `.env` and customize for your environment.

### .htaccess
Apache web server configuration file that provides:
- Clean URL routing
- Security restrictions on sensitive files
- Upload size limits
- Error logging settings
- Performance optimizations (compression)

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

Nethal Fernando

## Acknowledgments

- Tailwind CSS for the beautiful UI components
- PHP community for excellent documentation
- All contributors and testers
