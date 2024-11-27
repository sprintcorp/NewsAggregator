# Variables
ENV_FILE=.env
ENV_EXAMPLE_FILE=.env.example

# OS Detection
UNAME_S := $(shell uname -s)

# Targets
.PHONY: init keygen up

# Initialization: Copy .env.example to .env
init:
ifeq ($(UNAME_S), Linux)
	@echo "Detected Linux..."
	cp $(ENV_EXAMPLE_FILE) $(ENV_FILE)
else ifeq ($(UNAME_S), Darwin)
	@echo "Detected macOS..."
	cp $(ENV_EXAMPLE_FILE) $(ENV_FILE)
else ifeq ($(UNAME_S), Windows_NT)
	@echo "Detected Windows..."
	copy $(ENV_EXAMPLE_FILE) $(ENV_FILE)
else
	@echo "Unknown OS. Please copy the .env file manually."
	exit 1
endif
	@echo ".env file created."

# Generate app key
keygen:
	@echo "Generating app key..."
	php artisan key:generate

# Build and start Docker containers
up: init keygen
	@echo "Building and starting Docker containers..."
	docker-compose up --build
