-- SmartResto — Schéma SQL v1
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS dish_ingredients;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS ingredients;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS tables_resto;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('gerant','serveur','cuisinier','caissier') NOT NULL,
  phone VARCHAR(20),
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tables_resto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  number VARCHAR(10) NOT NULL UNIQUE,
  capacity INT NOT NULL DEFAULT 4,
  pos_x INT DEFAULT 0,
  pos_y INT DEFAULT 0,
  status ENUM('libre','occupee','reservee','nettoyer') DEFAULT 'libre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  client_name VARCHAR(120) NOT NULL,
  client_phone VARCHAR(30),
  people INT DEFAULT 2,
  reserved_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (table_id) REFERENCES tables_resto(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dishes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  available TINYINT(1) DEFAULT 1,
  image VARCHAR(255),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ingredients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  unit VARCHAR(20) DEFAULT 'kg',
  stock DECIMAL(10,2) DEFAULT 0,
  threshold DECIMAL(10,2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ingredient_id INT NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  type ENUM('entree','sortie') NOT NULL,
  note VARCHAR(255),
  user_id INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  waiter_id INT,
  status ENUM('brouillon','envoyee','en_preparation','pret','servie','encaissee','annulee') DEFAULT 'brouillon',
  note TEXT,
  urgent TINYINT(1) DEFAULT 0,
  sent_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (table_id) REFERENCES tables_resto(id),
  FOREIGN KEY (waiter_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  dish_id INT NOT NULL,
  quantity INT DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  note VARCHAR(255),
  status ENUM('en_attente','en_preparation','pret','servi') DEFAULT 'en_attente',
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (dish_id) REFERENCES dishes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  tax DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('impayee','payee','partielle') DEFAULT 'impayee',
  cashier_id INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (cashier_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  method ENUM('especes','mtn_momo','moov_money','celtiis','carte') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  reference VARCHAR(120),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ================== DONNÉES DE DÉMO ==================
-- Mots de passe hashés avec password_hash(..., PASSWORD_DEFAULT)
-- admin123 / serveur123 / cuisine123 / caisse123
INSERT INTO users (full_name,email,password_hash,role,phone) VALUES
('Fadel Adjovi','admin@smartresto.bj','$2y$10$e0NRPu4C.jI7oQ2PZ0e2XeYm1JmvV1G1qk6oJ9wG9V0mR3qz.HxZm','gerant','+229 97 00 00 01'),
('Awa Serveuse','serveur@smartresto.bj','$2y$10$D8L0Yk9GJv0kQzq3n5oZ.eXk4z2xJ9dQvj8QKgYnMgS0oS0M2Yq5W','serveur','+229 97 00 00 02'),
('Kofi Chef','cuisine@smartresto.bj','$2y$10$Q3iVwzC0/YkY2b6vJ5w1B.zvXn0mFyO2v3aG2m5Fbz3fFbYlqL7GC','cuisinier','+229 97 00 00 03'),
('Mariam Caisse','caisse@smartresto.bj','$2y$10$Vy5w2qOxV0Sxz5cCg6E5S.b2wI0aQaJ5nQKmS9k1x3v5uJcM.7YkS','caissier','+229 97 00 00 04');

INSERT INTO tables_resto (number,capacity,pos_x,pos_y,status) VALUES
('T1',2,50,50,'libre'),('T2',4,200,50,'occupee'),('T3',4,350,50,'reservee'),
('T4',2,500,50,'libre'),('T5',6,50,220,'nettoyer'),('T6',4,200,220,'libre'),
('T7',8,350,220,'occupee'),('T8',2,500,220,'libre'),
('T9',4,50,390,'libre'),('T10',4,200,390,'libre');

INSERT INTO categories (name,display_order) VALUES
('Entrées',1),('Plats locaux',2),('Plats internationaux',3),('Grillades',4),('Desserts',5),('Boissons',6);

INSERT INTO dishes (category_id,name,description,price,available) VALUES
(1,'Salade béninoise','Salade fraîche aux légumes locaux',2500,1),
(1,'Beignets de crevettes','Crevettes panées maison',3500,1),
(2,'Poulet DG','Plat camerounais revisité',6500,1),
(2,'Riz sauce arachide','Riz parfumé, sauce arachide',4500,1),
(2,'Amiwo poisson','Amiwo et poisson braisé',5500,1),
(3,'Pâtes carbonara','Pâtes fraîches, sauce crémeuse',5000,1),
(3,'Burger classic','Bœuf, cheddar, frites maison',5500,1),
(4,'Poulet braisé','Demi-poulet aux épices',7000,1),
(4,'Poisson braisé','Bar braisé, sauce piment',8500,1),
(5,'Ananas frais','Ananas de Bénin',1500,1),
(5,'Crème caramel','Dessert maison',2000,1),
(6,'Sodabi cocktail','Cocktail signature',3000,1),
(6,'Jus de bissap','Boisson locale rafraîchissante',1000,1),
(6,'Eau minérale 50cl','',500,1);

INSERT INTO ingredients (name,unit,stock,threshold) VALUES
('Riz','kg',25,10),('Poulet','kg',8,10),('Poisson bar','kg',6,5),
('Tomate','kg',12,5),('Oignon','kg',15,5),('Huile','L',20,8),
('Farine','kg',18,10),('Sucre','kg',10,5),('Ananas','pièce',20,10),
('Bissap','kg',4,3),('Eau 50cl','pièce',48,24);
