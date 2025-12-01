
# 💬 PHPProsjekt — Gemini Chatbot

A group project built with **PHP**, **MySQL**, and **Gemini API**, featuring secure authentication and a chatbot interface.
Developed and tested locally with **XAMPP**.
---

## 🧩 Project Structure

```
PHPPROSJEKT/
├── public/
│   ├── index.php         # Chatbot interface (main page)
│   ├── signup.php        # User registration
│   ├── login.php         # User login
│   ├── logout.php        # Session logout
│   ├── ChatBot.php       # Handles Gemini API requests
│   └── assets/           # (optional) CSS, JS, etc.
│
├── src/
│   ├── auth.php          # Session + login helpers
│   ├── database.php      # PDO connection (MySQL / SQLite)
│
├── db/
│   └── (empty)           # Local database folder (ignored in git)
│
├── vendor/               # Composer dependencies (ignored in git)
├── .env                  # Local secrets (ignored)
├── .env.example          # Example env file for teammates
├── .gitignore
└── README.md
```

---

## ⚙️ Requirements

* **XAMPP** (PHP 8+ with Apache + MySQL)
* **Composer** installed globally
* A **Google Gemini API key** (add it to your `.env`)

---

## 🧠 Setup Guide

### 1️⃣ Clone the Repository

```bash
cd C:\xampp\htdocs
git clone https://github.com/YourTeam/phpprosjekt.git
cd phpprosjekt
```

### 2️⃣ Install Dependencies

```bash
composer install
```

### 3️⃣ Create the Database

Open [phpMyAdmin](http://localhost/phpmyadmin) and run:

```sql
CREATE DATABASE IF NOT EXISTS phpprosjekt
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'phpuser'@'localhost'
  IDENTIFIED BY 'StrongPass_123!';

GRANT ALL PRIVILEGES ON phpprosjekt.* TO 'phpuser'@'localhost';
FLUSH PRIVILEGES;
```

### 4️⃣ Create the Tables

Select the **phpprosjekt** database → go to **SQL** → run:

```sql
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('user','assistant') NOT NULL,
  text MEDIUMTEXT NOT NULL,
  ts INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Configure Environment Variables

Copy `.env.example` → rename it to `.env`, then edit:

```ini
GEMINI_API_KEY=your_real_gemini_key_here
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=phpprosjekt;charset=utf8mb4
DB_USER=phpuser
DB_PASS=StrongPass_123!
```

---

## Run the Project

1. Open **XAMPP Control Panel**

   * Start **Apache** and **MySQL**
2. In your browser:

   ```
   http://localhost/PHPPROSJEKT/public/signup.php
   ```

✅ You can now:

* Create an account
* Log in / Log out
* Access the chatbot

---

## Team Collaboration

Each team member must:

1. Clone the repo.
2. Create their own local MySQL database (see above).
3. Copy `.env.example` → rename to `.env`.
4. Update their DB credentials and Gemini API key.
5. Run `composer install`.

The `.env` file is **never shared or committed**.

---

## Git Ignore Setup

Your `.gitignore` file should include:

```gitignore
# Ignore environment / secret files
.env
.env.*
*.env
*.env.*
!.env.example

# Ignore dependencies
/vendor/

/db/*.sqlite
/db/*.db
```

## Authors

Group project — IS-216 - Chatbot
Developed using PHP, MySQL, and the Google Gemini API.


