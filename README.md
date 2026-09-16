# Books Market

A PHP/MySQL bookstore project using Bootstrap and PDO.

## Local setup

1. Start Apache and MySQL from XAMPP.
2. Create a MySQL database named `project`.
3. Import `project.sql` for the original project data.
4. Import `database_update.sql` to add account activation and contact-message support.
5. Copy the repository into `htdocs` and open `http://localhost/The_project/`.

## Main features

- User registration with pending account approval.
- Admin activation/deactivation of user accounts.
- Role-based protection for admin pages.
- Secure password hashing with automatic upgrade of legacy SHA1 passwords on successful login.
- Book management with stock validation and safer image uploads.
- Shopping cart with ownership checks.
- Checkout that validates stock, creates orders, updates inventory, and uses a transaction.
- Admin order cancellation with stock restoration.
- Customer order history in `my_orders.php`.
- Contact form saved to the database and admin message management in `Acontact.php`.
- Real session logout through `logout.php`.
