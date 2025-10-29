# PLATEFORME DE PÉTITIONS

A simple PHP & MySQL project that allows users to create, sign, and manage online petitions with real-time updates and notifications.

## TABLE SCHEMA

Create these tables in order to use the APP:

```sql
-- Base de données Petition
CREATE DATABASE IF NOT EXISTS Petition;
USE Petition;

-- Table Utilisateur
CREATE TABLE Utilisateur (
    IDU INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(100) NOT NULL,
    Prenom VARCHAR(100) NOT NULL,
    Email VARCHAR(150) UNIQUE NOT NULL,
    MotDePasse VARCHAR(255) NOT NULL, 
    Role ENUM('user', 'admin') DEFAULT 'user', 
    DateInscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    Actif BOOLEAN DEFAULT TRUE
);

-- Table Petition
CREATE TABLE Petition (
    IDP INT AUTO_INCREMENT PRIMARY KEY,
    TitreP VARCHAR(200) NOT NULL,
    DescriptionP TEXT NOT NULL,
    DateAjoutP DATETIME DEFAULT CURRENT_TIMESTAMP,
    DateFinP DATE,
    NomPorteurP VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL,
    IDU INT, 
    FOREIGN KEY (IDU) REFERENCES Utilisateur(IDU) ON DELETE SET NULL
);

-- Table Signature
CREATE TABLE Signature (
    IDS INT AUTO_INCREMENT PRIMARY KEY,
    IDP INT NOT NULL,
    NomS VARCHAR(100) NOT NULL,
    PrenomS VARCHAR(100) NOT NULL,
    PaysS VARCHAR(100),
    DateS DATETIME DEFAULT CURRENT_TIMESTAMP,
    HeureS TIME DEFAULT (CURRENT_TIME),
    EmailS VARCHAR(150) NOT NULL,
    IDU INT, 
    FOREIGN KEY (IDP) REFERENCES Petition(IDP) ON DELETE CASCADE,
    FOREIGN KEY (IDU) REFERENCES Utilisateur(IDU) ON DELETE SET NULL,
    UNIQUE KEY unique_signature (IDP, EmailS)
);

-- Données de test
INSERT INTO Utilisateur (Nom, Prenom, Email, MotDePasse, Role) VALUES
('Admin', 'Système', 'admin@petition.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('User', 'Test', 'user@example.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- Mot de passe: password

INSERT INTO Petition (TitreP, DescriptionP, DateFinP, NomPorteurP, Email, IDU) VALUES
('Pour un environnement plus propre', 'Pétition pour améliorer la propreté et la gestion des déchets dans notre ville.', '2025-12-31', 'Ahmed Bennani', 'ahmed.b@gmail.com', 1),
('Amélioration des transports publics', 'Demande d\'extension et d\'amélioration du réseau de transport en commun.', '2025-11-30', 'Fatima Alaoui', 'fatima.a@gmail.com', 1),
('Protection des espaces verts', 'Pétition pour la préservation et la création de nouveaux parcs et jardins publics.', '2025-10-31', 'Youssef Tahiri', 'youssef.t@gmail.com', 2);

INSERT INTO Signature (IDP, NomS, PrenomS, PaysS, EmailS) VALUES
(1, 'El Amrani', 'Sara', 'Maroc', 'sara.e@gmail.com'),
(1, 'Benkirane', 'Omar', 'Maroc', 'omar.b@gmail.com'),
(1, 'Chakir', 'Meryem', 'Maroc', 'meryem.c@gmail.com'),
(2, 'Tazi', 'Karim', 'Maroc', 'karim.t@gmail.com'),
(2, 'Fassi', 'Leila', 'Maroc', 'leila.f@gmail.com'),
(3, 'Benjelloun', 'Hamza', 'Maroc', 'hamza.b@gmail.com');
```

## FEATURES

- **User Authentication** with role-based access (User/Admin)
- **Create Petitions** with title, description, and end date
- **Sign Petitions** with personal information (name, country, email)
- **List Petitions** sorted by most recent
- **Real-time Updates** using AJAX/XMLHttpRequest
- **Signature Display** showing last 5 signatures
- **Notification System** for new petitions
- **Live Tracking** of most signed petition
- **Manage Petitions** (view, edit, delete your own petitions)
- **Duplicate Prevention** (unique constraint per email/petition)

## PROJECT STRUCTURE

```
├── config/                      # Database configuration
├── includes/
│   ├── footer.php              # Footer template
│   └── navbar.php              # Navigation bar
├── migrations/
│   └── script.sql              # Database schema
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── styles.css      # Styles
│   │   └── js/
│   │       └── script.js       # JavaScript functions
│   └── auth/
│       ├── login.php           # Login page
│       ├── logout.php          # Logout handler
│       ├── process_login.php   # Login processing
│       ├── process_register.php # Registration processing
│       └── register.php        # Registration page
├── AjouterSignature.php        # Add signature handler
├── check_new_petitions.php     # Check for new petitions (AJAX)
├── creer_petition.php          # Create petition page
├── get_recent_signatures.php   # Fetch recent signatures (AJAX)
├── get_top_petition.php        # Get most signed petition (AJAX)
├── home.php                    # Homepage
├── ListePetition.php           # List all petitions
├── mes_petitions.php           # User's petitions
├── modifier_petition.php       # Edit petition
├── signer_petition.php         # Sign petition form
└── supprimer_petition.php      # Delete petition
```

## INSTALLATION

1. Clone the repository:
```bash
git clone https://github.com/essebaiyayaa/Platefrome-Petitions.git
cd Platefrome-Petitions
```

2. Import the database:
```bash
mysql -u your_username -p < migrations/script.sql
```

3. Configure database connection in `config/` directory

4. Place the project in your web server's root directory (e.g., `htdocs` for XAMPP)

5. Access via: `http://localhost/Platefrome-Petitions`

## USAGE

1. **Register/Login** - Create account or login (default password: `password`)
2. **Create Petition** - Add new petition with details
3. **Browse Petitions** - View all petitions on ListePetition page
4. **Sign Petition** - Click petition and fill signature form
5. **Manage** - Edit or delete your own petitions

## TECHNOLOGIES

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **AJAX:** XMLHttpRequest for real-time updates
