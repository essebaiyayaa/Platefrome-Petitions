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
('Essebaiy', 'aya', 'ayaa@petition.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Alami', 'Zineb', 'zineb@example.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Idrissi', 'Mohamed', 'mohamed@example.ma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- Mot de passe: password

INSERT INTO Petition (TitreP, DescriptionP, DateFinP, NomPorteurP, Email, IDU) VALUES
('Pour une éducation de qualité pour tous', 'Pétition pour améliorer les infrastructures éducatives et garantir un accès équitable à l\'éducation dans toutes les régions du Maroc.', '2026-06-30', 'Laila Bensouda', 'laila.b@ensa.ma', 1),
('Soutien aux entrepreneurs locaux', 'Demande de mesures pour faciliter l\'entrepreneuriat et soutenir les petites entreprises marocaines avec des financements et formations.', '2026-03-31', 'Amine El Fassi', 'amine.f@startup.ma', 2),
('Préservation du patrimoine culturel marocain', 'Pétition pour la protection et la restauration des sites historiques et monuments culturels du royaume.', '2026-09-15', 'Samira Chraibi', 'samira.c@culture.ma', 1),
('Amélioration des services de santé publique', 'Demande d\'augmentation du budget santé et amélioration de la qualité des soins dans les hôpitaux publics.', '2025-12-31', 'Dr. Karim Ziani', 'karim.z@sante.ma', 3);

INSERT INTO Signature (IDP, NomS, PrenomS, PaysS, EmailS) VALUES
(1, 'Tahiri', 'Yasmine', 'Maroc', 'yasmine.t@gmail.com'),
(1, 'Benjelloun', 'Rachid', 'Maroc', 'rachid.b@gmail.com'),
(1, 'Kettani', 'Nadia', 'Maroc', 'nadia.k@gmail.com'),
(1, 'Berrada', 'Hassan', 'Maroc', 'hassan.b@gmail.com'),
(2, 'Senhaji', 'Imane', 'Maroc', 'imane.s@gmail.com'),
(2, 'Mouline', 'Youssef', 'Maroc', 'youssef.m@gmail.com'),
(2, 'Elbaz', 'Sarah', 'Maroc', 'sarah.e@gmail.com'),
(3, 'Chakir', 'Omar', 'Maroc', 'omar.c@gmail.com'),
(3, 'Filali', 'Meriem', 'Maroc', 'meriem.f@gmail.com'),
(3, 'Tazi', 'Mehdi', 'Maroc', 'mehdi.t@gmail.com'),
(4, 'Alaoui', 'Fatima', 'Maroc', 'fatima.a@gmail.com'),
(4, 'Amrani', 'Saad', 'Maroc', 'saad.a@gmail.com');
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
