USE gamestats;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM private_messages;
DELETE FROM private_conversations;
DELETE FROM friendships;
DELETE FROM friend_requests;
DELETE FROM admin_moderation_logs;
DELETE FROM user_badges;
DELETE FROM notifications;
DELETE FROM moderation_logs;
DELETE FROM forum_messages;
DELETE FROM tickets;
DELETE FROM reviews;
DELETE FROM favorites;
DELETE FROM games;
DELETE FROM users;

ALTER TABLE private_messages AUTO_INCREMENT = 1;
ALTER TABLE private_conversations AUTO_INCREMENT = 1;
ALTER TABLE friendships AUTO_INCREMENT = 1;
ALTER TABLE friend_requests AUTO_INCREMENT = 1;
ALTER TABLE admin_moderation_logs AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE moderation_logs AUTO_INCREMENT = 1;
ALTER TABLE forum_messages AUTO_INCREMENT = 1;
ALTER TABLE tickets AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE games AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Mot de passe de tous les comptes de test : password123
-- Admin : admin@gamestats.fr / password123

INSERT INTO users
(id, username, email, password, avatar, bio, role, is_banned, ban_reason, email_verified, email_verification_token, email_verified_at, created_at)
VALUES
(1, 'Admin', 'admin@gamestats.fr', 'password123', NULL, 'Administrateur principal de GameStats.', 'admin', 0, NULL, 1, NULL, NOW(), '2026-01-01 10:00:00'),
(2, 'lukas1', 'lukas1@gamestats.fr', 'password123', NULL, 'Fan de RPG et de jeux narratifs.', 'user', 0, NULL, 1, NULL, NOW(), '2026-01-02 11:00:00'),
(3, 'lukas2', 'lukas2@gamestats.fr', 'password123', NULL, 'Joueur compétitif et amateur de FPS.', 'user', 0, NULL, 1, NULL, NOW(), '2026-01-03 12:00:00'),
(4, 'lukas3', 'lukas3@gamestats.fr', 'password123', NULL, 'Collectionneur de jeux rétro.', 'user', 0, NULL, 1, NULL, NOW(), '2026-01-04 13:00:00'),
(5, 'lukas4', 'lukas4@gamestats.fr', 'password123', NULL, 'Passionné de jeux indépendants.', 'user', 0, NULL, 1, NULL, NOW(), '2026-01-05 14:00:00'),
(6, 'lukas5', 'lukas5@gamestats.fr', 'password123', NULL, 'Compte banni pour test.', 'user', 1, 'Comportement toxique répété.', 1, NULL, NOW(), '2026-01-06 15:00:00'),
(7, 'NotVerified', 'notverified@gamestats.fr', 'password123', NULL, 'Compte de test non vérifié.', 'user', 0, NULL, 0, 'test_token_non_verifie_123456789', NULL, '2026-01-07 16:00:00');

INSERT INTO games
(id, title, platform, genre, release_year, publisher, global_sales, critic_score, user_score, image_url)
VALUES
(1, 'The Legend of Zelda: Breath of the Wild', 'Switch', 'Action-aventure', 2017, 'Nintendo', 31.15, 97.0, 8.7, NULL),
(2, 'Elden Ring', 'PC', 'Action RPG', 2022, 'Bandai Namco', 25.00, 96.0, 8.1, NULL),
(3, 'Minecraft', 'Multi', 'Sandbox', 2011, 'Mojang', 300.00, 93.0, 8.5, NULL),
(4, 'Grand Theft Auto V', 'Multi', 'Action', 2013, 'Rockstar Games', 200.00, 97.0, 8.3, NULL),
(5, 'The Witcher 3: Wild Hunt', 'Multi', 'RPG', 2015, 'CD Projekt', 50.00, 93.0, 9.2, NULL),
(6, 'Red Dead Redemption 2', 'Multi', 'Action-aventure', 2018, 'Rockstar Games', 61.00, 97.0, 8.8, NULL),
(7, 'Cyberpunk 2077', 'Multi', 'RPG', 2020, 'CD Projekt', 25.00, 86.0, 7.1, NULL),
(8, 'God of War', 'PlayStation', 'Action-aventure', 2018, 'Sony Interactive Entertainment', 23.00, 94.0, 9.1, NULL),
(9, 'Super Mario Odyssey', 'Switch', 'Plateforme', 2017, 'Nintendo', 28.50, 97.0, 8.9, NULL),
(10, 'Hades', 'Multi', 'Roguelike', 2020, 'Supergiant Games', 7.00, 93.0, 8.8, NULL),
(11, 'Hollow Knight', 'Multi', 'Metroidvania', 2017, 'Team Cherry', 5.00, 90.0, 9.0, NULL),
(12, 'Stardew Valley', 'Multi', 'Simulation', 2016, 'ConcernedApe', 20.00, 89.0, 8.7, NULL);

