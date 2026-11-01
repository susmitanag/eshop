# Everly

An online store built with PHP and MySQL. Shoppers can browse products, filter and sort them, add items to a cart without reloading the page, and place an order. An admin can manage products, upload photos and update order status.

## Screenshots

![Home](screenshots/home.png)

![Shop](screenshots/shop.png)

![Product](screenshots/product.png)

## What it does

Shop
- Home page with categories, popular items, a sale section and new arrivals
- Shop page with category, price range, sale, new and in stock filters, search, sorting and pagination
- Product page with sale prices, stock messages and related products
- Cart that updates without a page reload, with a free delivery progress bar (free over $75, $5 below that)
- Checkout for logged in customers, paid on delivery
- Customer accounts (sign up, log in, log out) and an order history with a status timeline

Admin
- Dashboard with revenue, order count, orders waiting to ship, recent orders and low stock
- Add, edit and delete products, with photo upload (JPG, PNG or WebP, up to 2 MB)
- Orders list with status filters. Changing an order to Cancelled puts the items back in stock.

## Pictures and logo

The logo and the product pictures are drawn with SVG code, so the project needs no image files. To use a real photo, open Admin, then Products, choose Edit and upload a photo. The photo replaces the drawn picture for that product.

## Built with

- PHP 8 (no framework)
- MySQL with PDO and prepared statements
- Plain JavaScript (fetch for the cart, no libraries)
- Hand written HTML and CSS, Google Fonts (Bricolage Grotesque and Figtree)

## Run it locally with XAMPP

1. Copy this folder to `C:\xampp\htdocs\eshop`
2. Start Apache and MySQL in the XAMPP Control Panel
3. Open http://localhost/phpmyadmin, click Import, choose `db/schema.sql`, click Import
4. Open http://localhost/eshop/

Demo logins:

- Customer: `demo@everly.test` / `demo123`
- Admin (http://localhost/eshop/admin/login.php): `admin@everly.test` / `admin123`

## Notes

- Checkout locks the product rows inside a database transaction, so two people cannot buy the last item at the same time.
- Order prices are saved with each order, so later price changes do not change old orders.
- Passwords are stored with bcrypt. Uploaded files are checked by their real image type, not their file name.
- Products and orders are made up for the demo.
