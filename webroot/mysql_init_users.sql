USE yourdatabase;

SET NAMES 'utf8';

DROP TABLE IF EXISTS User; 
--
-- Table for user
--
CREATE TABLE User
(
  id INT AUTO_INCREMENT PRIMARY KEY,
  acronym CHAR(20) UNIQUE NOT NULL,
  firstname VARCHAR(50),
  lastname VARCHAR(50),
  email VARCHAR(80) UNIQUE,
  password VARCHAR(100),
  account_type INT NOT NULL,
  code VARCHAR(100),
  activated boolean,
  last_updated DATETIME,
  created DATETIME
) ENGINE INNODB CHARACTER SET utf8;
 
