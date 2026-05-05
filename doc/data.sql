USE gamestats;

INSERT INTO users (username, email, password, bio, role)
VALUES
(
    'admin',
    'admin@gamestats.fr',
    'admin123',
    'Compte administrateur de test.',
    'admin'
),
(
    'lukas',
    'lukas@gamestats.fr',
    'lukas123',
    'Passionné de jeux vidéo et de statistiques.',
    'user'
);

INSERT INTO games (title, platform, genre, release_year, publisher, global_sales, critic_score, user_score)
VALUES
('The Legend of Zelda', 'Switch', 'Adventure', 2017, 'Nintendo', 25.00, 9.7, 9.5),
('Mario Kart Wii', 'Wii', 'Racing', 2008, 'Nintendo', 37.00, 8.2, 8.7),
('Minecraft', 'PC', 'Sandbox', 2011, 'Mojang', 33.00, 9.0, 9.2),
('Grand Theft Auto V', 'PS4', 'Action', 2013, 'Rockstar Games', 45.00, 9.6, 9.1),
('FIFA 18', 'PS4', 'Sports', 2017, 'Electronic Arts', 24.00, 8.4, 7.9),
('Call of Duty: Black Ops', 'X360', 'Shooter', 2010, 'Activision', 30.00, 8.8, 8.3),
('Elden Ring', 'PC', 'RPG', 2022, 'Bandai Namco', 20.00, 9.6, 9.2),
('Red Dead Redemption 2', 'PS4', 'Adventure', 2018, 'Rockstar Games', 32.00, 9.7, 9.4);

INSERT INTO favorites (user_id, game_id)
VALUES
(2, 1),
(2, 3);

INSERT INTO reviews (user_id, game_id, rating, comment)
VALUES
(2, 1, 5, 'Excellent jeu, très immersif.'),
(2, 3, 4, 'Très bon jeu créatif avec beaucoup de liberté.');

INSERT INTO tickets (user_id, title, platform, genre, message, status)
VALUES
(2, 'Cyberpunk 2077', 'PC', 'RPG', 'Je pense que ce jeu devrait être ajouté au catalogue.', 'pending');

INSERT INTO forum_messages (user_id, message)
VALUES
(2, 'Bienvenue sur le forum GameStats !');