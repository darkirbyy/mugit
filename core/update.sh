#!/bin/sh

# Checking if first argument is present
if [ -z "$1" ]; then
    echo "No version provided, aborting."
    exit 1
fi

# Checking if first argument is a version (semver format)
if  ! echo "$1" | grep -Eq "^[0-9]+\.[0-9]+\.[0-9]+$"; then
    echo "Version not im semver format (X.X.X), aborting."
    exit 1
fi

echo "Updating the core version..."

# Stop the current mugit core container
docker compose down

# Checkout the version given in argument
git fetch origin --tags
git checkout "v$1"

# Reuild and restart the container
docker compose up --build -d

echo "Done."
exit 0
