Laravel Repository Management System

📌 About This Project

This is a Repository Management System built with Laravel for the backend and React.js for the frontend. It allows users to create, manage, and track repositories with details like descriptions, unique repository codes, and the number of stars.

🚀 Features

Create, edit, and delete repositories

Fetch all repositories with filtering and pagination

Manage repository metadata (name, description, URL, number of stars)

RESTful API with Swagger documentation

🛠️ Tech Stack

Backend (Laravel 10)

PHP 8+

Laravel Framework

MySQL Database

Swagger API Documentation

Laravel Validation & Exception Handling

Frontend (React.js)

React Router

Axios for API Calls

Bootstrap UI

React Toast Notifications


API Documentation (Swagger)

This project includes Swagger API documentation.

Setup Swagger in Laravel:
1. Install the l5-swagger package:
composer require darkaonline/l5-swagger

2. Publish the configuration:
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

3. Generate the Swagger documentation:
php artisan l5-swagger:generate

4. Access the API docs at:
http://your-app-url/api/documentation