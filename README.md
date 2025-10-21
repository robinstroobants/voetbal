# Voetbal Lineup Generator

This project is a PHP-based application that automatically generates optimal lineups and substitution schedules for soccer matches. It takes into account player selections, positions, playing minutes, and substitution schemes to create fair and balanced teams.

## Features

-   **Automatic Lineup Generation:** Creates optimal lineups based on player skills and availability.
-   **Substitution Schedules:** Generates substitution schedules to ensure fair playing time for all players.
-   **Player Management:** Keeps track of player information and performance.
-   **Customizable Formations:** Supports different game formats (e.g., 8v8, 11v11).
-   **Dockerized Environment:** Easy to set up and run using Docker.

## Tech Stack

-   **Backend:** PHP
-   **Database:** MySQL
-   **Containerization:** Docker

## Getting Started

To get started with this project, you need to have Docker and Docker Compose installed on your machine.

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/voetbal.git
    ```

2.  **Navigate to the project directory:**

    ```bash
    cd voetbal
    ```

3.  **Start the application:**

    ```bash
    docker-compose up -d
    ```

4.  **Access the application:**

    -   **Web Application:** [http://localhost:8085](http://localhost:8085)
    -   **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)

## Project Structure

```
.
├── db
│   └── init.sql
├── docker-compose.yml
├── php
│   ├── assets
│   ├── css
│   ├── selecties
│   ├── wisselschemas
│   ├── Dockerfile
│   ├── edit_players.php
│   ├── edit_scores.php
│   ├── game.php
│   ├── getconn.php
│   ├── import_scores.php
│   ├── index.php
│   ├── playerscores.php
│   ├── speelminuten.php
│   ├── stats.php
│   └── test.php
└── README.md
```

-   `db/init.sql`: Initializes the MySQL database with the necessary tables and data.
-   `docker-compose.yml`: Defines the services, networks, and volumes for the Dockerized application.
-   `php/`: Contains the PHP source code for the web application.
    -   `selecties/`: Contains player selections for different matches.
    -   `wisselschemas/`: Contains substitution schedules for different game formats.
    -   `index.php`: The main entry point of the application.

## How it Works

The application uses a set of PHP scripts to generate lineups and substitution schedules. The `index.php` script is the main entry point, which loads the necessary data from the `selecties` and `wisselschemas` directories. It then uses a shuffling algorithm to find the optimal lineup based on player scores and positions.

The application is containerized using Docker, with three main services:

-   `web`: A PHP container that serves the web application.
-   `db`: A MySQL container that stores the player data.
-   `phpmyadmin`: A phpMyAdmin container for managing the MySQL database.

## Database

The application uses a MySQL database to store player information and scores. The database schema is defined in the `db/init.sql` file and consists of two tables:

-   `players`: Stores information about the players, such as their name, birthdate, and team.
-   `player_scores`: Stores the scores of each player for different positions.

To access the database, you can use phpMyAdmin at [http://localhost:8081](http://localhost:8081).
The database credentials are set in the `docker-compose.yml` file. For security reasons, it is recommended to change the default passwords.
