-- -- Membuat database
-- CREATE DATABASE achats;
-- USE achats;

-- -- Tabel users
-- CREATE TABLE users (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     fullName VARCHAR(255) NOT NULL,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     password VARCHAR(255) NOT NULL,
--     address TEXT,
--     img VARCHAR(255),
--     number VARCHAR(15)
-- );

-- -- Tabel sellers
-- CREATE TABLE sellers (
--     sellerId INT AUTO_INCREMENT PRIMARY KEY,
--     userId INT NOT NULL,
--     bio TEXT,
--     img VARCHAR(255),
--     rate FLOAT DEFAULT 0,
--     FOREIGN KEY (userId) REFERENCES users(id)
-- );

-- -- Tabel skills
-- CREATE TABLE skills (
--     skillId INT AUTO_INCREMENT PRIMARY KEY,
--     skillValue VARCHAR(255) NOT NULL UNIQUE
-- );

-- -- Tabel sellerSkill
-- CREATE TABLE sellerSkill (
--     sellerSkillId INT AUTO_INCREMENT PRIMARY KEY,
--     sellerId INT NOT NULL,
--     skillId INT NOT NULL,
--     FOREIGN KEY (sellerId) REFERENCES sellers(sellerId),
--     FOREIGN KEY (skillId) REFERENCES skills(skillId)
-- );

-- -- Tabel products
-- CREATE TABLE products (
--     productId INT AUTO_INCREMENT PRIMARY KEY,
--     sellerId INT NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     description TEXT,
--     price INT NOT NULL,
--     stock INT DEFAULT 0,
--     rate FLOAT DEFAULT 0,
--     sold INT DEFAULT 0,
--     FOREIGN KEY (sellerId) REFERENCES sellers(sellerId)
-- );

-- -- Tabel imagesFactory
-- CREATE TABLE imagesFactory (
--     imageId INT AUTO_INCREMENT PRIMARY KEY,
--     entityId INT NOT NULL,
--     imageUrl VARCHAR(255) NOT NULL,
--     uploadedAt DATETIME DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Tabel purchases
-- CREATE TABLE purchases (
--     purchaseId INT AUTO_INCREMENT PRIMARY KEY,
--     productId INT NOT NULL,
--     userId INT NOT NULL,
--     purchaseDate DATETIME DEFAULT CURRENT_TIMESTAMP,
--     quantity INT NOT NULL,
--     amountTotal INT NOT NULL,
--     FOREIGN KEY (productId) REFERENCES products(productId),
--     FOREIGN KEY (userId) REFERENCES users(id)
-- );

-- -- Tabel purchaseStatus
-- CREATE TABLE purchaseStatus (
--     purchaseStatusId INT AUTO_INCREMENT PRIMARY KEY,
--     purchaseId INT NOT NULL,
--     statusValue VARCHAR(255) NOT NULL,
--     FOREIGN KEY (purchaseId) REFERENCES purchases(purchaseId)
-- );

-- -- Tabel orders
-- CREATE TABLE orders (
--     orderId INT AUTO_INCREMENT PRIMARY KEY,
--     userId INT NOT NULL,
--     sellerId INT NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     description TEXT,
--     price INT NOT NULL,
--     workTime INT NOT NULL,
--     deadline DATETIME,
--     status VARCHAR(255) NOT NULL,
--     FOREIGN KEY (userId) REFERENCES users(id),
--     FOREIGN KEY (sellerId) REFERENCES sellers(sellerId)
-- );

-- -- Tabel comments
-- CREATE TABLE comments (
--     commentId INT AUTO_INCREMENT PRIMARY KEY,
--     userId INT NOT NULL,
--     productId INT NOT NULL,
--     comment TEXT NOT NULL,
--     timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (userId) REFERENCES users(id),
--     FOREIGN KEY (productId) REFERENCES products(productId)
-- );

CREATE DATABASE achats;
USE achats;

-- Tabel users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullName VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  address TEXT NOT NULL,
  img VARCHAR(255),
  number INT
);

-- Tabel sellers
CREATE TABLE sellers (
  sellerId INT AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  bio TEXT,
  img VARCHAR(255),
  rate INT,
  FOREIGN KEY (userId) REFERENCES users(id)
);

-- Tabel skills
CREATE TABLE skills (
  skillId INT AUTO_INCREMENT PRIMARY KEY,
  skillValue VARCHAR(255) NOT NULL
);

-- Tabel sellerSkill
CREATE TABLE sellerSkill (
  sellerSkillId INT AUTO_INCREMENT PRIMARY KEY,
  sellerId INT NOT NULL,
  skillId INT NOT NULL,
  FOREIGN KEY (sellerId) REFERENCES sellers(sellerId),
  FOREIGN KEY (skillId) REFERENCES skills(skillId)
);

-- Tabel products (Barang)
CREATE TABLE products (
  productId INT AUTO_INCREMENT PRIMARY KEY,
  sellerId INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price INT NOT NULL,
  stock INT NOT NULL,
  rate INT,
  sold INT,
  FOREIGN KEY (sellerId) REFERENCES sellers(sellerId)
);

-- Tabel productsCategories
CREATE TABLE productsCategories (
  productsCategoriesId INT AUTO_INCREMENT PRIMARY KEY,
  productId INT NOT NULL,
  productIdValues VARCHAR(255),
  FOREIGN KEY (productId) REFERENCES products(productId)
);

-- Tabel imagesFactory (Menyimpan semua gambar untuk produk, order, komentar, dll)
CREATE TABLE imagesFactory (
  imageId INT AUTO_INCREMENT PRIMARY KEY,
  entityId INT NOT NULL,
  entityType ENUM('product', 'order', 'comment') NOT NULL, -- Menyimpan tipe entitas (produk, order, komentar)
  imageUrl VARCHAR(255),
  uploadedAt DATETIME,
  FOREIGN KEY (entityId) REFERENCES products(productId) ON DELETE CASCADE -- Jika entityType = 'product'
);

-- Tabel purchases (Pembelian Barang)
CREATE TABLE purchases (
  purchaseId INT AUTO_INCREMENT PRIMARY KEY,
  productId INT NOT NULL,
  userId INT NOT NULL,
  purchaseDate DATETIME,
  quantity INT NOT NULL,
  amountTotal INT NOT NULL,
  FOREIGN KEY (productId) REFERENCES products(productId),
  FOREIGN KEY (userId) REFERENCES users(id)
);

-- Tabel purchaseStatus
CREATE TABLE purchaseStatus (
  purchaseStatusId INT AUTO_INCREMENT PRIMARY KEY,
  purchaseId INT NOT NULL,
  statusValue VARCHAR(255),
  FOREIGN KEY (purchaseId) REFERENCES purchases(purchaseId)
);

-- Tabel orders (Pembelian Jasa)
CREATE TABLE orders (
  orderId INT AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  sellerId INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price INT NOT NULL,
  workTime INT NOT NULL,
  deadline DATETIME,
  status VARCHAR(255),
  FOREIGN KEY (userId) REFERENCES users(id),
  FOREIGN KEY (sellerId) REFERENCES sellers(sellerId)
);

-- Tabel comments (Komentar Produk)
CREATE TABLE comments (
  commentId INT AUTO_INCREMENT PRIMARY KEY,
  userId INT NOT NULL,
  productId INT NOT NULL,
  comment TEXT,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (userId) REFERENCES users(id),
  FOREIGN KEY (productId) REFERENCES products(productId)
);
