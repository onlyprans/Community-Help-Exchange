
# Community Help Exchange

## About the Project

Community Help Exchange is a web-based platform designed to connect people who need help with people who are willing to offer their skills, time and services.

The platform encourages community collaboration by allowing users to create help requests, offer assistance and communicate through responses.

## Features

- User registration and login
- Secure password storage
- User logout
- Create and manage help requests
- Post help offers
- Browse available help requests and offers
- Search and filter listings
- Respond to help requests
- Accept or reject responses
- Update help request status
- Personalised user dashboard

## Technologies Used

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP

### Database
- MySQL

### Development Environment
- XAMPP
- Visual Studio Code

## Project Structure

```text
community-help-exchange/
│
├── api/
├── config/
├── css/
├── js/
├── database/
│   └── community_help_exchange.sql
├── index.html
├── login.html
├── register.html
├── dashboard.php
├── browse.php
├── request-help.html
├── offer-help.html
└── README.md
```

## Installation and Setup

1. Install XAMPP on your computer.
2. Copy the project folder into the `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin at:

   http://localhost/phpmyadmin/

5. Create a database named:

   `community_help_exchange`

6. Select the database and import the SQL file from:

   `database/community_help_exchange.sql`

7. Check the database connection settings in:

   `config/db.php`

8. Open the project in your browser:

   http://localhost/community-help-exchange/

## How to Use

1. Register a new account.
2. Log in using your credentials.
3. Browse existing help requests and offers.
4. Post a help request if assistance is needed.
5. Post a help offer to share your skills.
6. Respond to requests posted by other users.
7. Manage responses and request statuses through the dashboard.

## Project Objective

The objective of Community Help Exchange is to build a collaborative digital platform that makes it easier for people to exchange assistance, share resources and strengthen community relationships.

---

**Project:** Community Help Exchange  
**Category:** Web Technology  
**Backend:** PHP and MySQL
