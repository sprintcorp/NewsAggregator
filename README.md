# News Aggregator API

Welcome to the **News Aggregator API**. This backend API is built using Laravel and serves as a central service for aggregating and delivering news articles from multiple sources.

## Features

### User Authentication
- **Endpoints:**
  - `POST /register`: User registration.
  - `POST /login`: User login using Laravel Sanctum.
  - `POST /logout`: User logout by revoking the API token.
  - `POST /password/reset`: Request and handle password reset.
  - `POST /password/update`: update password after reset.
- **Security:** Laravel Sanctum is used for token-based API authentication.

### Article Management
- **Endpoints:**
  - `GET /articles`: Fetch a paginated list of articles.
    - Filters: `keyword`, `date`, `category`, `source`.
  - `GET /articles/{id}`: Retrieve detailed information about a single article.
- **Features:**
  - Full-text search.
  - Pagination for efficient data retrieval.

### User Preferences
- **Endpoints:**
  - `POST /preferences`: Save user preferences for categories, authors, or news sources.
  - `GET /preferences`: Retrieve the user’s preferences.
  - `GET /personalized-feed`: Fetch a customized feed based on preferences.
- **Functionality:** Preferences are used to tailor news feeds to individual users.

### Data Aggregation
- **Sources:**
  - **NewsAPI**
  - **The Guardian**
  - **New York Times**
- **Implementation:**
  - Laravel scheduled commands (`schedule:run`) to fetch and store articles from APIs regularly.
  - Articles are stored locally in the database for optimized retrieval and filtering.
  - Efficient indexing ensures performant search operations.

### Database Design
- **Schema:**
  - **Users**: Stores user details and authentication information.
  - **Articles**: Stores news articles with fields for title, content, source, category, date, and author.
  - **Preferences**: Stores user-specific preferences for categories, authors, and sources.
- **Tools:** Laravel migrations and seeders for setup.

### API Documentation
- **Tool:** Swagger/OpenAPI.
- **Postman Api Documentation:** 
  `https://documenter.getpostman.com/view/7305732/2sAYBVhrye`
- **Endpoint:** Accessible via `http://localhost:8000/api/documentation`.
- **Includes:**
  - Detailed descriptions of all endpoints.
  - Request/response examples.
  - Validation rules for each endpoint.

### Dockerized Setup
- **Docker Compose:**
  - `docker-compose.yml` to orchestrate the application and dependencies.
  - Services: Laravel, MySQL/PostgreSQL, Redis (for caching).
- **Setup Instructions:**
  1. Clone the repository.
  2. Run `docker-compose up` to start the application.
  3. Access the API via `http://localhost:8000`.

### Performance Optimization
- **Caching:**
  - Laravel cache for frequently accessed queries (e.g., personalized feeds).
  - Redis for fast in-memory caching.
- **Rate Limiting:**
  - Implemented using Laravel's `ThrottleRequests` middleware.

### Security
- Input sanitization to prevent SQL Injection and XSS (custom middleware implemented).
- Authorization checks for protected routes.
- CSRF protection enabled for stateful requests.
- Rate limiting to prevent abuse.

### Testing
- **Unit Tests:** Test individual components (e.g., models, services).
- **Feature Tests:** Validate API endpoints.
- **Tools:** PHPUnit for testing.
- **Coverage:** Ensure adequate coverage for critical functionalities.

## Project Setup

### Prerequisites
- Docker installed.
- API keys for chosen data sources (e.g., NewsAPI).

### Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/sprintcorp/NewsAggregator.git

   git checkout master
   

2. Switch to the folder directory
    ```bash
    cd NewsAggregator

Use make file command `make up` to run all need command or skip to run individually


3. Create .env file if it does not exist and copy the .env.example to .env file and make the required   configuration changes
 
    ```bash
    cp .env.example .env (Windows)

4. Generate a new application key
    ```bash
    php artisan key:generate

 
5. Update the .env file with the following Docker-specific values:
    ```bash
    MAIL_HOST=

    NY_TIMES_KEY=""
    GUARDIAN_KEY=""
    NEWSORG_API_KEY=""

    MAIL_USERNAME=
    MAIL_PASSWORD=

6. Build and Start Docker Containers: Run the following command to build the Docker containers and start the services:

    ```bash
    docker-compose up --build

7. Access the Application:

API Base URL: http://localhost:8000
Swagger Documentation: http://localhost:8000/api/documentation


8. Rebuild Containers:
    ```bash
    docker-compose up --build -d

9. Stop and Remove Containers:
    ```bash
    docker-compose down
   
10. Access application container
    ```bash
    docker ps
    
11. Select the container name and access it  
    ```bash
    docker exec -it <container_name> bash
    
12. To run test in container
    ```bash
    docker exec -it <container_id> php artisan test



