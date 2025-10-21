SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS lineup_db;
USE lineup_db;

CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    shortname VARCHAR(100),
    birthdate DATE,
    team VARCHAR(50)
    
);

INSERT INTO players (first_name, last_name, birthdate) VALUES
('Miel', 'T\'Syen', '2015-07-30'),
('Jack', 'Stroobants', '2015-08-06'),
('Staf', 'V', NULL),
('Thibo', 'Fierens', '2015-05-19'),
('Tiebe', 'Leduc', NULL),
('Loris', 'Fierens', NULL),
('MuratC', 'Cilingir', NULL),
('MuratY', 'Yagmuroglu', NULL),
('Arda', 'Shakir', NULL),
('Senn', 'Goossens', NULL),
('Léno', 'Kerckhofs', NULL),
('Jayden', 'Theys', NULL),
('Wannes', 'Van Gestel', NULL),
('Rune', 'Truyers', NULL),
('Seppe', 'Geukens', NULL),
('Otis', 'Laurent', NULL),
('Franklin', 'Tebe', NULL),
('NoahS', 'Sterckx-Geukens', NULL),
('NoahW', 'Willems', NULL),
('Alessio', 'Armento', NULL),
('Tyrone', 'Monkau', NULL);

update players set short_name=first_name;
update players set first_name = 'Murat' where short_name in ('MuratY','MuratC');  
update players set first_name = 'Noah' where short_name in ('NoahS','NoahW');  


drop table player_scores;
CREATE TABLE IF NOT EXISTS player_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT,
    position INT,
    score INT,
    score_date DATE,
    FOREIGN KEY (player_id) REFERENCES players(id)
);

