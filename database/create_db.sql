-- create_db.sql

-- ==============================
-- Tabela: users
-- Armazena informações básicas de todos os usuários (clientes, freelancers e admins).
-- ==============================
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0,      -- 0 = não é admin, 1 = é admin
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,-- em create_db.sql (tabela users)
    wallet REAL DEFAULT 0          -- saldo do utilizador (€, começa 0)
    
);

-- ==============================
-- Tabela: categories
-- Lista de categorias de serviços (ex.: Design, Programação, Escrita, etc.).
-- ==============================
CREATE TABLE IF NOT EXISTS categories (
    category_id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name TEXT UNIQUE NOT NULL
);

-- ==============================
-- Tabela: services
-- Serviços oferecidos pelos freelancers, associados a um freelancer (user_id).
-- ==============================
CREATE TABLE IF NOT EXISTS services (
    service_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    price REAL NOT NULL,
    delivery_time INTEGER NOT NULL,
    image TEXT,      
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);


-- ==============================
-- Tabela: orders
-- Registra cada "contratação" de um serviço. Armazena o cliente e o freelancer por meio do service_id.
-- ==============================
CREATE TABLE IF NOT EXISTS orders (
    order_id        INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id      INTEGER NOT NULL,
    client_id       INTEGER NOT NULL,
    total_price     REAL,
    status          TEXT DEFAULT 'pending',
    order_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- ▸ NOVOS CAMPOS:
    custom_price    REAL,
    custom_delivery INTEGER,
    --
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (client_id)  REFERENCES users(user_id)
);


-- ==============================
-- Tabela: reviews
-- Armazena avaliações e comentários de clientes sobre serviços completados.
-- ==============================
CREATE TABLE IF NOT EXISTS reviews (
    review_id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,    -- review associado a um pedido específico
    rating INTEGER NOT NULL,      -- nota, por ex. 1-5
    comment TEXT,                 -- comentário
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- ==============================
-- Tabela: messages
-- Permite troca de mensagens entre freelancer e cliente, ou entre quaisquer usuários se desejado.
-- ==============================
CREATE TABLE IF NOT EXISTS messages (
    message_id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,      -- quem enviou a mensagem
    receiver_id INTEGER NOT NULL,    -- quem recebeu
    content TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS service_media (
    media_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id  INTEGER NOT NULL,
    file_name   TEXT    NOT NULL,
    media_type  TEXT    NOT NULL,     -- 'image' | 'video'
    FOREIGN KEY (service_id) REFERENCES services(service_id)
);



INSERT INTO categories (category_name) VALUES ('Design');
INSERT INTO categories (category_name) VALUES ('Programação');