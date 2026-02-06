Restaurant reservation platform inspired by the style of TheFork website.

## Features

- User registration and login (client/admin)
- Restaurant search and filtering
- Table reservation by date/time/people
- Reservation management (client and admin)
- Add, edit, and remove restaurants (admin)
- Restaurant change history

## Project Structure

admin_reservas.php # Reservation management (admin)
admin_restaurante.php # Add restaurant (admin)
cancelar_reserva.php # Cancel reservation (client)
dashboard.php # Main dashboard (client/admin)
db.php # Database connection
editar_restaurante.php # Edit restaurant (admin)
index.php # Login page
logout.php # Logout
registar.php # User registration
remover_restaurante.php # Remove restaurant (admin)
reservar.php # Make reservation (client)
css/style.css # Styles
js/script.js # Scripts

## Requirements

- PHP 8+
- PostgreSQL
- Web server (PHP server)

## Installation

1. Clone the repository.
2. Configure the PostgreSQL database as shown in `db.php`.
3. Import the SQL schema (not included).
4. Place the files on a web server.
5. Access `index.php` to start.

## Inspiration

Design and features are inspired by [TheFork](https://www.thefork.pt/).

## License

Academic project. Free to use for educational purposes.
