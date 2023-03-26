# PHP-Chat-Backend-with-Slim-Framework-and-SQLite
This repository contains a simple, lightweight PHP chat backend built using the Slim Framework and SQLite. It serves as a backend for chat applications, allowing users to register, log in, and send and receive messages in real-time.

Features:

User registration and authentication
Sending and receiving messages
Secure password storage using bcrypt
CORS support for cross-origin requests
SQLite database for easy setup and deployment
Technologies used:

PHP 8
Slim Framework 4
SQLite 3
Tuupola CORS Middleware
Getting started:

Clone the repository.
Run composer install to install dependencies.
Ensure the database folder has write permissions.
Start the PHP development server by running php -S localhost:8000 -t public from the project root.
The API is now available at http://localhost:8000.
API endpoints:

POST /register: Register a new user with a username and password.
POST /login: Log in with a registered user's credentials.
POST /messages: Send a new message as a logged-in user.
GET /messages: Retrieve all messages in the chat.
Please note that this project serves as a backend for chat applications and does not include a frontend.
To use this backend, create a frontend application (e.g., using React, Angular, Vue.js, or Blazor WebAssembly) that communicates with the API endpoints provided by this backend.
