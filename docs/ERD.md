# EasyBites relational schema

**Milestone 2 schema.** `backend/database/easybites.sql` is the reproducible MySQL schema and sample-data export. It creates six relational entities and defines the keys and constraints shown below.

```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    USERS ||--o{ REVIEWS : writes
    RESTAURANTS ||--o{ MENU_ITEMS : offers
    RESTAURANTS ||--o{ ORDERS : receives
    RESTAURANTS ||--o{ REVIEWS : receives
    ORDERS ||--|{ ORDER_ITEMS : contains
    MENU_ITEMS ||--o{ ORDER_ITEMS : appears_in

    USERS {
        int id PK "unsigned auto-increment"
        varchar full_name
        varchar email UK
        varchar password_hash
        enum role "customer or admin"
        timestamp created_at
        timestamp updated_at
    }
    RESTAURANTS {
        int id PK "unsigned auto-increment"
        varchar name
        text description
        varchar address
        varchar phone
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    MENU_ITEMS {
        int id PK "unsigned auto-increment"
        int restaurant_id FK
        varchar name
        text description
        varchar category
        decimal price "BAM"
        boolean is_available
        timestamp created_at
        timestamp updated_at
    }
    ORDERS {
        int id PK
        int user_id FK
        int restaurant_id FK
        enum status
        decimal total "BAM"
        varchar delivery_address
        timestamp created_at
        timestamp updated_at
    }
    ORDER_ITEMS {
        int id PK
        int order_id FK
        int menu_item_id FK
        int quantity
        decimal unit_price "BAM at order time"
        timestamp created_at
    }
    REVIEWS {
        int id PK
        int user_id FK
        int restaurant_id FK
        int rating
        text comment
        timestamp created_at
        timestamp updated_at
    }
```

`ORDER_ITEMS` is the associative entity between orders and menu items. It stores the quantity and unit price at the time of purchase, so later menu-price changes do not rewrite the order history. One order belongs to one user and one restaurant. The database restricts deleting users/restaurants with orders and menu items referenced by order history; deleting a restaurant cascades to its menu items and reviews, and deleting an order cascades to its order items.

The schema enforces unique user emails, one review per user/restaurant, one line per menu item in an order, positive item quantities, non-negative prices/totals, and review ratings from 1 through 5. All tables use InnoDB and `utf8mb4`. Sample restaurant and menu-item rows are included; accounts, orders, and reviews are not seeded.
