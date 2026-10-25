# EasyBites draft ERD

**Milestone 1 planning draft.** This diagram identifies the first six entities and their planned relationships. Column names and constraints may be refined during database implementation.

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
        int id PK
        string full_name
        string email UK
        string password_hash
        string role
        datetime created_at
    }
    RESTAURANTS {
        int id PK
        string name
        string description
        string address
        string phone
        boolean is_active
    }
    MENU_ITEMS {
        int id PK
        int restaurant_id FK
        string name
        string description
        decimal price
        boolean is_available
    }
    ORDERS {
        int id PK
        int user_id FK
        int restaurant_id FK
        string status
        decimal total
        string delivery_address
        datetime created_at
    }
    ORDER_ITEMS {
        int id PK
        int order_id FK
        int menu_item_id FK
        int quantity
        decimal unit_price
    }
    REVIEWS {
        int id PK
        int user_id FK
        int restaurant_id FK
        int rating
        string comment
        datetime created_at
    }
```

`ORDER_ITEMS` is the associative entity between orders and menu items. It stores the quantity and unit price at the time of purchase, so later menu-price changes do not rewrite the order history. The `role` field is planned to distinguish `customer` and `admin`; authorization rules will be implemented in the backend.
