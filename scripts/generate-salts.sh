#!/bin/bash

# Script to generate WordPress salts and update .env file
# Usage: ./scripts/generate-salts.sh

echo "Generating WordPress salts..."

# Fetch salts from Roots API
SALTS=$(curl -s https://roots.io/salts.html | grep "define" | sed "s/define('//g" | sed "s/',.*'/=/g" | sed "s/');//g")

if [ -z "$SALTS" ]; then
    echo "Error: Failed to fetch salts from roots.io"
    exit 1
fi

# Create temp file with new salts
echo "$SALTS" > /tmp/salts.tmp

# Update .env file if it exists
if [ -f .env ]; then
    echo "Updating .env file with new salts..."
    
    # Read each salt line and update .env
    while IFS='=' read -r key value; do
        if [ ! -z "$key" ]; then
            # Check if key exists in .env
            if grep -q "^${key}=" .env; then
                # Replace the line
                sed -i "s|^${key}=.*|${key}='${value}'|g" .env
            else
                # Append if not exists
                echo "${key}='${value}'" >> .env
            fi
        fi
    done < /tmp/salts.tmp
    
    echo "Salts updated successfully!"
else
    echo "Error: .env file not found. Please copy .env.example to .env first."
    exit 1
fi

# Clean up
rm /tmp/salts.tmp