INSERT INTO favorites (user_id, game_id, created_at)
VALUES
(2, 1, '2026-02-01 10:00:00'),
(2, 2, '2026-02-01 11:00:00'),
(2, 5, '2026-02-01 12:00:00'),
(3, 4, '2026-02-02 10:00:00'),
(3, 6, '2026-02-02 11:00:00'),
(4, 3, '2026-02-03 10:00:00'),
(4, 9, '2026-02-03 11:00:00'),
(5, 10, '2026-02-04 10:00:00'),
(5, 11, '2026-02-04 11:00:00'),
(6, 7, '2026-02-05 10:00:00');

INSERT INTO reviews (id, user_id, game_id, rating, comment, created_at)
VALUES
(1, 2, 1, 5, 'Une aventure incroyable avec une liberté énorme.', '2026-02-10 10:00:00'),
(2, 2, 2, 5, 'Très exigeant mais vraiment satisfaisant.', '2026-02-10 11:00:00'),
(3, 3, 4, 4, 'Toujours aussi impressionnant malgré les années.', '2026-02-11 10:00:00'),
(4, 3, 6, 5, 'Ambiance, narration et monde ouvert excellents.', '2026-02-11 11:00:00'),
(5, 4, 3, 5, 'Un classique intemporel.', '2026-02-12 10:00:00'),
(6, 4, 9, 4, 'Très fun et parfaitement maîtrisé.', '2026-02-12 11:00:00'),
(7, 5, 10, 5, 'Gameplay nerveux et direction artistique superbe.', '2026-02-13 10:00:00'),
(8, 5, 11, 5, 'Un des meilleurs metroidvania.', '2026-02-13 11:00:00'),
(9, 6, 7, 3, 'Bon univers mais lancement compliqué.', '2026-02-14 10:00:00');

INSERT INTO tickets
(id, user_id, title, platform, genre, release_year, publisher, global_sales, critic_score, user_score, image_url, source_url, message, status, added_to_catalog, created_at)
VALUES
(1, 2, 'Celeste', 'Multi', 'Plateforme', 2018, 'Maddy Makes Games', 3.00, 92.0, 8.6, NULL, 'https://www.igdb.com/games/celeste', 'Je propose d’ajouter Celeste au catalogue.', 'pending', 0, '2026-03-01 10:00:00'),
(2, 3, 'Baldur''s Gate 3', 'PC', 'RPG', 2023, 'Larian Studios', 15.00, 96.0, 9.0, NULL, 'https://www.igdb.com/games/baldurs-gate-3', 'Jeu très important à ajouter.', 'accepted', 0, '2026-03-02 10:00:00'),
(3, 4, 'Fake Game Test', 'PC', 'Action', 2024, 'Unknown', 0.10, 50.0, 4.0, NULL, NULL, 'Ticket de test refusé.', 'rejected', 0, '2026-03-03 10:00:00'),
(4, 5, 'Outer Wilds', 'Multi', 'Aventure', 2019, 'Annapurna Interactive', 2.00, 85.0, 9.1, NULL, 'https://www.igdb.com/games/outer-wilds', 'Excellent jeu d’exploration.', 'accepted', 1, '2026-03-04 10:00:00');

INSERT INTO forum_messages (id, user_id, message, created_at)
VALUES
(1, 2, 'Salut tout le monde, vous jouez à quoi en ce moment ?', '2026-04-01 10:00:00'),
(2, 3, 'Je refais Elden Ring, toujours aussi difficile.', '2026-04-01 10:05:00'),
(3, 4, 'Je recommande Hollow Knight à ceux qui aiment les défis.', '2026-04-01 10:10:00'),
(4, 5, 'Hades est parfait pour des petites sessions.', '2026-04-01 10:15:00');

INSERT INTO moderation_logs (id, user_id, content_type, blocked_content, reason, created_at)
VALUES
(1, 2, 'review', 'contenu interdit test', 'Mot interdit détecté automatiquement.', '2026-04-05 10:00:00'),
(2, 3, 'forum', 'spam spam spam', 'Spam détecté automatiquement.', '2026-04-05 11:00:00'),
(3, 5, 'review', '<script>alert(1)</script>', 'Tentative de contenu dangereux.', '2026-04-05 12:00:00');

INSERT INTO notifications (id, user_id, type, title, message, link, is_read, created_at)
VALUES
(1, 2, 'system', 'Bienvenue sur GameStats', 'Ton compte est prêt à être utilisé.', 'profile.php', 0, '2026-04-10 10:00:00'),
(2, 2, 'badge', 'Nouveau badge débloqué', 'Tu as débloqué le badge Membre.', 'profile.php', 0, '2026-04-10 10:05:00'),
(3, 3, 'ticket', 'Ticket accepté', 'Ton ticket Baldur''s Gate 3 a été accepté.', 'tickets.php', 0, '2026-04-10 10:10:00'),
(4, 4, 'moderation', 'Contenu bloqué', 'Un de tes contenus a été bloqué par la modération.', 'my_sanctions.php', 1, '2026-04-10 10:15:00'),
(5, 5, 'friend', 'Nouvelle demande d’ami', 'lukas1 souhaite t’ajouter en ami.', 'friends.php', 0, '2026-04-10 10:20:00'),
(6, 2, 'message', 'Nouveau message privé', 'lukas3 t’a envoyé un message.', 'messages.php', 0, '2026-04-10 10:25:00');

