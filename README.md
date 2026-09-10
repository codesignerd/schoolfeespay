# School Fees Payment System

This project helps educational institutions manage their student enrollments and fee collections from a single centralized system. It takes administrative workflows, processes pending student registrations, and produces verifiable fee payment records. No complicated spreadsheets are required, just straightforward functionality that keeps school finances organized and transparent.

## Installation

Follow these instructions to get the application running on a local local development environment.

1. Clone the repository:
```bash
git clone https://github.com/codesignerd/schoolfeespay.git
cd schoolfeespay
```

2. Import the database schema:
Import the provided SQL schema into a MySQL server to create the required tables and initial seed data.
```bash
mysql -u root -p < database/schema.sql
```

3. Configure the database connection:
Update the database configuration file with the correct local credentials.
```php
// config/database.php
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'schoolhub',
    'username' => 'root',
    'password' => 'secret',
    'charset' => 'utf8mb4'
];
```

4. Start the application:
Place the project folder in the web root directory of a local server like Apache or Nginx and map the document root to the `public` directory.

## Usage

The application provides different portals based on the logged-in user role. Users can authenticate via the login page to access their respective dashboards. 

To log into the administrator portal, use the predefined demo credentials:
```text
Email: admin@school.test
Password: Admin@12345
```

Once logged in, administrators can navigate to the Fees section to generate a new invoice for a student. The invoice requires selecting a student, specifying an amount, and setting a due date. Students logging into their portal will immediately see this outstanding balance and can log a payment for the finance team to verify.

## Features

* **Role-Based Access Control**: Granular permissions isolating student portals from administrative and finance operations.
* **Student Onboarding Workflow**: Automated registration portal for students with an administrative approval queue.
* **Comprehensive Fee Management**: Full lifecycle tracking of tuition fees including invoice generation, payment logging, and balance calculations.
* **Payment Verification System**: Two-step payment confirmation requiring finance officers to approve submitted receipts before balances are cleared.
* **Audit Logging**: Immutable tracking of critical system actions including logins, record deletions, and payment verifications.
* **Real-time Dashboard Metrics**: Aggregated views of outstanding fees, student counts, and recent system activities.

## Technologies Used

| Category | Technology |
| :--- | :--- |
| **Backend** | [PHP 8.3](https://www.php.net/) |
| **Database** | [MySQL](https://www.mysql.com/) |
| **Architecture** | Custom MVC Framework |
| **Frontend** | [Bootstrap 5](https://getbootstrap.com/) |
| **Icons** | [Bootstrap Icons](https://icons.getbootstrap.com/) |
| **Visualizations** | [Chart.js](https://www.chartjs.org/) |

## API Documentation

The application relies on standard form submissions utilizing the Post/Redirect/Get pattern. Below are the core functional endpoints used by the interface.

#### POST /login
**Description**: Authenticates a user and establishes a secure session.

**Request**:
```json
{
"email": "admin@school.test",
"password": "securepassword",
"_csrf": "generated_csrf_token"
}
```

**Response**:
```text
302 Found
Location: /dashboard
```

**Errors**:
* 419: Session token expired.
* 302 (Redirect back to login): Invalid credentials or inactive account.

#### POST /students
**Description**: Creates a new student record from the admin dashboard and assigns parents.

**Request**:
```json
{
"admission_no": "ADM-2024-0001",
"first_name": "John",
"last_name": "Doe",
"gender": "male",
"email": "john.doe@example.com",
"status": "active",
"_csrf": "generated_csrf_token"
}
```

**Response**:
```text
302 Found
Location: /students
```

#### POST /fees/invoice
**Description**: Generates a new fee invoice for a specific student.

**Request**:
```json
{
"student_id": "1",
"term_id": "2",
"invoice_no": "INV-2024-0001",
"description": "First Term Tuition",
"amount": "150000",
"due_date": "2024-12-01",
"_csrf": "generated_csrf_token"
}
```

**Response**:
```text
302 Found
Location: /fees
```

#### POST /fees/payment
**Description**: Records a payment against an outstanding invoice. Can be submitted by an admin or a student.

**Request**:
```json
{
"invoice_id": "5",
"receipt_no": "RCT-2024-0042",
"amount_paid": "50000",
"payment_method": "bank",
"payment_reference": "TRX-123456789",
"_csrf": "generated_csrf_token"
}
```

**Response**:
```text
302 Found
Location: /fees
```

#### POST /fees/verify
**Description**: Allows a finance administrator to approve or reject a pending payment submission.

**Request**:
```json
{
"id": "12",
"decision": "verified",
"_csrf": "generated_csrf_token"
}
```

**Response**:
```text
302 Found
Location: /fees
```

## Author Info

* LinkedIn: https://linkedin.com/in/blessingabayomi
* X (Twitter): https://x.com/remedyuiux

## Badges

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

[![Readme was generated by Dokugen](https://img.shields.io/badge/Readme%20was%20generated%20by-Dokugen-brightgreen)](https://dokugen.samueltuoyo.com)