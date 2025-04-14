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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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
    user_id INTEGER NOT NULL,                -- freelancer que oferece o serviço
    category_id INTEGER NOT NULL,            -- categoria do serviço
    title TEXT NOT NULL,                     -- título (ex.: "Logo Design")
    description TEXT NOT NULL,               -- descrição
    price REAL NOT NULL,                     -- preço base do serviço
    delivery_time INTEGER NOT NULL,          -- tempo de entrega em dias
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- ==============================
-- Tabela: orders
-- Registra cada "contratação" de um serviço. Armazena o cliente e o freelancer por meio do service_id.
-- ==============================
CREATE TABLE IF NOT EXISTS orders (
    order_id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,     -- referencia o serviço
    client_id INTEGER NOT NULL,      -- usuário que está contratando (pode ser a mesma pessoa que user_id, mas normalmente não)
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TEXT DEFAULT 'pending',   -- 'pending', 'in_progress', 'completed', 'cancelled'
    total_price REAL NOT NULL,
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (client_id) REFERENCES users(user_id)
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

INSERT INTO categories (category_name) VALUES ('Design');
INSERT INTO categories (category_name) VALUES ('Programação');