INSERT INTO user_badges (user_id, badge_key, unlocked_at)
VALUES
(2, 'member', '2026-02-01 10:00:00'),
(2, 'reviewer', '2026-02-10 10:00:00'),
(2, 'favorite_collector', '2026-02-10 12:00:00'),
(3, 'member', '2026-02-02 10:00:00'),
(3, 'reviewer', '2026-02-11 10:00:00'),
(4, 'member', '2026-02-03 10:00:00'),
(5, 'member', '2026-02-04 10:00:00'),
(5, 'reviewer', '2026-02-13 10:00:00');

INSERT INTO admin_moderation_logs
(id, admin_id, target_user_id, action, reason, details, created_at)
VALUES
(1, 1, 6, 'ban_user', 'Comportement toxique répété.', 'Utilisateur banni depuis la gestion des membres.', '2026-04-15 10:00:00'),
(2, 1, 2, 'delete_review', 'Langage inapproprié.', 'Avis supprimé par un administrateur.', '2026-04-15 10:10:00'),
(3, 1, 3, 'delete_forum_message', 'Spam.', 'Message forum supprimé par un administrateur.', '2026-04-15 10:20:00'),
(4, 1, NULL, 'delete_game', 'Suppression de jeu', 'Jeu supprimé du catalogue : Exemple supprimé.', '2026-04-15 10:30:00'),
(5, 1, 6, 'unban_user', 'Test débannissement.', 'Utilisateur débanni depuis la gestion des membres.', '2026-04-15 10:40:00'),
(6, 1, 6, 'ban_user', 'Nouveau test de bannissement.', 'Utilisateur banni depuis la gestion des membres.', '2026-04-15 10:50:00'),
(7, 1, 4, 'delete_review', 'Spoiler non signalé.', 'Avis supprimé par un administrateur.', '2026-04-15 11:00:00'),
(8, 1, 5, 'delete_forum_message', 'Hors sujet.', 'Message forum supprimé par un administrateur.', '2026-04-15 11:10:00'),
(9, 1, NULL, 'delete_game', 'Nettoyage catalogue.', 'Jeu supprimé du catalogue : Ancien jeu test.', '2026-04-15 11:20:00'),
(10, 1, 2, 'ban_user', 'Test pagination.', 'Utilisateur banni pour tester l’historique.', '2026-04-15 11:30:00'),
(11, 1, 2, 'unban_user', 'Test pagination terminé.', 'Utilisateur débanni pour tester l’historique.', '2026-04-15 11:40:00'),
(12, 1, 3, 'delete_review', 'Avis abusif.', 'Avis supprimé pour test pagination.', '2026-04-15 11:50:00');

INSERT INTO friend_requests (id, sender_id, receiver_id, status, created_at, responded_at)
VALUES
(1, 2, 5, 'pending', '2026-05-01 10:00:00', NULL),
(2, 3, 2, 'accepted', '2026-05-01 11:00:00', '2026-05-01 11:30:00'),
(3, 4, 2, 'rejected', '2026-05-01 12:00:00', '2026-05-01 12:30:00');

INSERT INTO friendships (id, user_id, friend_id, created_at)
VALUES
(1, 2, 3, '2026-05-01 11:30:00'),
(2, 3, 2, '2026-05-01 11:30:00'),
(3, 2, 4, '2026-05-02 10:00:00'),
(4, 4, 2, '2026-05-02 10:00:00');

INSERT INTO private_conversations (id, user_one_id, user_two_id, created_at, updated_at)
VALUES
(1, 2, 3, '2026-05-03 10:00:00', '2026-05-03 10:10:00'),
(2, 2, 4, '2026-05-04 10:00:00', '2026-05-04 10:05:00');

INSERT INTO private_messages (id, conversation_id, sender_id, message, is_read, created_at)
VALUES
(1, 1, 2, 'Salut lukas2, tu veux jouer ce soir ?', 1, '2026-05-03 10:00:00'),
(2, 1, 3, 'Oui carrément, on lance Elden Ring ?', 0, '2026-05-03 10:10:00'),
(3, 2, 4, 'Tu as testé Hollow Knight ?', 1, '2026-05-04 10:00:00'),
(4, 2, 2, 'Oui, il est excellent.', 0, '2026-05-04 10:05:00');
